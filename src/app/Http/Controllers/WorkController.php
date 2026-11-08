<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\GarmentType;
use App\Models\Setting;
use App\Models\WorkAssignment;
use App\Models\WorkCancelRequest;
use App\Models\WorkSubmission;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class WorkController extends Controller
{
    private function hasClockedInToday(): bool
    {
        return Attendance::where('user_id', auth()->id())
            ->where('type', 'clock_in')
            ->whereDate('noted_at', today())
            ->exists();
    }

    private function cooldownInfo(int $userId): array
    {
        $cooldownMinutes = (int) Setting::get('work_cooldown_minutes', 2);

        $lastCompleted = WorkAssignment::where('assigned_to', $userId)
            ->whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->first();

        if (! $lastCompleted || ! $lastCompleted->completed_at) {
            return ['in_cooldown' => false, 'remaining_seconds' => 0, 'cooldown_minutes' => $cooldownMinutes];
        }

        $cooldownEndsAt = $lastCompleted->completed_at->addMinutes($cooldownMinutes);
        $now            = now();

        if ($now->greaterThanOrEqualTo($cooldownEndsAt)) {
            return ['in_cooldown' => false, 'remaining_seconds' => 0, 'cooldown_minutes' => $cooldownMinutes];
        }

        return [
            'in_cooldown'       => true,
            'remaining_seconds' => (int) $now->diffInSeconds($cooldownEndsAt),
            'cooldown_ends_at'  => $cooldownEndsAt,
            'cooldown_minutes'  => $cooldownMinutes,
        ];
    }

    public function index()
    {
        $user = auth()->user();

        // Semua tugas aktif milik user — personal selalu di atas
        $myTasks = WorkAssignment::with(['garmentType', 'workType', 'latestSubmission.review', 'latestCancelRequest'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['open', 'in_progress', 'submitted', 'revision', 'cancel_requested'])
            ->orderByRaw("FIELD(type, 'personal', 'pool')")
            ->orderBy('deadline')
            ->get();

        $hasPersonal = $myTasks->where('type', 'personal')->isNotEmpty();

        // Pool selalu dimuat kecuali cooldown — tombol klaim dikontrol di view
        $cooldown        = $this->cooldownInfo($user->id);
        $poolAssignments = collect();

        if (! $cooldown['in_cooldown']) {
            $poolAssignments = WorkAssignment::with(['garmentType', 'workType', 'createdBy'])
                ->openPool()
                ->orderBy('deadline')
                ->get();
        }

        $history = WorkAssignment::with(['garmentType', 'workType', 'latestSubmission.review'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('work.index', compact('myTasks', 'hasPersonal', 'poolAssignments', 'history', 'cooldown'));
    }

    public function show(WorkAssignment $assignment)
    {
        $user = auth()->user();

        $canView = $assignment->assigned_to === $user->id
            || ($assignment->isPool() && $assignment->status === 'open' && is_null($assignment->assigned_to));

        abort_unless($canView, 403);

        $assignment->load(['garmentType', 'workType', 'createdBy', 'submissions.review.reviewedBy', 'latestCancelRequest']);

        $isSelfAssign = $assignment->created_by === $user->id && $assignment->assigned_to === $user->id;
        $garmentKategori   = collect();
        $garmentByKategori = collect();
        $workTypes         = collect();

        if ($isSelfAssign && $assignment->status === 'in_progress') {
            $garmentKategori   = \App\Models\GarmentCategory::active()->orderBy('nama')->pluck('nama', 'id');
            $garmentByKategori = GarmentType::active()->with('garmentCategory')->orderBy('contoh_pakaian')->get()->groupBy('garment_category_id');
            $workTypes         = WorkType::active()->orderBy('nama')->get();
        }

        return view('work.show', compact('assignment', 'garmentKategori', 'garmentByKategori', 'workTypes'));
    }

    public function claim(WorkAssignment $assignment)
    {
        $user = auth()->user();

        abort_unless($assignment->isPool() && $assignment->status === 'open' && is_null($assignment->assigned_to), 400);

        if (! $this->hasClockedInToday()) {
            return redirect()->route('work.index')
                ->with('error', 'Kamu belum absen masuk hari ini. Absen dulu sebelum mengambil tugas.');
        }

        if ($user->hasActiveWorkAssignment()) {
            return redirect()->route('work.index')
                ->with('error', 'Kamu masih punya tugas aktif. Selesaikan dulu sebelum mengambil tugas baru.');
        }

        $cooldown = $this->cooldownInfo($user->id);
        if ($cooldown['in_cooldown']) {
            return redirect()->route('work.index')
                ->with('error', 'Masih dalam masa cooldown. Tunggu ' . ceil($cooldown['remaining_seconds'] / 60) . ' menit lagi.');
        }

        $claimed = WorkAssignment::where('id', $assignment->id)
            ->where('status', 'open')
            ->whereNull('assigned_to')
            ->update([
                'assigned_to' => $user->id,
                'status'      => 'in_progress',
            ]);

        if (! $claimed) {
            return redirect()->route('work.index')
                ->with('error', 'Tugas ini sudah diklaim orang lain. Pilih tugas lain.');
        }

        return redirect()->route('work.show', $assignment)
            ->with('success', 'Tugas berhasil diklaim! Semangat mengerjakan.');
    }

    public function start(WorkAssignment $assignment)
    {
        abort_unless($assignment->assigned_to === auth()->id(), 403);
        abort_unless($assignment->status === 'open', 400);

        if (! $this->hasClockedInToday()) {
            return redirect()->route('work.show', $assignment)
                ->with('error', 'Kamu belum absen masuk hari ini. Absen dulu sebelum memulai tugas.');
        }

        $assignment->update(['status' => 'in_progress']);

        return redirect()->route('work.show', $assignment)->with('success', 'Tugas dimulai, semangat!');
    }

    public function selfCreate(Request $request)
    {
        $user = auth()->user();

        if (Setting::get('self_assign_mode', '0') !== '1') {
            abort(403, 'Mode Mandiri tidak aktif.');
        }

        if (! $this->hasClockedInToday()) {
            return redirect()->route('dashboard')
                ->with('error', 'Kamu belum absen masuk hari ini. Absen dulu sebelum membuat tugas.');
        }

        $maxTugas   = \App\Filament\Pages\WorkSettings::getSelfAssignMaxTugas();
        $tugasAktif = WorkAssignment::where('assigned_to', $user->id)
            ->where('type', 'personal')
            ->whereIn('status', ['open', 'in_progress', 'submitted', 'revision'])
            ->count();

        if ($tugasAktif >= $maxTugas) {
            return redirect()->route('dashboard')
                ->with('error', "Kamu sudah punya {$tugasAktif} tugas mandiri aktif. Maksimal {$maxTugas} tugas bersamaan.");
        }

        $validated = $request->validate([
            'garment_type_id' => ['required', 'exists:garment_types,id'],
            'work_type_id'    => ['required', 'exists:work_types,id'],
            'target_jumlah'   => ['required', 'integer', 'min:1', 'max:999'],
            'instruksi'       => ['nullable', 'string', 'max:500'],
        ]);

        $assignment = WorkAssignment::create([
            'type'            => 'personal',
            'garment_type_id' => $validated['garment_type_id'],
            'work_type_id'    => $validated['work_type_id'],
            'assigned_to'     => $user->id,
            'created_by'      => $user->id,
            'target_jumlah'   => $validated['target_jumlah'],
            'instruksi'       => $validated['instruksi'] ?? null,
            'status'          => 'in_progress',
        ]);

        return redirect()->route('work.show', $assignment)
            ->with('success', 'Tugas berhasil dibuat. Semangat mengerjakan!');
    }

    public function submit(Request $request, WorkAssignment $assignment)
    {
        abort_unless($assignment->assigned_to === auth()->id(), 403);
        abort_unless(in_array($assignment->status, ['in_progress', 'revision']), 400);

        $hasFotoFile   = $request->hasFile('foto');
        $hasFotoBase64 = $request->filled('foto_base64') && str_starts_with($request->input('foto_base64'), 'data:image/');

        $validated = $request->validate([
            'jumlah_selesai' => ['required', 'integer', 'min:1', 'max:' . $assignment->target_jumlah],
            'catatan'        => ['nullable', 'string', 'max:1000'],
            'foto'           => [$hasFotoBase64 ? 'nullable' : 'required', 'image', 'max:5120'],
        ], [
            'foto.required' => 'Foto bukti wajib dilampirkan.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.max'      => 'Ukuran foto maksimal 5MB.',
        ]);

        if (! $hasFotoFile && ! $hasFotoBase64) {
            return back()->withErrors(['foto' => 'Foto bukti wajib dilampirkan.'])->withInput();
        }

        $fotoPath = null;
        if ($hasFotoFile) {
            $manager = new ImageManager(new GdDriver());
            $image   = $manager->read($request->file('foto')->getRealPath())
                               ->scaleDown(width: 1280)
                               ->toJpeg(quality: 75);
            $filename = 'work-submissions/' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $image);
            $fotoPath = $filename;
        } elseif ($hasFotoBase64) {
            $raw        = $request->input('foto_base64');
            $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $raw);
            $imageData  = base64_decode($base64Data, strict: true);

            // Tolak jika decode gagal atau ukuran melebihi 5 MB
            if ($imageData === false || strlen($imageData) > 5 * 1024 * 1024) {
                return back()->withErrors(['foto' => 'Foto tidak valid atau ukuran melebihi 5MB.'])->withInput();
            }

            // Reprocess via GD agar hanya piksel valid yang tersimpan
            try {
                $manager  = new ImageManager(new GdDriver());
                $image    = $manager->read($imageData)->scaleDown(width: 1280)->toJpeg(quality: 75);
                $filename = 'work-submissions/' . uniqid() . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $image);
                $fotoPath = $filename;
            } catch (\Throwable) {
                return back()->withErrors(['foto' => 'File bukan gambar yang valid.'])->withInput();
            }
        }

        $submission = WorkSubmission::create([
            'work_assignment_id' => $assignment->id,
            'submitted_by'       => auth()->id(),
            'jumlah_selesai'     => $validated['jumlah_selesai'],
            'catatan'            => $validated['catatan'] ?? null,
            'foto_path'          => $fotoPath,
            'nilai_poin'         => 0,
        ]);

        $submission->update(['nilai_poin' => $submission->computePoin()]);

        $assignment->update(['status' => 'submitted']);

        return redirect()->route('work.show', $assignment)->with('success', 'Hasil pekerjaan berhasil dikirim!');
    }

    public function requestCancel(Request $request, WorkAssignment $assignment)
    {
        abort_unless($assignment->assigned_to === auth()->id(), 403);
        abort_unless(in_array($assignment->status, ['open', 'in_progress', 'submitted', 'revision']), 400);

        // Cek tidak ada cancel request pending sebelumnya
        $existingRequest = $assignment->cancelRequests()->where('status', 'pending')->exists();
        if ($existingRequest) {
            return redirect()->route('work.show', $assignment)
                ->with('error', 'Permintaan cancel sudah ada dan sedang menunggu persetujuan.');
        }

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        WorkCancelRequest::create([
            'work_assignment_id' => $assignment->id,
            'requested_by'       => auth()->id(),
            'alasan'             => $validated['alasan'],
        ]);

        $assignment->update(['status' => 'cancel_requested']);

        return redirect()->route('work.show', $assignment)
            ->with('success', 'Permintaan cancel dikirim. Menunggu persetujuan supervisor.');
    }

    public function selfEdit(Request $request, WorkAssignment $assignment)
    {
        $user = auth()->user();
        abort_unless($assignment->assigned_to === $user->id && $assignment->created_by === $user->id, 403);
        abort_unless($assignment->status === 'in_progress', 400);

        $validated = $request->validate([
            'garment_type_id' => ['required', 'exists:garment_types,id'],
            'work_type_id'    => ['required', 'exists:work_types,id'],
            'target_jumlah'   => ['required', 'integer', 'min:1', 'max:999'],
            'instruksi'       => ['nullable', 'string', 'max:500'],
        ]);

        $assignment->update($validated);

        return redirect()->route('work.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function selfDelete(Request $request, WorkAssignment $assignment)
    {
        $user = auth()->user();
        abort_unless($assignment->assigned_to === $user->id && $assignment->created_by === $user->id, 403);
        abort_unless(in_array($assignment->status, ['in_progress', 'open']), 400);

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $assignment->update([
            'status'       => 'cancelled',
            'completed_at' => now(),
        ]);

        WorkCancelRequest::create([
            'work_assignment_id' => $assignment->id,
            'requested_by'       => $user->id,
            'alasan'             => $validated['alasan'],
            'status'             => 'approved',
            'reviewed_by'        => $user->id,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Tugas berhasil dihapus.');
    }

    public function rekap()
    {
        $user = auth()->user();

        $stats = [
            'total'     => WorkAssignment::where('assigned_to', $user->id)->count(),
            'approved'  => WorkAssignment::where('assigned_to', $user->id)->where('status', 'approved')->count(),
            'rejected'  => WorkAssignment::where('assigned_to', $user->id)->where('status', 'rejected')->count(),
            'cancelled' => WorkAssignment::where('assigned_to', $user->id)->where('status', 'cancelled')->count(),
            'active'    => WorkAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['open', 'in_progress', 'submitted', 'revision', 'cancel_requested'])->count(),
        ];

        $totalPoin = WorkSubmission::whereHas('assignment', fn ($q) => $q->where('assigned_to', $user->id))
            ->whereHas('review', fn ($q) => $q->where('status', 'approved'))
            ->sum('nilai_poin');

        $avgKualitas = WorkSubmission::whereHas('assignment', fn ($q) => $q->where('assigned_to', $user->id))
            ->whereHas('review', fn ($q) => $q->where('status', 'approved')->whereNotNull('skor_kualitas'))
            ->join('work_reviews', 'work_submissions.id', '=', 'work_reviews.work_submission_id')
            ->avg('work_reviews.skor_kualitas');

        $history = WorkAssignment::with(['garmentType', 'workType', 'latestSubmission.review'])
            ->where('assigned_to', $user->id)
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('work.rekap', compact('stats', 'totalPoin', 'avgKualitas', 'history'));
    }
}
