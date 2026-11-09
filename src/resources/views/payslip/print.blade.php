<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Slip Gaji{{ $slips->count() === 1 ? ' - ' . $slips->first()->user->name . ' - ' . $slips->first()->periodLabel() : '' }}</title>
<style>
    @page { size: A4 portrait; margin: 10mm; }
    * { box-sizing: border-box; }
    html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { margin: 0; background: #f3f4f6; color: #111; font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; font-size: 14px; }

    .toolbar { position: sticky; top: 0; z-index: 5; display: flex; gap: 8px; align-items: center; justify-content: center;
               padding: 10px 16px; background: #fff; border-bottom: 1px solid #e5e7eb; }
    .toolbar button { cursor: pointer; border: 0; border-radius: 8px; padding: 8px 16px; font: inherit; font-weight: 600; }
    .btn-print { background: #262626; color: #fff; }
    .btn-back { background: #e5e7eb; color: #111; }

    .slip { position: relative; width: 190mm; max-width: 100%; margin: 16px auto; padding: 14mm 12mm;
            background: #fff; border: 1px solid #222; break-after: page; page-break-after: always; }
    .slip:last-of-type { break-after: auto; page-break-after: auto; }
    .draft { position: absolute; top: 4mm; right: 6mm; color: #b91c1c; font-weight: 700; font-size: 12px; letter-spacing: .08em; }

    .title { background: #262626; color: #fff; text-align: center; font-weight: 700; font-size: 19px; padding: 11px 8px; }
    .meta { display: flex; justify-content: space-between; gap: 16px; margin-top: 24px; font-weight: 600; }
    .meta table { border-collapse: collapse; }
    .meta td { padding: 4px 6px 4px 0; vertical-align: top; }
    .meta td.v { text-align: right; padding-left: 28px; }
    hr { border: 0; border-top: 1px solid #222; margin: 26px 0 22px; }

    h4 { margin: 0 0 6px; font-size: 15px; font-weight: 700; }
    table.rows { width: 100%; border-collapse: collapse; }
    table.rows td { padding: 5px 0; }
    table.rows td.l { padding-left: 34px; }
    table.rows td.c { width: 22px; text-align: center; }
    table.rows td.a { width: 150px; text-align: right; padding-right: 8px; }
    table.rows.info td.l { padding-left: 0; }

    table.bar { width: 100%; border-collapse: collapse; background: #262626; color: #fff; font-weight: 700; }
    table.bar td { padding: 7px 6px; }
    table.bar td.c { width: 22px; text-align: center; }
    table.bar td.a { width: 150px; text-align: right; padding-right: 8px; }
    .gap { height: 14px; }

    .transfer { margin-top: 22px; border: 1px solid #222; padding: 8px 10px; font-size: 13px; }
    .transfer b { font-style: italic; }
    .transfer table { width: 100%; border-collapse: collapse; margin-top: 6px; font-style: italic; }
    .transfer td { padding: 5px 0; }
    .transfer td.k { width: 90px; }
    .transfer td.c { width: 20px; }
    .transfer td.v { text-align: center; }
    .note { margin-top: 10px; font-size: 11px; color: #555; }

    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .slip { margin: 0 auto; border: 1px solid #222; }
    }
    @media (max-width: 640px) {
        .slip { padding: 14px 12px; }
        table.rows td.l { padding-left: 12px; }
        table.rows td.a, table.bar td.a { width: 104px; }
        .meta { flex-direction: column; gap: 0; }
    }
</style>
</head>
<body>
<div class="toolbar">
    <button type="button" class="btn-back" onclick="history.length > 1 ? history.back() : window.close()">← Kembali</button>
    <button type="button" class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

@foreach($slips as $slip)
    <section class="slip">
        @unless($slip->isFinal())
            <div class="draft">DRAFT — BELUM FINAL</div>
        @endunless

        <div class="title">SLIP GAJI KARYAWAN {{ mb_strtoupper($company) }}</div>

        <div class="meta">
            <table>
                <tr><td>Nama</td><td>:</td><td>{{ mb_strtoupper($slip->user->name) }}</td></tr>
                <tr><td>Periode</td><td>:</td><td>{{ $slip->periodLabel() }}</td></tr>
            </table>
            <table>
                <tr><td>Hari Kerja</td><td>:</td><td class="v">{{ $slip->work_days }}</td></tr>
                <tr><td>Jlh Baju</td><td>:</td><td class="v">{{ $slip->garment_count }}</td></tr>
            </table>
        </div>

        <hr>

        <h4>A. Benefit</h4>
        <table class="rows">
            <tr><td class="l">Hasil Kerja</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->piece_earnings) }}</td></tr>
            <tr><td class="l">Uang makan</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->meal_allowance) }}</td></tr>
            <tr><td class="l">Tunjangan</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->allowance) }}</td></tr>
            <tr><td class="l">Bonus Kinerja</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->performance_bonus) }}</td></tr>
        </table>

        <hr>

        <h4>B. Bantuan</h4>
        <table class="rows">
            <tr><td class="l">BPJS TK (ditanggung {{ ucwords(mb_strtolower($company)) }})*</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->employer_bpjs) }}</td></tr>
            <tr><td class="l">Bonus lainnya</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->other_bonus) }}</td></tr>
            <tr><td class="l">Jajan</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->pocket_money) }}</td></tr>
        </table>

        <hr>

        <table class="rows info">
            <tr><td class="l"><h4 style="margin:0">C. Lainnya</h4></td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->other_income) }}</td></tr>
        </table>

        <div class="gap"></div>

        <table class="bar">
            <tr><td>I. Total Pendapatan (A+B+C)</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->total_income, false) }}</td></tr>
            <tr><td>II. Pengurangan</td><td class="c">:</td><td class="a">{{ $slip->deduction > 0 ? \App\Models\Payslip::rupiah($slip->deduction) : '' }}</td></tr>
        </table>

        <div class="gap"></div>

        <table class="bar">
            <tr><td>Take Home Pay (I-II)</td><td class="c">:</td><td class="a">{{ \App\Models\Payslip::rupiah($slip->take_home_pay, false) }}</td></tr>
        </table>

        <div class="transfer">
            <b>Transfer ke Rekening</b>
            <table>
                <tr><td class="k">No Rek</td><td class="c">:</td><td class="v">{{ trim(($slip->bank_name ?: '') . ($slip->bank_name && $slip->bank_account ? '#' : '') . ($slip->bank_account ?: '')) ?: '-' }}</td></tr>
                <tr><td class="k">Tgl</td><td class="c">:</td><td class="v">{{ $slip->transfer_date?->format('d/m/Y') ?? '-' }}</td></tr>
            </table>
        </div>

        <div class="note">* BPJS TK dibayarkan perusahaan, hanya sebagai informasi dan tidak dijumlahkan ke Total Pendapatan.</div>
    </section>
@endforeach
</body>
</html>
