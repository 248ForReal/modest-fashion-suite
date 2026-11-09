<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Services\PayrollCalculator;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    /** Daftar slip final milik karyawan yang sedang login. */
    public function index()
    {
        $slips = Payslip::where('user_id', auth()->id())
            ->where('status', Payslip::STATUS_FINAL)
            ->orderByDesc('period')
            ->get();

        return view('payslip.index', compact('slips'));
    }

    /**
     * Halaman cetak (A4) satu atau beberapa slip: ?ids=1,2,3
     * Pengelola boleh semua slip (draft ditandai); karyawan hanya slip final miliknya.
     */
    public function print(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->take(300)
            ->values();

        abort_if($ids->isEmpty(), 404);

        $user  = $request->user();
        $slips = Payslip::with('user')
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (Payslip $slip) => $slip->visibleTo($user))
            ->sortBy(fn (Payslip $slip) => $slip->user->name . '|' . $slip->period)
            ->values();

        abort_if($slips->isEmpty(), 403);

        return view('payslip.print', [
            'slips'   => $slips,
            'company' => PayrollCalculator::companyName(),
        ]);
    }
}
