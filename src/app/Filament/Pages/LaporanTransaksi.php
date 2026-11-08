<?php

namespace App\Filament\Pages;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanTransaksi extends Page
{
    protected string $view = 'filament.pages.laporan-transaksi';
    protected static ?string $navigationLabel = 'Laporan Transaksi';
    protected static ?string $title = 'Laporan Transaksi Inventory';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 6;

    public array $cfg = [
        'filter_tipe'        => '',
        'filter_warehouse'   => '',
        'filter_product'     => '',
        'filter_date_from'   => '',
        'filter_date_to'     => '',
        'per_page'           => 20,
        'page'               => 1,
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getGudangOptions(): array
    {
        return Warehouse::orderBy('nama')->pluck('nama', 'id')->toArray();
    }

    public function getProductOptions(): array
    {
        return Product::orderBy('nama')->pluck('nama', 'id')->toArray();
    }

    public function getTipeOptions(): array
    {
        return InventoryTransaction::TIPE_LABELS;
    }

    public function resetFilter(): void
    {
        $this->cfg['filter_tipe']      = '';
        $this->cfg['filter_warehouse'] = '';
        $this->cfg['filter_product']   = '';
        $this->cfg['filter_date_from'] = '';
        $this->cfg['filter_date_to']   = '';
        $this->cfg['page']             = 1;
    }

    public function applyFilter(): void
    {
        $this->cfg['page'] = 1;
    }

    public function goToPage(int $page): void
    {
        $this->cfg['page'] = $page;
    }

    private function buildQuery()
    {
        $q = InventoryTransaction::with(['product', 'warehouse', 'warehouseTujuan', 'createdBy'])
            ->orderByDesc('created_at');

        if ($this->cfg['filter_tipe']) {
            $q->where('tipe', $this->cfg['filter_tipe']);
        }
        if ($this->cfg['filter_warehouse']) {
            $q->where(function ($sub) {
                $sub->where('warehouse_id', (int) $this->cfg['filter_warehouse'])
                    ->orWhere('warehouse_tujuan_id', (int) $this->cfg['filter_warehouse']);
            });
        }
        if ($this->cfg['filter_product']) {
            $q->where('product_id', (int) $this->cfg['filter_product']);
        }
        if ($this->cfg['filter_date_from']) {
            $q->whereDate('created_at', '>=', $this->cfg['filter_date_from']);
        }
        if ($this->cfg['filter_date_to']) {
            $q->whereDate('created_at', '<=', $this->cfg['filter_date_to']);
        }

        return $q;
    }

    public function getTransaksi(): array
    {
        $perPage = (int) ($this->cfg['per_page'] ?? 20);
        $page    = (int) ($this->cfg['page'] ?? 1);

        $paginator = $this->buildQuery()->paginate($perPage, ['*'], 'page', $page);

        return [
            'data'          => $paginator->items(),
            'total'         => $paginator->total(),
            'per_page'      => $perPage,
            'current_page'  => $paginator->currentPage(),
            'last_page'     => $paginator->lastPage(),
            'from'          => $paginator->firstItem() ?? 0,
            'to'            => $paginator->lastItem() ?? 0,
        ];
    }

    public function getSummary(): array
    {
        $q = $this->buildQuery();

        $byTipe = (clone $q)->select('tipe', DB::raw('count(*) as total'), DB::raw('sum(jumlah) as total_unit'))
            ->reorder()
            ->groupBy('tipe')
            ->get();

        $summary = [];
        foreach (InventoryTransaction::TIPE_LABELS as $key => $label) {
            $row = $byTipe->firstWhere('tipe', $key);
            $summary[] = [
                'tipe'        => $key,
                'label'       => $label,
                'color'       => InventoryTransaction::TIPE_COLORS[$key] ?? 'gray',
                'count'       => $row?->total ?? 0,
                'total_unit'  => $row?->total_unit ?? 0,
            ];
        }

        return $summary;
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->buildQuery()->get();

        $filename = 'transaksi-inventory-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // BOM untuk Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nomor', 'Tanggal', 'Tipe', 'Produk', 'Kode Unit', 'Ukuran',
                'Gudang Asal', 'Gudang Tujuan', 'Jumlah',
                'Status Dari', 'Status Ke', 'Catatan', 'Oleh',
            ]);

            // Pre-load ukuran dari unit_code untuk efisiensi
            $unitCodes = $rows->whereNotNull('unit_code')->pluck('unit_code')->unique()->toArray();
            $ukuranMap = ProductUnit::with('size')
                ->whereIn('unit_code', $unitCodes)
                ->get()
                ->pluck('size.nama', 'unit_code')
                ->toArray();

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->nomor,
                    $row->created_at->format('d/m/Y H:i'),
                    InventoryTransaction::TIPE_LABELS[$row->tipe] ?? $row->tipe,
                    $row->product?->nama ?? '-',
                    $row->unit_code ?? '-',
                    ($row->unit_code ? ($ukuranMap[$row->unit_code] ?? '-') : '-'),
                    $row->warehouse?->nama ?? '-',
                    $row->warehouseTujuan?->nama ?? '-',
                    $row->jumlah ?? 0,
                    ProductUnit::STATUSES[$row->status_dari] ?? ($row->status_dari ?? '-'),
                    ProductUnit::STATUSES[$row->status_ke]   ?? ($row->status_ke   ?? '-'),
                    $row->catatan ?? '',
                    $row->createdBy?->name ?? 'Sistem',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
