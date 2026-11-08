@php $waNumber = \App\Models\Setting::get('katalog_wa_number', '6281234567890'); @endphp
<!DOCTYPE html>
<html lang="id" x-data="indexApp()" :lang="lang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#A85C43">
    <title>Katalog Produk — Atelier</title>
    <link rel="icon" type="image/jpeg" href="/images/logo.svg">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    <meta name="description" content="Koleksi produk konveksi Atelier.">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{-webkit-tap-highlight-color:transparent;scroll-behavior:smooth}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;background:#F7F3EC;min-height:100vh;color:#352B25}
        img{display:block;max-width:100%}
        a{text-decoration:none;color:inherit}
        button{font-family:inherit}
        [x-cloak]{display:none!important}

        /* ── MONITOR (desktop) ── */
        @media(min-width:700px){
            body{background:#2e2e2e;display:flex;justify-content:center}
            .monitor-shell{width:430px;min-height:100vh;background:#F7F3EC;box-shadow:0 0 0 1px rgba(0,0,0,.25),0 32px 80px rgba(0,0,0,.5);position:relative;overflow-x:hidden}
        }
        @media(max-width:699px){
            .monitor-shell{width:100%;position:relative}
        }

        /* ── HEADER ── */
        .header{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid rgba(0,0,0,.06);padding:10px 16px}
        .header-inner{display:flex;align-items:center;gap:10px;justify-content:space-between}
        .header-logo{width:40px;height:40px;object-fit:contain;flex-shrink:0}
        .header-brand{flex:1;min-width:0;font-size:15px;font-weight:800;color:#352B25}
        .header-right{display:flex;align-items:center;gap:8px;flex-shrink:0}
        .lang-btn{height:30px;border:1.5px solid #DDD4C8;border-radius:100px;padding:0 10px;font-size:11px;font-weight:700;cursor:pointer;background:#fff;color:#75685D;display:flex;align-items:center;gap:4px;transition:all .15s}
        .lang-btn:hover{border-color:#A85C43;color:#A85C43}
        .lang-btn svg{width:13px;height:13px}
        .cart-btn{width:38px;height:38px;border-radius:12px;background:#FBF6EF;border:1.5px solid #D4B898;display:flex;align-items:center;justify-content:center;position:relative;cursor:pointer}
        .cart-btn svg{width:18px;height:18px;color:#A85C43}
        .cart-badge{position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;background:#A85C43;color:#fff;border-radius:100px;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;padding:0 4px;border:2px solid #fff}

        /* ── HERO ── */
        .hero{background:linear-gradient(135deg,#A85C43 0%,#A85C43 50%,#A85C43 100%);padding:22px 16px 26px;color:#fff;position:relative;overflow:hidden}
        .hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E")}
        .hero-badge{display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);border-radius:100px;padding:4px 10px;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px}
        .hero-badge svg{width:8px;height:8px}
        .hero h2{font-size:22px;font-weight:800;line-height:1.2;margin-bottom:6px}
        .hero p{font-size:13px;opacity:.88;line-height:1.55;margin-bottom:14px}
        .hero-stats{display:flex;gap:10px}
        .hero-stat{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);border-radius:12px;padding:8px 14px;flex:1;text-align:center}
        .hero-stat-n{font-size:18px;font-weight:800;line-height:1}
        .hero-stat-l{font-size:10px;opacity:.85;margin-top:2px;font-weight:500}

        /* ── SEARCH ── */
        .search-wrap{padding:14px 16px 0}
        .search-row{display:flex;gap:8px;align-items:stretch}
        .search-form{flex:1;background:#fff;border-radius:14px;display:flex;align-items:center;border:1.5px solid #DDD4C8;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:border-color .2s;min-width:0}
        .search-form:focus-within{border-color:#A85C43}
        .search-icon{padding:0 10px 0 12px;color:#75685D;flex-shrink:0;display:flex}
        .search-icon svg{width:16px;height:16px}
        .search-input{flex:1;padding:11px 0;font-size:14px;font-family:inherit;border:none;outline:none;background:transparent;color:#352B25;min-width:0}
        .search-input::placeholder{color:#bbb}
        .search-btn{background:#A85C43;color:#fff;border:none;padding:0 16px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;border-radius:0 12px 12px 0;flex-shrink:0;transition:background .15s;height:100%}
        .search-btn:hover{background:#8B4A35}
        /* filter sort */
        .sort-select-wrap{flex-shrink:0;position:relative}
        .sort-select{height:100%;padding:0 30px 0 12px;font-size:12px;font-weight:600;font-family:inherit;border:1.5px solid #DDD4C8;border-radius:12px;background:#fff;color:#75685D;cursor:pointer;outline:none;appearance:none;-webkit-appearance:none;transition:border-color .2s;white-space:nowrap}
        .sort-select:focus{border-color:#A85C43}
        .sort-arrow{position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#75685D}
        .sort-arrow svg{width:14px;height:14px}

        /* ── CATEGORIES ── */
        .cats-wrap{padding:12px 16px 0}
        .cats-scroll{display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none}
        .cats-scroll::-webkit-scrollbar{display:none}
        .cat-pill{display:inline-flex;align-items:center;padding:7px 14px;border-radius:100px;font-size:12px;font-weight:600;white-space:nowrap;flex-shrink:0;border:1.5px solid transparent;transition:all .15s;cursor:pointer}
        .cat-pill.active{background:#A85C43;color:#fff;border-color:#A85C43}
        .cat-pill.inactive{background:#fff;color:#75685D;border-color:#DDD4C8}

        /* ── SECTION LABEL ── */
        .section-wrap{padding:16px 16px 10px}
        .section-label{font-size:12px;font-weight:700;color:#75685D;letter-spacing:.05em;text-transform:uppercase}

        /* ── GRID ── */
        .grid-wrap{padding:0 16px}
        .product-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}

        /* ── CARD ── */
        .card{background:#fff;border-radius:16px;overflow:hidden;border:1px solid rgba(0,0,0,.06);box-shadow:0 1px 4px rgba(0,0,0,.05);display:flex;flex-direction:column;transition:transform .15s}
        .card:active{transform:scale(.97)}
        .card-img{aspect-ratio:1/1;overflow:hidden;background:#E8DED0;position:relative}
        .card-img img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
        .card:hover .card-img img{transform:scale(1.04)}
        .card-no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#d1c4b8}
        .card-no-img svg{width:34px;height:34px}
        .card-sold-badge{position:absolute;top:8px;left:8px;background:#ef4444;color:#fff;font-size:9px;font-weight:700;padding:3px 8px;border-radius:100px;text-transform:uppercase;letter-spacing:.04em}
        .card-body{padding:10px 12px 12px;flex:1;display:flex;flex-direction:column;gap:3px}
        .card-cat{font-size:10px;font-weight:600;color:#A85C43;text-transform:uppercase;letter-spacing:.04em}
        .card-nama{font-size:13px;font-weight:600;color:#3D2E26;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .card-harga{font-size:14px;font-weight:700;color:#A85C43;margin-top:4px}
        .card-stok-ok{display:inline-block;font-size:9px;background:#f0fdf4;color:#16a34a;padding:2px 7px;border-radius:100px;font-weight:700}
        .card-stok-habis{display:inline-block;font-size:9px;background:#fef2f2;color:#ef4444;padding:2px 7px;border-radius:100px;font-weight:700}

        /* ── EMPTY ── */
        .empty{padding:48px 16px;text-align:center}
        .empty-icon{width:72px;height:72px;border-radius:24px;background:#fff;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .empty-icon svg{width:32px;height:32px;color:#d1c4b8}
        .empty h3{font-size:16px;font-weight:700;color:#374151;margin-bottom:6px}
        .empty p{font-size:13px;color:#75685D;line-height:1.5}
        .empty-btn{display:inline-block;margin-top:16px;background:#A85C43;color:#fff;padding:10px 24px;border-radius:100px;font-size:13px;font-weight:700}

        /* ── PAGINATION ── */
        .pagi-wrap{padding:20px 16px 32px}
        .pagi{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap}
        .pagi a,.pagi span{display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;border-radius:12px;font-size:13px;font-weight:600;padding:0 10px;border:1.5px solid #DDD4C8;background:#fff;color:#75685D;transition:all .15s}
        .pagi a:hover{border-color:#A85C43;color:#A85C43}
        .pagi span[aria-current="page"]{background:#A85C43;border-color:#A85C43;color:#fff}
        .pagi span.disabled{opacity:.4;pointer-events:none}

        /* ── FOOTER ── */
        .footer{background:#fff;border-top:1px solid #E8DED0;padding:24px 16px;text-align:center}
        .footer img{width:48px;height:48px;object-fit:contain;margin:0 auto 10px}
        .footer-brand{font-size:14px;font-weight:800;color:#352B25;margin-bottom:2px}
        .footer-copy{font-size:11px;color:#bbb;margin-top:12px}

        /* ── WA FLOAT ── */
        .wa-float{position:fixed;bottom:24px;right:16px;z-index:49;background:#25d366;color:#fff;border-radius:18px;display:flex;align-items:center;gap:8px;padding:12px 16px;font-size:13px;font-weight:700;box-shadow:0 4px 20px rgba(37,211,102,.45);transition:transform .15s}
        .wa-float:active{transform:scale(.96)}
        .wa-float svg{width:22px;height:22px;flex-shrink:0}
        @media(min-width:700px){
            .wa-float{right:calc(50% - 430px/2 + 16px)}
        }

        /* ── CART DRAWER ── */
        .drawer-overlay{position:fixed;inset:0;z-index:70;background:rgba(0,0,0,.5)}
        .drawer{position:fixed;bottom:0;left:50%;transform:translateX(-50%);z-index:71;background:#fff;border-radius:24px 24px 0 0;width:100%;max-width:430px;max-height:85vh;overflow-y:auto;padding-bottom:env(safe-area-inset-bottom)}
        .drawer-handle{width:40px;height:4px;background:#DDD4C8;border-radius:100px;margin:12px auto 0}
        .drawer-head{padding:14px 18px 10px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E8DED0}
        .drawer-head h3{font-size:16px;font-weight:800;color:#352B25}
        .drawer-close{width:30px;height:30px;border-radius:50%;background:#E8DED0;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#75685D}
        .drawer-close svg{width:16px;height:16px}
        .drawer-body{padding:12px 18px}
        .cart-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #F7F3EC}
        .cart-item:last-child{border-bottom:none}
        .cart-item-img{width:60px;height:60px;border-radius:12px;overflow:hidden;background:#E8DED0;flex-shrink:0}
        .cart-item-img img{width:100%;height:100%;object-fit:cover}
        .cart-item-info{flex:1;min-width:0}
        .cart-item-nama{font-size:13px;font-weight:700;color:#352B25}
        .cart-item-sub{font-size:11px;color:#75685D;margin-top:2px}
        .cart-item-harga{font-size:13px;font-weight:800;color:#A85C43;margin-top:4px}
        .cart-item-del{background:none;border:none;cursor:pointer;color:#d1d5db;padding:4px;transition:color .15s;flex-shrink:0}
        .cart-item-del:hover{color:#ef4444}
        .cart-item-del svg{width:18px;height:18px}
        .cart-empty-box{text-align:center;padding:40px 0;color:#75685D}
        .cart-empty-box svg{width:40px;height:40px;margin:0 auto 10px;opacity:.4}
        .cart-empty-box p{font-size:13px}
        .drawer-footer{padding:14px 18px;border-top:1px solid #E8DED0;background:#fff;position:sticky;bottom:0}
        .drawer-total-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        .drawer-total-label{font-size:13px;color:#75685D;font-weight:600}
        .drawer-total-val{font-size:18px;font-weight:800;color:#A85C43}
        .btn-checkout{width:100%;background:#25d366;color:#fff;border:none;border-radius:14px;padding:14px;font-size:14px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;transition:background .15s}
        .btn-checkout:hover{background:#16a34a}
        .btn-checkout svg{width:20px;height:20px;flex-shrink:0}
        .btn-clear{width:100%;margin-top:8px;background:none;border:none;color:#75685D;font-size:12px;cursor:pointer;padding:6px;font-family:inherit}

        /* ── COOKIE ── */
        .cookie-bar{position:fixed;bottom:0;left:50%;transform:translateX(-50%);z-index:200;background:#1F1D1B;color:#fff;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%;max-width:430px}
        .cookie-bar p{flex:1;font-size:12px;line-height:1.5;min-width:180px}
        .cookie-btn{background:#A85C43;color:#fff;border:none;border-radius:10px;padding:9px 16px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;white-space:nowrap}
    </style>
</head>
<body>
<div class="monitor-shell">

{{-- HEADER --}}
<header class="header">
    <div class="header-inner">
        <img src="/images/logo.svg" alt="Atelier" class="header-logo">
        <div class="header-brand"></div>
        <div class="header-right">
            <button class="lang-btn" @click="toggleLang()">
                <span x-text="lang==='id' ? '🇮🇩' : '🇬🇧'"></span>
                <span x-text="lang==='id' ? 'EN' : 'ID'"></span>
            </button>
            <div class="cart-btn" @click="showCart=true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <div class="cart-badge" x-show="cartCount > 0" x-text="cartCount"></div>
            </div>
        </div>
    </div>
</header>

{{-- HERO --}}
<div class="hero">
    <div class="hero-badge">
        <svg viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
        <span x-text="lang==='id' ? 'Produk Terbaru' : 'New Arrivals'"></span>
    </div>
    <h2 x-text="lang==='id' ? 'Koleksi Atelier' : 'Atelier Collection'"></h2>
    <p x-text="lang==='id' ? 'Baju, kemeja & kaos berkualitas langsung dari konveksi kami.' : 'Quality garments direct from our factory.'"></p>
    @php $totalProduk = $products->total(); @endphp
    <div class="hero-stats">
        <div class="hero-stat">
            <div class="hero-stat-n">{{ $totalProduk }}+</div>
            <div class="hero-stat-l" x-text="lang==='id' ? 'Produk' : 'Products'"></div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-n">{{ $categories->count() }}</div>
            <div class="hero-stat-l" x-text="lang==='id' ? 'Kategori' : 'Categories'"></div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-n">✓</div>
            <div class="hero-stat-l" x-text="lang==='id' ? 'Bisa Custom' : 'Custom Orders'"></div>
        </div>
    </div>
</div>

{{-- SEARCH --}}
<div class="search-wrap">
    <form method="GET" action="{{ route('products.index') }}" class="search-row" id="search-form">
        <div class="search-form">
            <div class="search-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if($kategori)<input type="hidden" name="kategori" value="{{ $kategori }}">@endif
            <input type="text" name="search" value="{{ $search }}"
                :placeholder="lang==='id' ? 'Cari produk...' : 'Search products...'"
                class="search-input" autocomplete="off">
            <button type="submit" class="search-btn" x-text="lang==='id' ? 'Cari' : 'Search'"></button>
        </div>
        <div class="sort-select-wrap">
            <select name="sort" class="sort-select" onchange="document.getElementById('search-form').submit()">
                <option value="terbaru" {{ (request('sort','terbaru')==='terbaru') ? 'selected' : '' }} x-text="lang==='id' ? 'Terbaru' : 'Newest'">Terbaru</option>
                <option value="harga_asc" {{ request('sort')==='harga_asc' ? 'selected' : '' }} x-text="lang==='id' ? 'Harga ↑' : 'Price ↑'">Harga ↑</option>
                <option value="harga_desc" {{ request('sort')==='harga_desc' ? 'selected' : '' }} x-text="lang==='id' ? 'Harga ↓' : 'Price ↓'">Harga ↓</option>
                <option value="nama_az" {{ request('sort')==='nama_az' ? 'selected' : '' }} x-text="lang==='id' ? 'A–Z' : 'A–Z'">A–Z</option>
            </select>
            <div class="sort-arrow"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
        </div>
    </form>
</div>

{{-- KATEGORI --}}
<div class="cats-wrap">
    <div class="cats-scroll">
        <a href="{{ route('products.index', ['search' => $search]) }}"
            class="cat-pill {{ !$kategori ? 'active' : 'inactive' }}"
            x-text="lang==='id' ? 'Semua' : 'All'"></a>
        @foreach ($categories as $cat)
        <a href="{{ route('products.index', ['kategori' => $cat->id, 'search' => $search]) }}"
            class="cat-pill {{ $kategori == $cat->id ? 'active' : 'inactive' }}">{{ $cat->nama }}</a>
        @endforeach
    </div>
</div>

{{-- PRODUK --}}
@if ($products->isEmpty())
<div class="empty">
    <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/></svg>
    </div>
    <h3 x-text="lang==='id' ? 'Produk tidak ditemukan' : 'No products found'"></h3>
    <p x-text="lang==='id' ? 'Coba kata kunci lain atau lihat semua kategori.' : 'Try another keyword or browse all categories.'"></p>
    <a href="{{ route('products.index') }}" class="empty-btn" x-text="lang==='id' ? 'Lihat Semua' : 'See All'"></a>
</div>
@else
<div class="section-wrap">
    <span class="section-label">
        @if($search)"{{ $search }}"@elseif($kategori){{ $categories->firstWhere('id',$kategori)?->nama ?? '' }}@else<span x-text="lang==='id' ? 'Semua Produk' : 'All Products'"></span>@endif
        <span style="color:#bbb;font-weight:500;font-size:11px;text-transform:none;letter-spacing:0;margin-left:6px">{{ $products->total() }} item</span>
    </span>
</div>

<div class="grid-wrap">
    <div class="product-grid">
        @foreach ($products as $product)
        <a href="{{ route('products.show', $product->product_code) }}" class="card">
            <div class="card-img">
                @if($product->photos->isNotEmpty())
                <img src="{{ Storage::url($product->photos->first()->photo_path) }}" alt="{{ $product->nama }}" loading="lazy">
                @else
                <div class="card-no-img">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif
                @if($product->jumlah == 0)<div class="card-sold-badge" x-text="lang==='id' ? 'Habis' : 'Sold Out'"></div>@endif
            </div>
            <div class="card-body">
                @if($product->garmentCategory)<div class="card-cat">{{ $product->garmentCategory->nama }}</div>@endif
                <div class="card-nama">{{ $product->nama }}</div>
                <div class="card-harga">Rp {{ number_format($product->harga,0,',','.') }}</div>
                <div style="margin-top:4px">
                    @if($product->jumlah > 0)
                    <span class="card-stok-ok" x-text="lang==='id' ? 'Stok {{ $product->jumlah }}' : '{{ $product->jumlah }} left'"></span>
                    @else
                    <span class="card-stok-habis" x-text="lang==='id' ? 'Habis' : 'Sold Out'"></span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>

<div class="pagi-wrap">
    {{ $products->onEachSide(1)->links('vendor.pagination.simple-custom') }}
</div>
@endif

{{-- FOOTER --}}
<footer class="footer">
    <img src="/images/logo.svg" alt="Atelier">
    <div class="footer-brand">Atelier</div>
    <div class="footer-copy">&copy; {{ date('Y') }} Atelier. All rights reserved.</div>
</footer>

</div>{{-- /monitor-shell --}}

{{-- WA FLOAT --}}
<a href="https://wa.me/{{ $waNumber }}?text=Halo%20Atelier%2C%20saya%20ingin%20tanya%20produk" target="_blank" rel="noopener" class="wa-float" x-show="!showCart">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.55 4.107 1.514 5.833L.057 23.394a.5.5 0 00.549.549l5.56-1.457A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.866 9.866 0 01-5.031-1.377l-.36-.214-3.731.979.994-3.634-.234-.374A9.867 9.867 0 012.118 12C2.118 6.534 6.534 2.118 12 2.118c5.466 0 9.882 4.416 9.882 9.882 0 5.466-4.416 9.882-9.882 9.882z"/></svg>
    <span x-text="lang==='id' ? 'Hubungi Kami' : 'Contact Us'"></span>
</a>

{{-- CART DRAWER --}}
<template x-if="showCart">
<div>
    <div class="drawer-overlay" @click="showCart=false"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="drawer"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="transform translate-y-full" x-transition:enter-end="transform translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform translate-y-0" x-transition:leave-end="transform translate-y-full">
        <div class="drawer-handle"></div>
        <div class="drawer-head">
            <h3 x-text="lang==='id' ? 'Keranjang' : 'Cart'"></h3>
            <button class="drawer-close" @click="showCart=false"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="drawer-body">
            <template x-if="cart.length === 0">
                <div class="cart-empty-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p x-text="lang==='id' ? 'Keranjang masih kosong' : 'Your cart is empty'"></p>
                </div>
            </template>
            <template x-for="(item, idx) in cart" :key="idx">
                <div class="cart-item">
                    <div class="cart-item-img"><img :src="item.photo||''" :alt="item.nama" onerror="this.style.display='none'"></div>
                    <div class="cart-item-info">
                        <div class="cart-item-nama" x-text="item.nama"></div>
                        <div class="cart-item-sub" x-text="(item.size?(lang==='id'?'Ukuran: ':'Size: ')+item.size+' · ':'')+item.qty+' pcs'"></div>
                        <div class="cart-item-harga" x-text="'Rp '+(item.harga*item.qty).toLocaleString('id-ID')"></div>
                    </div>
                    <button class="cart-item-del" @click="removeItem(idx)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            </template>
        </div>
        <template x-if="cart.length > 0">
            <div class="drawer-footer">
                <div class="drawer-total-row">
                    <span class="drawer-total-label" x-text="lang==='id' ? 'Total' : 'Total'"></span>
                    <span class="drawer-total-val" x-text="'Rp '+cartTotal.toLocaleString('id-ID')"></span>
                </div>
                <a :href="buildWaCheckout()" target="_blank" rel="noopener" class="btn-checkout">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.55 4.107 1.514 5.833L.057 23.394a.5.5 0 00.549.549l5.56-1.457A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.866 9.866 0 01-5.031-1.377l-.36-.214-3.731.979.994-3.634-.234-.374A9.867 9.867 0 012.118 12C2.118 6.534 6.534 2.118 12 2.118c5.466 0 9.882 4.416 9.882 9.882 0 5.466-4.416 9.882-9.882 9.882z"/></svg>
                    <span x-text="lang==='id' ? 'Checkout via WhatsApp' : 'Checkout via WhatsApp'"></span>
                </a>
                <button class="btn-clear" @click="clearCart()" x-text="lang==='id' ? 'Kosongkan keranjang' : 'Clear cart'"></button>
            </div>
        </template>
    </div>
</div>
</template>

{{-- COOKIE --}}
<div class="cookie-bar" x-show="showCookieBanner" x-cloak>
    <p x-text="lang==='id' ? 'Kami pakai cookies untuk menyimpan keranjang belanja kamu.' : 'We use cookies to save your shopping cart.'"></p>
    <button class="cookie-btn" @click="acceptCookies()" x-text="lang==='id' ? 'Setuju' : 'Accept'"></button>
</div>

<script>
function indexApp() {
    return {
        lang: (function(){ try{ return localStorage.getItem('atelier_lang')||'en' }catch(e){ return 'en' } })(),
        showCart: false,
        cart: [],
        cookiesAccepted: false,
        showCookieBanner: false,

        get cartCount(){ return this.cart.reduce((s,i)=>s+i.qty,0) },
        get cartTotal(){ return this.cart.reduce((s,i)=>s+i.harga*i.qty,0) },

        init(){
            try{
                this.cookiesAccepted = localStorage.getItem('atelier_cookies')==='1';
                this.showCookieBanner = !this.cookiesAccepted;
                if(this.cookiesAccepted){
                    const raw = localStorage.getItem('atelier_cart');
                    if(raw) this.cart = JSON.parse(raw);
                }
            }catch(e){}
        },
        toggleLang(){
            this.lang = this.lang==='id' ? 'en' : 'id';
            try{ localStorage.setItem('atelier_lang', this.lang) }catch(e){}
        },
        acceptCookies(){
            try{
                localStorage.setItem('atelier_cookies','1');
                const raw = localStorage.getItem('atelier_cart');
                if(raw) this.cart = JSON.parse(raw);
            }catch(e){}
            this.cookiesAccepted = true;
            this.showCookieBanner = false;
        },
        removeItem(idx){ this.cart.splice(idx,1); this.save() },
        clearCart(){ this.cart=[]; this.save() },
        save(){
            if(!this.cookiesAccepted) return;
            try{ localStorage.setItem('atelier_cart', JSON.stringify(this.cart)) }catch(e){}
        },
        buildWaCheckout(){
            const lines = this.cart.map(i=>{
                const sz = i.size ? ` (${this.lang==='id'?'Ukuran':'Size'}: ${i.size})` : '';
                return `• ${i.nama}${sz} x${i.qty} = Rp ${(i.harga*i.qty).toLocaleString('id-ID')}`;
            });
            const total = 'Rp '+this.cartTotal.toLocaleString('id-ID');
            const msg = this.lang==='id'
                ? `Halo Atelier, saya ingin pesan:\n\n${lines.join('\n')}\n\nTotal: ${total}\n\nMohon konfirmasinya, terima kasih!`
                : `Hello Atelier, I would like to order:\n\n${lines.join('\n')}\n\nTotal: ${total}\n\nPlease confirm, thank you!`;
            return 'https://wa.me/{{ $waNumber }}?text='+encodeURIComponent(msg);
        }
    }
}
</script>
<script src="/js/alpine.min.js" defer></script>
</body>
</html>
