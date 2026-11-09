@extends('layouts.app')
@section('title', 'Slip Gaji - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Slip Gaji')

@section('content')
<div style="display:flex;flex-direction:column;gap:12px;">
    @forelse($slips as $slip)
        <a href="{{ route('payslips.print', ['ids' => $slip->id]) }}"
           style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;background:#fff;border:1px solid #f3f4f6;border-radius:16px;box-shadow:0 1px 2px rgba(0,0,0,.05);text-decoration:none;color:inherit;">
            <div style="min-width:0;">
                <div style="font-weight:600;font-size:15px;color:#111827;">{{ $slip->periodLabel() }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">
                    {{ $slip->work_days }} hari kerja · {{ $slip->garment_count }} baju
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:11px;color:#9ca3af;">Take Home Pay</div>
                <div style="font-weight:700;font-size:15px;color:#ea580c;">Rp {{ \App\Models\Payslip::rupiah($slip->take_home_pay, false) }}</div>
            </div>
        </a>
    @empty
        <div style="padding:32px 16px;text-align:center;background:#fff;border:1px solid #f3f4f6;border-radius:16px;color:#6b7280;font-size:14px;">
            Belum ada slip gaji. Slip akan muncul di sini setelah difinalkan oleh admin.
        </div>
    @endforelse
</div>
@endsection
