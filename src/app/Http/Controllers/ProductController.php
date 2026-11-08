<?php

namespace App\Http\Controllers;

use App\Models\GarmentCategory;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Cast & sanitize input — cegah type juggling dan injection
        $kategori = request()->integer('kategori') ?: null;
        $search   = substr(strip_tags(request('search', '')), 0, 100) ?: null;

        $sort = request('sort', 'terbaru');

        $products = Product::with(['garmentCategory', 'photos'])
            ->where('status', 'aktif')
            ->when($kategori, fn ($q) => $q->where('garment_category_id', $kategori))
            ->when($search, fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($sort === 'harga_asc',  fn ($q) => $q->orderBy('harga'))
            ->when($sort === 'harga_desc', fn ($q) => $q->orderByDesc('harga'))
            ->when($sort === 'nama_az',    fn ($q) => $q->orderBy('nama'))
            ->when($sort === 'terbaru',    fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        $categories = GarmentCategory::where('aktif', true)
            ->whereHas('products', fn ($q) => $q->where('status', 'aktif'))
            ->orderBy('nama')
            ->get();

        return view('products.index', compact('products', 'categories', 'kategori', 'search'));
    }

    public function show(string $slug)
    {
        // Slug = product_code — tidak expose numeric ID di URL
        $product = Product::where('product_code', $slug)->where('status', 'aktif')->firstOrFail();
        $product->load(['garmentCategory', 'garmentType', 'photos', 'colors']);

        // Stok per ukuran: ['nama' => X, 'stok' => N]
        $unitsBySize = \App\Models\ProductUnit::with('size')
            ->where('product_id', $product->id)
            ->where('status', 'stok')
            ->get()
            ->groupBy(fn ($u) => $u->product_size_id ?? 0)
            ->map(fn ($units, $sizeId) => [
                'size_id'  => $sizeId,
                'size_nama'=> $units->first()->size?->nama ?? null,
                'stok'     => $units->count(),
            ])
            ->values();

        $stok = $unitsBySize->sum('stok');

        return view('products.show', compact('product', 'unitsBySize', 'stok'));
    }
}
