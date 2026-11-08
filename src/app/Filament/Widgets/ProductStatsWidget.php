<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductUnit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalProduk  = Product::where('status', 'aktif')->count();
        $stokTersedia = ProductUnit::where('status', 'stok')->count();
        $dalamProses  = ProductUnit::whereIn('status', ['dipesan', 'dikirim', 'sampai'])->count();
        $selesaiHari  = ProductUnit::where('status', 'selesai')
            ->whereDate('updated_at', today())
            ->count();

        return [
            Stat::make('Produk Aktif', $totalProduk)
                ->description('Total produk yang aktif dijual')
                ->color('primary')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Stok Tersedia', $stokTersedia)
                ->description('Unit siap dipesan')
                ->color($stokTersedia > 0 ? 'success' : 'danger')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Dalam Proses', $dalamProses)
                ->description('Dipesan / dikirim / dalam perjalanan')
                ->color($dalamProses > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-truck'),

            Stat::make('Selesai Hari Ini', $selesaiHari)
                ->description('Transaksi selesai hari ini')
                ->color('info')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
