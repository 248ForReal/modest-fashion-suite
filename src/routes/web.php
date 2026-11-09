<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/forgot-password', [PasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
    Route::get('/auth/google/register', [GoogleController::class, 'redirectRegister'])->name('auth.google.register');
    Route::get('/auth/google/login', [GoogleController::class, 'redirectLogin'])->name('auth.google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware(['auth'])->get('/export/attendances', function (\Illuminate\Http\Request $request) {
    abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
    return (new \App\Exports\AttendanceExport(
        $request->dari,
        $request->sampai,
        $request->type,
        $request->mode,
        $request->role,
    ))->download();
})->name('export.attendances');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/pending', fn() => view('auth.pending'))->name('pending');

    Route::middleware(\App\Http\Middleware\EnsureUserIsActive::class)->group(function () {
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $todayAttendances = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('noted_at', today())
                ->orderBy('noted_at')
                ->get();
            $clockedIn  = $todayAttendances->where('type', 'clock_in')->isNotEmpty();
            $clockedOut = $todayAttendances->where('type', 'clock_out')->isNotEmpty();
            $workStart  = \App\Models\UserWorkSchedule::startFor($user->id);
            $workEnd    = \App\Models\Setting::get('work_end_time', '17:00');

            // Semua tugas user yang masih aktif (personal + pool yg sudah diklaim)
            $myTasks         = collect();
            $poolAssignments = collect();
            $hasPersonal     = false;

            $selfAssignMode   = false;
            $garmentKategori  = collect();
            $garmentByKategori = [];
            $workTypes        = collect();

            if ($user->can('work.view_own')) {
                // Semua tugas milik user yg masih aktif — personal dulu, baru pool
                $myTasks = \App\Models\WorkAssignment::with(['garmentType', 'workType'])
                    ->where('assigned_to', $user->id)
                    ->whereIn('status', ['open', 'in_progress', 'submitted', 'revision', 'cancel_requested'])
                    ->orderByRaw("FIELD(type, 'personal', 'pool')")  // personal selalu atas
                    ->orderBy('deadline')
                    ->get();

                $hasPersonal = $myTasks->where('type', 'personal')->isNotEmpty();

                // Pool selalu ditampilkan (kecuali cooldown), tapi tombol klaim dinonaktifkan kalau ada personal
                $cooldownMinutes = (int) \App\Models\Setting::get('work_cooldown_minutes', 2);
                $lastCompleted   = \App\Models\WorkAssignment::where('assigned_to', $user->id)
                    ->whereIn('status', ['approved', 'rejected', 'cancelled'])
                    ->whereNotNull('completed_at')
                    ->orderByDesc('completed_at')->first();
                $inCooldown = $lastCompleted &&
                    now()->lessThan($lastCompleted->completed_at->addMinutes($cooldownMinutes));
                if (! $inCooldown) {
                    $poolAssignments = \App\Models\WorkAssignment::with(['garmentType', 'workType'])
                        ->openPool()->orderBy('deadline')->get();
                }

                // Mode mandiri — admin bisa aktifkan sementara
                $selfAssignMode = \App\Models\Setting::get('self_assign_mode', '0') === '1';
                if ($selfAssignMode && $myTasks->isEmpty()) {
                    $garmentKategori = \App\Models\GarmentType::active()
                        ->orderBy('kategori')->distinct()->pluck('kategori');
                    $garmentByKategori = \App\Models\GarmentType::active()
                        ->orderBy('kategori')->orderBy('contoh_pakaian')
                        ->get()->groupBy('kategori');
                    $workTypes = \App\Models\WorkType::active()->orderBy('nama')->get();
                }
            }

            return view('dashboard', compact(
                'clockedIn', 'clockedOut', 'workStart', 'workEnd',
                'myTasks', 'hasPersonal', 'poolAssignments',
                'selfAssignMode', 'garmentKategori', 'garmentByKategori', 'workTypes'
            ));
        })->name('dashboard');
        Route::get('/absensi', fn() => redirect()->route('dashboard'))->name('attendance');
        Route::get('/absensi/masuk', fn() => view('attendance.clock-in'))->name('attendance.clock-in');
        Route::get('/absensi/pulang', fn() => view('attendance.clock-out'))->name('attendance.clock-out');
        Route::get('/absensi/izin', fn() => view('attendance.leave-request'))->name('attendance.leave-request');
        Route::get('/absensi/riwayat', fn() => view('attendance.history'))->name('attendance.history');
        Route::get('/profile', fn() => view('profile.index'))->name('profile');
        Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

        // Slip gaji: karyawan melihat slip final miliknya; pengelola boleh mencetak semua (dicek di controller)
        Route::get('/slip-gaji', [\App\Http\Controllers\PayslipController::class, 'index'])->name('payslips.index');
        Route::get('/slip-gaji/cetak', [\App\Http\Controllers\PayslipController::class, 'print'])->name('payslips.print');

        // Tugas pekerjaan (magang/siswa/education)
        Route::middleware('can:work.view_own')->group(function () {
            Route::get('/tugas/rekap', [WorkController::class, 'rekap'])->name('work.rekap');
            Route::get('/tugas', [WorkController::class, 'index'])->name('work.index');
            Route::get('/tugas/{assignment}', [WorkController::class, 'show'])->name('work.show');
            Route::post('/tugas/{assignment}/claim', [WorkController::class, 'claim'])->name('work.claim');
            Route::post('/tugas/{assignment}/start', [WorkController::class, 'start'])->name('work.start');
            Route::post('/tugas/{assignment}/submit', [WorkController::class, 'submit'])->name('work.submit');
            Route::post('/tugas/{assignment}/cancel-request', [WorkController::class, 'requestCancel'])->name('work.cancel-request');
            Route::post('/tugas/mandiri/buat', [WorkController::class, 'selfCreate'])->name('work.self-create');
            Route::post('/tugas/{assignment}/mandiri/edit', [WorkController::class, 'selfEdit'])->name('work.self-edit');
            Route::post('/tugas/{assignment}/mandiri/hapus', [WorkController::class, 'selfDelete'])->name('work.self-delete');
        });
    });
});

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class])
    ->get('/api/pool-count', function () {
        $count = \App\Models\WorkAssignment::openPool()->count();
        return response()->json(['count' => $count]);
    });

// ===== User Module Routes =====
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class])->prefix('user')->name('user.')->group(function () {
    Route::get('/scan-barcode', fn() => view('user.scan-barcode'))
        ->middleware(\App\Http\Middleware\EnsureUserModuleAccess::class . ':scan_barcode')
        ->name('scan-barcode');
    Route::get('/cetak-barcode', fn() => view('user.cetak-barcode'))
        ->middleware(\App\Http\Middleware\EnsureUserModuleAccess::class . ':cetak_barcode')
        ->name('cetak-barcode');
    Route::get('/distribusi', fn() => view('user.distribusi'))
        ->middleware(\App\Http\Middleware\EnsureUserModuleAccess::class . ':distribusi')
        ->name('distribusi');
});

Route::get('/', function () {
    $landingHost = config('app.landing_host');
    if ($landingHost && str_contains(request()->getHost(), $landingHost)) {
        return app(LandingController::class)->index();
    }
    return redirect()->route('login');
})->name('landing.index');

// Preview landing page untuk admin — tidak cek domain, hanya super_admin via token
Route::get('/landing-preview', function () {
    $token = request()->query('token');
    $validToken = hash('sha256', config('app.key') . 'landing_preview');
    if ($token !== $validToken) {
        abort(403);
    }
    return app(LandingController::class)->index();
})->name('landing.preview');

// Preview per-section — render hanya 1 section (hero/about/dna/dll)
Route::get('/landing-section-preview', function () {
    $token = request()->query('token');
    $validToken = hash('sha256', config('app.key') . 'landing_preview');
    if ($token !== $validToken) {
        abort(403);
    }

    $key = request()->query('section', 'hero');
    $section = \App\Models\LandingSection::with(['activeItems'])
        ->where('key', $key)
        ->first();

    if (!$section) {
        abort(404);
    }

    $featuredProducts = collect();
    if ($key === 'collections') {
        $featuredProducts = \App\Models\Product::where('status', 'aktif')
            ->with(['photos', 'garmentCategory'])
            ->latest()->limit(10)->get();
    }

    $waNumber = \App\Models\Setting::get('katalog_wa_number', '6281234567890');

    return view('landing.section-preview', compact('section', 'key', 'featuredProducts', 'waNumber'));
})->name('landing.section-preview');

// Setting katalog — hanya super_admin
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class])
    ->post('/admin/setting/katalog-wa', function (\Illuminate\Http\Request $request) {
        abort_unless(auth()->user()?->hasRole('super_admin'), 403);
        $wa = preg_replace('/\D/', '', $request->input('wa_number', ''));
        if (strlen($wa) < 8 || strlen($wa) > 15) {
            return response()->json(['ok' => false, 'message' => 'Nomor tidak valid (8–15 digit)']);
        }
        \App\Models\Setting::set('katalog_wa_number', $wa);
        return response()->json(['ok' => true]);
    });

// Upload foto sementara dari mode AI (admin only)
Route::post('/admin/ai/upload-photo', function (\Illuminate\Http\Request $req) {
    abort_unless(auth()->check() && auth()->user()->hasAnyRole(['super_admin','admin']), 403);
    $file = $req->file('photo');
    if (!$file || !$file->isValid()) return response()->json(['error' => 'File tidak valid'], 422);
    $path = $file->store('products', 'public');
    // Compress
    try {
        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
        $manager  = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $img      = $manager->read($fullPath);
        $img->scaleDown(width: 1280);
        $img->toJpeg(80)->save($fullPath);
    } catch (\Throwable $e) {}
    return response()->json(['path' => $path]);
})->middleware('web');

// E-Catalog PDF publik (target QR cetak) — file bisa diganti dari admin tanpa mengubah URL
Route::get('/katalogpdf', [\App\Http\Controllers\CatalogPdfController::class, 'show'])->name('catalogpdf.show');
Route::get('/katalogpdf/file', [\App\Http\Controllers\CatalogPdfController::class, 'file'])->name('catalogpdf.file');
Route::get('/katalogpdf/download', [\App\Http\Controllers\CatalogPdfController::class, 'download'])->name('catalogpdf.download');

// Katalog produk publik (tanpa login) — pakai product_code sebagai slug (tidak expose DB id)
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('products.show');

// Dummy preview URL — redirect ke katalog publik
Route::get('/bentar/dulu', fn() => redirect()->route('products.index'))->name('catalog.preview');
