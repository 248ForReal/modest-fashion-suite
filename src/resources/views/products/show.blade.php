@php
    $waNumber = \App\Models\Setting::get('katalog_wa_number', '6281234567890');
    $photoUrl = $product->photos->isNotEmpty()
        ? \Illuminate\Support\Facades\Storage::url($product->photos->first()->photo_path)
        : null;
    $allPhotos = $product->photos->map(fn($p) => \Illuminate\Support\Facades\Storage::url($p->photo_path))->values()->toArray();
    $hasSizes = $unitsBySize->isNotEmpty() && $unitsBySize->first()['size_nama'] !== null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#A85C43">
    <title>{{ $product->nama }} — Atelier</title>
    <link rel="icon" type="image/jpeg" href="/images/logo.svg">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{-webkit-tap-highlight-color:transparent;scroll-behavior:smooth}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;background:#F7F3EC;min-height:100vh;color:#352B25;overscroll-behavior:none}
        img{display:block;max-width:100%}
        a{text-decoration:none;color:inherit}
        button{font-family:inherit;cursor:pointer}
        [x-cloak]{display:none!important}

        /* ── MONITOR ── */
        @media(min-width:700px){
            body{background:#1F1D1B;display:flex;justify-content:center;align-items:flex-start}
            .monitor-shell{width:430px;min-height:100vh;background:#F7F3EC;box-shadow:0 0 0 1px rgba(0,0,0,.25),0 32px 80px rgba(0,0,0,.5);position:relative;overflow-x:hidden;display:flex;flex-direction:column}
        }
        @media(max-width:699px){
            .monitor-shell{width:100%;position:relative;min-height:100vh;display:flex;flex-direction:column}
        }

        /* ── TOP NAV ── */
        .topnav{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid rgba(0,0,0,.06);padding:0 16px}
        .topnav-inner{display:flex;align-items:center;height:52px;gap:10px}
        .btn-back{display:flex;align-items:center;gap:6px;font-size:14px;font-weight:600;color:#75685D;background:none;border:none;padding:6px 0;flex-shrink:0}
        .btn-back svg{width:18px;height:18px}
        .topnav-title{flex:1;min-width:0;font-size:14px;font-weight:600;color:#3D2E26;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:center}
        .topnav-right{display:flex;align-items:center;gap:6px;flex-shrink:0}
        .lang-btn{height:28px;border:1.5px solid #E8DED0;border-radius:100px;padding:0 10px;font-size:11px;font-weight:700;background:#fff;color:#75685D;display:flex;align-items:center;gap:4px;transition:all .15s}
        .lang-btn:hover{border-color:#A85C43;color:#A85C43}
        .lang-btn svg{width:13px;height:13px}
        .topnav-cart{width:36px;height:36px;border-radius:10px;background:#F7F3EC;border:1.5px solid #E8DED0;display:flex;align-items:center;justify-content:center;position:relative}
        .topnav-cart svg{width:17px;height:17px;color:#A85C43}
        .cart-badge{position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;background:#A85C43;color:#fff;border-radius:100px;font-size:9px;font-weight:800;display:flex;align-items:center;justify-content:center;padding:0 3px;border:2px solid #fff}

        /* ── GALLERY ── */
        .gallery{position:relative;background:#E8DED0;aspect-ratio:1/1;overflow:hidden;user-select:none}
        .gallery-slides{display:flex;height:100%;transition:transform .3s cubic-bezier(.4,0,.2,1);will-change:transform}
        .gallery-slide{flex-shrink:0;width:100%;height:100%}
        .gallery-slide img{width:100%;height:100%;object-fit:cover}
        .gallery-no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#d1c4b8;flex-direction:column;gap:8px}
        .gallery-no-img svg{width:56px;height:56px}
        .gallery-dots{position:absolute;bottom:12px;left:0;right:0;display:flex;justify-content:center;gap:6px}
        .gallery-dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.5);transition:all .2s}
        .gallery-dot.active{width:18px;border-radius:3px;background:#fff}
        .gallery-thumbs{display:flex;gap:8px;padding:10px 16px;overflow-x:auto;scrollbar-width:none}
        .gallery-thumbs::-webkit-scrollbar{display:none}
        .gallery-thumb{width:54px;height:54px;border-radius:10px;overflow:hidden;flex-shrink:0;border:2px solid transparent;transition:border-color .15s}
        .gallery-thumb.active{border-color:#A85C43}
        .gallery-thumb img{width:100%;height:100%;object-fit:cover}
        .gallery-nav{position:absolute;top:50%;transform:translateY(-50%);width:34px;height:34px;background:rgba(255,255,255,.85);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#75685D;box-shadow:0 2px 8px rgba(0,0,0,.15)}
        .gallery-nav.prev{left:10px}
        .gallery-nav.next{right:10px}
        .gallery-nav svg{width:16px;height:16px}

        /* ── DETAIL ── */
        .detail{padding:16px 16px 0}
        .detail-cats{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
        .detail-cat{font-size:10px;font-weight:700;color:#A85C43;background:#F7F3EC;border:1px solid #E8DED0;padding:3px 9px;border-radius:100px;text-transform:uppercase;letter-spacing:.04em}
        .detail-nama{font-size:20px;font-weight:600;color:#3D2E26;line-height:1.25;margin-bottom:6px}
        .detail-harga{font-size:24px;font-weight:800;color:#A85C43;letter-spacing:-.01em;margin-bottom:12px}
        .detail-stok-row{display:flex;align-items:center;gap:10px;margin-bottom:16px}
        .stok-badge-ok{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;padding:5px 12px;border-radius:100px}
        .stok-badge-ok svg{width:12px;height:12px}
        .stok-badge-habis{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:#ef4444;background:#fef2f2;border:1px solid #fecaca;padding:5px 12px;border-radius:100px}
        .stok-badge-habis svg{width:12px;height:12px}
        .detail-code{font-size:11px;color:#bbb;font-weight:500}

        /* ── DIVIDER ── */
        .divider{height:1px;background:#E8DED0;margin:0 16px}

        /* ── SIZE PICKER ── */
        .section-block{padding:16px 16px 0}
        .section-title{font-size:12px;font-weight:800;color:#75685D;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
        .size-grid{display:flex;flex-wrap:wrap;gap:8px}
        .size-btn{position:relative;min-width:54px;height:44px;border-radius:12px;border:2px solid #E8DED0;background:#fff;font-size:13px;font-weight:700;color:#374151;padding:0 14px;transition:all .15s;display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1}
        .size-btn.selected{border-color:#A85C43;background:#F7F3EC;color:#A85C43}
        .size-btn.habis{opacity:.45;text-decoration:line-through;cursor:not-allowed;background:#f9fafb}
        .size-btn:not(.habis):hover{border-color:#B59A68}
        .size-btn-stok{font-size:9px;font-weight:600;color:#9ca3af;margin-top:1px}
        .size-btn.selected .size-btn-stok{color:#B59A68}

        /* ── QTY ── */
        .qty-row{display:flex;align-items:center;gap:12px;margin-top:12px}
        .qty-label{font-size:12px;font-weight:700;color:#75685D;text-transform:uppercase;letter-spacing:.04em}
        .qty-ctrl{display:flex;align-items:center;gap:0;border:1.5px solid #E8DED0;border-radius:12px;overflow:hidden;background:#fff}
        .qty-btn{width:38px;height:38px;background:none;border:none;font-size:18px;font-weight:700;color:#374151;display:flex;align-items:center;justify-content:center;transition:background .15s}
        .qty-btn:hover{background:#f9fafb}
        .qty-btn:disabled{opacity:.3;cursor:not-allowed}
        .qty-val{width:38px;text-align:center;font-size:15px;font-weight:600;color:#3D2E26}

        /* ── DESCRIPTION ── */
        .desc-block{padding:16px 16px 0}
        .desc-text{font-size:13px;color:#75685D;line-height:1.7}
        .desc-row{display:flex;gap:8px;padding:8px 0;border-bottom:1px solid #E8DED0;align-items:flex-start}
        .desc-row:last-child{border-bottom:none}
        .desc-key{font-size:12px;font-weight:700;color:#9ca3af;min-width:90px;padding-top:1px}
        .desc-val{font-size:13px;color:#374151;font-weight:600;flex:1}

        /* ── BOTTOM CTA ── */
        .cta-bar{position:sticky;bottom:0;z-index:40;background:#fff;border-top:1px solid #E8DED0;padding:10px 16px;padding-bottom:calc(10px + env(safe-area-inset-bottom));display:flex;gap:8px;align-items:center}
        .cta-back{height:46px;border-radius:14px;background:#E8DED0;border:none;color:#75685D;font-size:13px;font-weight:700;padding:0 16px;white-space:nowrap;flex-shrink:0}
        .cta-cart-btn{flex:1;height:46px;border-radius:14px;border:none;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .15s}
        .cta-cart-btn.ready{background:#A85C43;color:#fff}
        .cta-cart-btn.ready:hover{background:#8B4A35}
        .cta-cart-btn.blocked{background:#E8DED0;color:#9ca3af;cursor:not-allowed}
        .cta-cart-btn svg{width:17px;height:17px;flex-shrink:0}
        .cta-wa-btn{height:46px;width:46px;border-radius:14px;background:#25d366;color:#fff;border:none;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .cta-wa-btn svg{width:20px;height:20px}

        /* ── TOAST ── */
        .toast{position:fixed;top:72px;left:50%;transform:translateX(-50%);z-index:300;background:#1F1D1B;color:#fff;border-radius:14px;padding:10px 18px;font-size:13px;font-weight:600;white-space:nowrap;pointer-events:none;transition:opacity .3s}
        .toast.hidden{opacity:0}

        /* ── CART DRAWER ── */
        .drawer-overlay{position:fixed;inset:0;z-index:70;background:rgba(0,0,0,.5)}
        .drawer{position:fixed;bottom:0;left:50%;transform:translateX(-50%);z-index:71;background:#fff;border-radius:24px 24px 0 0;width:100%;max-width:430px;max-height:85vh;overflow-y:auto;padding-bottom:env(safe-area-inset-bottom)}
        .drawer-handle{width:40px;height:4px;background:#E8DED0;border-radius:100px;margin:12px auto 0}
        .drawer-head{padding:14px 18px 10px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E8DED0}
        .drawer-head h3{font-size:16px;font-weight:600;color:#3D2E26}
        .drawer-close{width:30px;height:30px;border-radius:50%;background:#E8DED0;border:none;display:flex;align-items:center;justify-content:center;color:#75685D}
        .drawer-close svg{width:16px;height:16px}
        .drawer-body{padding:12px 18px}
        .cart-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #F7F3EC}
        .cart-item:last-child{border-bottom:none}
        .cart-item-img{width:60px;height:60px;border-radius:12px;overflow:hidden;background:#E8DED0;flex-shrink:0}
        .cart-item-img img{width:100%;height:100%;object-fit:cover}
        .cart-item-info{flex:1;min-width:0}
        .cart-item-nama{font-size:13px;font-weight:600;color:#3D2E26}
        .cart-item-sub{font-size:11px;color:#9ca3af;margin-top:2px}
        .cart-item-harga{font-size:13px;font-weight:800;color:#A85C43;margin-top:4px}
        .cart-item-del{background:none;border:none;color:#d1d5db;padding:4px;transition:color .15s;flex-shrink:0}
        .cart-item-del:hover{color:#ef4444}
        .cart-item-del svg{width:18px;height:18px}
        .cart-empty-box{text-align:center;padding:40px 0;color:#9ca3af}
        .cart-empty-box svg{width:40px;height:40px;margin:0 auto 10px;opacity:.4}
        .cart-empty-box p{font-size:13px}
        .drawer-footer{padding:14px 18px;border-top:1px solid #E8DED0;background:#fff;position:sticky;bottom:0}
        .drawer-total-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        .drawer-total-label{font-size:13px;color:#75685D;font-weight:600}
        .drawer-total-val{font-size:18px;font-weight:800;color:#A85C43}
        .btn-checkout{width:100%;background:#25d366;color:#fff;border:none;border-radius:14px;padding:14px;font-size:14px;font-weight:800;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;transition:background .15s}
        .btn-checkout:hover{background:#16a34a}
        .btn-checkout svg{width:20px;height:20px;flex-shrink:0}
        .btn-clear{width:100%;margin-top:8px;background:none;border:none;color:#9ca3af;font-size:12px;padding:6px;font-family:inherit}

        /* ── COOKIE ── */
        .cookie-bar{position:fixed;bottom:0;left:50%;transform:translateX(-50%);z-index:200;background:#352B25;color:#fff;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%;max-width:430px}
        .cookie-bar p{flex:1;font-size:12px;line-height:1.5;min-width:180px}
        .cookie-btn{background:#A85C43;color:#fff;border:none;border-radius:10px;padding:9px 16px;font-size:12px;font-weight:700;white-space:nowrap}

        .spacer-bottom{height:20px}
    </style>
</head>
<body>
<div class="monitor-shell" x-data="app()">

{{-- TOP NAV --}}
<div class="topnav">
    <div class="topnav-inner">
        <button class="btn-back" onclick="history.length>1 ? history.back() : (window.location='{{ route('products.index') }}')">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            <span x-text="lang==='id' ? 'Kembali' : 'Back'"></span>
        </button>
        <div class="topnav-title">{{ $product->nama }}</div>
        <div class="topnav-right">
            <button class="lang-btn" @click="toggleLang()">
                <span x-text="lang==='id' ? '🇮🇩' : '🇬🇧'"></span>
                <span x-text="lang==='id' ? 'EN' : 'ID'"></span>
            </button>
            <div class="topnav-cart" @click="showCart=true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <div class="cart-badge" x-show="cartCount > 0" x-text="cartCount"></div>
            </div>
        </div>
    </div>
</div>

{{-- GALLERY --}}
<div class="gallery"
    @touchstart.passive="touchStartX = $event.changedTouches[0].clientX"
    @touchend.passive="swipe($event.changedTouches[0].clientX)">
    @if(count($allPhotos) > 0)
    <div class="gallery-slides" :style="'transform:translateX(-'+activePhoto*100+'%)'">
        @foreach($allPhotos as $i => $p)
        <div class="gallery-slide"><img src="{{ $p }}" alt="{{ $product->nama }}" loading="{{ $i==0 ? 'eager' : 'lazy' }}"></div>
        @endforeach
    </div>
    @if(count($allPhotos) > 1)
    <div class="gallery-dots">
        @foreach($allPhotos as $i => $p)
        <div class="gallery-dot" :class="activePhoto==={{ $i }} ? 'active' : ''"></div>
        @endforeach
    </div>
    <button class="gallery-nav prev" @click="prevPhoto()" x-show="activePhoto > 0">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button class="gallery-nav next" @click="nextPhoto()" x-show="activePhoto < {{ count($allPhotos)-1 }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
    @endif
    @else
    <div class="gallery-no-img">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span style="font-size:12px;color:#ccc">No image</span>
    </div>
    @endif
</div>

{{-- THUMBNAILS --}}
@if(count($allPhotos) > 1)
<div class="gallery-thumbs">
    @foreach($allPhotos as $i => $p)
    <div class="gallery-thumb"
        :class="activePhoto==={{ $i }} ? 'active' : ''"
        @click="activePhoto={{ $i }}">
        <img src="{{ $p }}" alt="thumb {{ $i+1 }}" loading="lazy">
    </div>
    @endforeach
</div>
@endif

{{-- DETAIL --}}
<div class="detail">
    <div class="detail-cats">
        @if($product->garmentCategory)<span class="detail-cat">{{ $product->garmentCategory->nama }}</span>@endif
        @if($product->garmentType)<span class="detail-cat">{{ $product->garmentType->contoh_pakaian }}</span>@endif
    </div>
    <div class="detail-nama">{{ $product->nama }}</div>
    <div class="detail-harga">Rp {{ number_format($product->harga, 0, ',', '.') }}</div>
    <div class="detail-stok-row">
        @if($stok > 0)
        <span class="stok-badge-ok">
            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span x-text="lang==='id' ? 'Tersedia ({{ $stok }} stok)' : 'In Stock ({{ $stok }})'"></span>
        </span>
        @else
        <span class="stok-badge-habis">
            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            <span x-text="lang==='id' ? 'Stok Habis' : 'Out of Stock'"></span>
        </span>
        @endif
        <span class="detail-code">{{ $product->product_code }}</span>
    </div>
</div>

{{-- SIZE PICKER --}}
@if($hasSizes)
<div class="divider"></div>
<div class="section-block">
    <div class="section-title">
        <span x-text="lang==='id' ? 'Pilih Ukuran' : 'Select Size'"></span>
        <span x-show="selectedSize" style="font-size:11px;color:#A85C43;font-weight:700;text-transform:none;letter-spacing:0" x-text="(lang==='id' ? 'Terpilih: ' : 'Selected: ') + selectedSize"></span>
    </div>
    <div class="size-grid">
        @foreach($unitsBySize as $row)
        @php $habis = $row['stok'] == 0; $sizeNama = $row['size_nama']; $sizeStok = $row['stok']; @endphp
        <button
            class="size-btn {{ $habis ? 'habis' : '' }}"
            :class="selectedSize === @json($sizeNama) ? 'selected' : ''"
            @click="{{ !$habis ? "selectSize(".json_encode($sizeNama).", ".$sizeStok.")" : '' }}"
            {{ $habis ? 'disabled' : '' }}>
            <span>{{ $sizeNama }}</span>
            <span class="size-btn-stok" x-text="lang==='id' ? '{{ $sizeStok }} pcs' : '{{ $sizeStok }} left'"></span>
        </button>
        @endforeach
    </div>
    <div class="qty-row" x-show="selectedSize">
        <span class="qty-label" x-text="lang==='id' ? 'Jumlah' : 'Qty'"></span>
        <div class="qty-ctrl">
            <button class="qty-btn" @click="qty > 1 && qty--" :disabled="qty <= 1">−</button>
            <span class="qty-val" x-text="qty"></span>
            <button class="qty-btn" @click="qty < maxQty && qty++" :disabled="qty >= maxQty">+</button>
        </div>
        <span style="font-size:12px;color:#9ca3af" x-text="lang==='id' ? 'Maks '+maxQty : 'Max '+maxQty"></span>
    </div>
</div>
@else
<div class="divider"></div>
<div class="section-block">
    <div class="section-title" x-text="lang==='id' ? 'Jumlah' : 'Quantity'"></div>
    <div class="qty-row">
        <div class="qty-ctrl">
            <button class="qty-btn" @click="qty > 1 && qty--" :disabled="qty <= 1">−</button>
            <span class="qty-val" x-text="qty"></span>
            <button class="qty-btn" @click="qty < maxQty && qty++" :disabled="qty >= maxQty">+</button>
        </div>
        <span style="font-size:12px;color:#9ca3af" x-text="lang==='id' ? 'Maks '+maxQty+' pcs' : 'Max '+maxQty+' pcs'"></span>
    </div>
</div>
@endif

{{-- DESKRIPSI --}}
@if($product->deskripsi || $product->colors->isNotEmpty())
<div class="divider" style="margin-top:16px"></div>
<div class="desc-block">
    <div class="section-title" x-text="lang==='id' ? 'Detail Produk' : 'Product Details'"></div>
    @if($product->colors->isNotEmpty())
    <div class="desc-row" style="align-items:flex-start">
        <span class="desc-key" x-text="lang==='id' ? 'Warna' : 'Colors'"></span>
        <div style="display:flex;flex-wrap:wrap;gap:6px;flex:1">
            @foreach($product->colors->sortBy('urutan') as $color)
            <div style="display:inline-flex;align-items:center;gap:5px;background:#f9fafb;border:1px solid #E8DED0;border-radius:100px;padding:4px 10px 4px 6px;">
                <span style="width:14px;height:14px;border-radius:50%;flex-shrink:0;border:1.5px solid rgba(0,0,0,.12);background:{{ $color->hex ?: '#E8DED0' }}"></span>
                <span style="font-size:12px;font-weight:600;color:#374151;white-space:nowrap">{{ $color->nama }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @if($product->garmentCategory)
    <div class="desc-row">
        <span class="desc-key" x-text="lang==='id' ? 'Kategori' : 'Category'"></span>
        <span class="desc-val">{{ $product->garmentCategory->nama }}</span>
    </div>
    @endif
    @if($product->deskripsi)
    <div style="margin-top:10px" class="desc-text">{{ $product->deskripsi }}</div>
    @endif
</div>
@endif

<div class="spacer-bottom"></div>

{{-- BOTTOM CTA --}}
<div class="cta-bar">
    <button class="cta-back" onclick="history.length>1 ? history.back() : (window.location='{{ route('products.index') }}')">
        <span x-text="lang==='id' ? 'Kembali' : 'Back'"></span>
    </button>

    <button
        class="cta-cart-btn"
        :class="canAddToCart() ? 'ready' : 'blocked'"
        @click="handleAddToCart()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span x-text="ctaLabel()"></span>
    </button>

    <a :href="buildWaProduct()" target="_blank" rel="noopener" class="cta-wa-btn" title="WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.55 4.107 1.514 5.833L.057 23.394a.5.5 0 00.549.549l5.56-1.457A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.866 9.866 0 01-5.031-1.377l-.36-.214-3.731.979.994-3.634-.234-.374A9.867 9.867 0 012.118 12C2.118 6.534 6.534 2.118 12 2.118c5.466 0 9.882 4.416 9.882 9.882 0 5.466-4.416 9.882-9.882 9.882z"/></svg>
    </a>
</div>

{{-- TOAST --}}
<div class="toast" :class="toastVisible ? '' : 'hidden'" x-text="toastMsg"></div>

{{-- CART DRAWER --}}
<template x-if="showCart">
<div>
    <div class="drawer-overlay" @click="showCart=false"></div>
    <div class="drawer">
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
                        <div class="cart-item-sub" x-text="(item.size ? (lang==='id' ? 'Ukuran: ' : 'Size: ') + item.size + ' · ' : '') + item.qty + ' pcs'"></div>
                        <div class="cart-item-harga" x-text="'Rp ' + (item.harga * item.qty).toLocaleString('id-ID')"></div>
                    </div>
                    <button class="cart-item-del" @click="removeItem(idx)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            </template>
        </div>
        <template x-if="cart.length > 0">
            <div class="drawer-footer">
                <div class="drawer-total-row">
                    <span class="drawer-total-label" x-text="lang==='id' ? 'Total' : 'Total'"></span>
                    <span class="drawer-total-val" x-text="'Rp ' + cartTotal.toLocaleString('id-ID')"></span>
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

</div>{{-- /monitor-shell --}}

<script>
const PRODUCT_DATA = {
    id: {{ $product->id }},
    code: @json($product->product_code),
    nama: @json($product->nama),
    harga: {{ (int) $product->harga }},
    photo: @json($photoUrl),
    hasSizes: {{ $hasSizes ? 'true' : 'false' }},
    sizes: @json($unitsBySize->values()),
    stok: {{ $stok }},
};

function app() {
    return {
        lang: (function(){ try{ return localStorage.getItem('atelier_lang')||'en'; }catch(e){ return 'en'; } })(),
        showCart: false,
        cart: [],
        cookiesAccepted: false,
        showCookieBanner: false,

        activePhoto: 0,
        touchStartX: 0,

        selectedSize: null,
        selectedStok: 0,
        qty: 1,

        toastVisible: false,
        toastMsg: '',
        _toastTimer: null,

        get cartCount(){ return this.cart.reduce(function(s,i){ return s+i.qty; }, 0); },
        get cartTotal(){ return this.cart.reduce(function(s,i){ return s+i.harga*i.qty; }, 0); },
        get maxQty(){
            if(PRODUCT_DATA.hasSizes){ return this.selectedStok > 0 ? this.selectedStok : 1; }
            return PRODUCT_DATA.stok > 0 ? PRODUCT_DATA.stok : 99;
        },

        init(){
            try{
                this.cookiesAccepted = localStorage.getItem('atelier_cookies') === '1';
                this.showCookieBanner = !this.cookiesAccepted;
                if(this.cookiesAccepted){
                    var raw = localStorage.getItem('atelier_cart');
                    if(raw){ this.cart = JSON.parse(raw); }
                }
            }catch(e){}
        },

        toggleLang(){
            this.lang = this.lang === 'id' ? 'en' : 'id';
            try{ localStorage.setItem('atelier_lang', this.lang); }catch(e){}
        },

        acceptCookies(){
            try{
                localStorage.setItem('atelier_cookies', '1');
                var raw = localStorage.getItem('atelier_cart');
                if(raw){ this.cart = JSON.parse(raw); }
            }catch(e){}
            this.cookiesAccepted = true;
            this.showCookieBanner = false;
        },

        nextPhoto(){
            var max = {{ max(0, count($allPhotos)-1) }};
            this.activePhoto = Math.min(this.activePhoto + 1, max);
        },
        prevPhoto(){
            this.activePhoto = Math.max(this.activePhoto - 1, 0);
        },
        swipe(endX){
            var diff = this.touchStartX - endX;
            if(Math.abs(diff) > 40){
                if(diff > 0){ this.nextPhoto(); } else { this.prevPhoto(); }
            }
        },

        selectSize(sizeName, stok){
            this.selectedSize = sizeName;
            this.selectedStok = stok;
            this.qty = 1;
        },

        canAddToCart(){
            if(PRODUCT_DATA.stok <= 0){ return false; }
            if(PRODUCT_DATA.hasSizes && !this.selectedSize){ return false; }
            return true;
        },

        ctaLabel(){
            if(PRODUCT_DATA.stok <= 0){
                return this.lang === 'id' ? 'Stok Habis' : 'Out of Stock';
            }
            if(PRODUCT_DATA.hasSizes && !this.selectedSize){
                return this.lang === 'id' ? 'Pilih Ukuran Dulu' : 'Select Size First';
            }
            return this.lang === 'id' ? 'Tambah ke Keranjang' : 'Add to Cart';
        },

        handleAddToCart(){
            if(!this.canAddToCart()){ return; }
            this.addToCart();
        },

        addToCart(){
            var key = PRODUCT_DATA.code + (this.selectedSize ? '_' + this.selectedSize : '');
            var existing = null;
            for(var i=0; i<this.cart.length; i++){
                if(this.cart[i].key === key){ existing = this.cart[i]; break; }
            }
            if(existing){
                existing.qty = Math.min(existing.qty + this.qty, this.maxQty);
            } else {
                this.cart.push({
                    key: key,
                    nama: PRODUCT_DATA.nama,
                    harga: PRODUCT_DATA.harga,
                    size: this.selectedSize,
                    qty: this.qty,
                    photo: PRODUCT_DATA.photo,
                });
            }
            this.save();
            this.qty = 1;
            this.showToast(this.lang === 'id' ? 'Ditambahkan ke keranjang!' : 'Added to cart!');
        },

        removeItem(idx){ this.cart.splice(idx, 1); this.save(); },
        clearCart(){ this.cart = []; this.save(); },

        save(){
            if(!this.cookiesAccepted){ return; }
            try{ localStorage.setItem('atelier_cart', JSON.stringify(this.cart)); }catch(e){}
        },

        showToast(msg){
            this.toastMsg = msg;
            this.toastVisible = true;
            clearTimeout(this._toastTimer);
            var self = this;
            this._toastTimer = setTimeout(function(){ self.toastVisible = false; }, 2500);
        },

        buildWaProduct(){
            var sz = this.selectedSize
                ? (this.lang === 'id' ? ' - Ukuran ' + this.selectedSize : ' - Size ' + this.selectedSize)
                : '';
            var msg = this.lang === 'id'
                ? 'Halo Atelier, saya tertarik dengan produk:\n' + PRODUCT_DATA.nama + sz + '\nHarga: Rp ' + PRODUCT_DATA.harga.toLocaleString('id-ID') + '\n\nApakah masih tersedia?'
                : 'Hello Atelier, I am interested in:\n' + PRODUCT_DATA.nama + sz + '\nPrice: Rp ' + PRODUCT_DATA.harga.toLocaleString('id-ID') + '\n\nIs it still available?';
            return 'https://wa.me/{{ $waNumber }}?text=' + encodeURIComponent(msg);
        },

        buildWaCheckout(){
            var self = this;
            var lines = this.cart.map(function(i){
                var sz = i.size ? ' (' + (self.lang==='id'?'Ukuran':'Size') + ': ' + i.size + ')' : '';
                return '• ' + i.nama + sz + ' x' + i.qty + ' = Rp ' + (i.harga*i.qty).toLocaleString('id-ID');
            });
            var total = 'Rp ' + this.cartTotal.toLocaleString('id-ID');
            var msg = this.lang === 'id'
                ? 'Halo Atelier, saya ingin pesan:\n\n' + lines.join('\n') + '\n\nTotal: ' + total + '\n\nMohon konfirmasinya, terima kasih!'
                : 'Hello Atelier, I would like to order:\n\n' + lines.join('\n') + '\n\nTotal: ' + total + '\n\nPlease confirm, thank you!';
            return 'https://wa.me/{{ $waNumber }}?text=' + encodeURIComponent(msg);
        }
    };
}
</script>
<script src="/js/alpine.min.js" defer></script>
</body>
</html>
