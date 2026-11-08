<?php

namespace App\Http\Controllers;

use App\Models\LandingSection;
use App\Models\Product;

class LandingController extends Controller
{
    public function index()
    {
        $sections = LandingSection::active()
            ->ordered()
            ->with(['activeItems'])
            ->get()
            ->keyBy('key');

        $waNumber = \App\Models\Setting::get('katalog_wa_number', '6281234567890');

        $featuredProducts = Product::where('status', 'aktif')
            ->with(['photos', 'garmentCategory'])
            ->latest()
            ->limit(10)
            ->get();

        $featuredCount = $featuredProducts->count();

        return view('landing.index', compact('sections', 'waNumber', 'featuredProducts', 'featuredCount'));
    }
}
