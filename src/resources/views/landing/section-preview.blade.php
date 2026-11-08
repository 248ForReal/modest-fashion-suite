@php
    $lang = 'en';
    $vw = request()->query('vw', '390');
    $isDesktop = $vw >= 900;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview — {{ $key }}</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{width:100%;height:auto;background:#F7F3EC;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;-webkit-font-smoothing:antialiased;color:#352B25}
        img{display:block;max-width:100%}
        a{text-decoration:none;color:inherit}
        button{font-family:inherit;cursor:pointer;border:none;background:none;padding:0}
        [x-cloak]{display:none!important}

        /* SECTION TOKENS */
        .section{padding:72px 24px}
        .section-light{background:#F7F3EC}
        .section-sand{background:#EDE6DA}
        .section-dark{background:#1F1D1B;color:#F7F3EC}
        .section-inner{max-width:1200px;margin:0 auto}
        .eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#B59A68;margin-bottom:14px}
        .eyebrow::before{content:'';display:inline-block;width:24px;height:1px;background:#B59A68}
        .section-title{font-size:26px;font-weight:700;line-height:1.2;color:#1F1D1B;letter-spacing:-.02em;margin-bottom:12px}
        .section-subtitle{font-size:14px;color:#75685D;line-height:1.65;margin-bottom:8px}
        .section-body{font-size:14px;color:#75685D;line-height:1.75}
        .btn-outline{display:inline-block;border:1.5px solid #352B25;color:#352B25;border-radius:4px;padding:12px 28px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}

        @media(min-width:900px){
            .section{padding:100px 60px}
            .section-inner{padding:0}
            .section-title{font-size:36px}
            .eyebrow{font-size:11px;margin-bottom:16px}
        }

        /* HERO */
        .hero-section{background:#1F1D1B;position:relative}
        .hero-desktop-inner{position:relative}
        .hero-carousel{position:relative;aspect-ratio:3/4;overflow:hidden}
        .hero-slide{position:absolute;inset:0;opacity:0;transition:opacity .8s ease}
        .hero-slide.active{opacity:1}
        .hero-slide img{width:100%;height:100%;object-fit:cover;object-position:top center}
        .hero-overlay{position:absolute;inset:0;background:linear-gradient(160deg,rgba(31,29,27,0) 30%,rgba(31,29,27,.75) 70%,rgba(31,29,27,.96) 100%)}
        .hero-text-col{position:absolute;left:0;right:0;bottom:0;padding:32px 24px 40px;color:#F7F3EC;z-index:2}
        .hero-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#B59A68;margin-bottom:16px;opacity:.9}
        .hero-eyebrow::before{content:'';display:inline-block;width:20px;height:1px;background:#B59A68}
        .hero-text-col h1{font-size:30px;font-weight:700;line-height:1.15;letter-spacing:-.02em;margin-bottom:14px;color:#F7F3EC;max-width:300px}
        .hero-text-col p{font-size:13px;line-height:1.65;color:rgba(247,243,236,.75);margin-bottom:24px;max-width:280px}
        .hero-cta-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px}
        .btn-hero-primary{background:#A85C43;color:#fff;border-radius:4px;padding:13px 24px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;display:inline-block}
        .btn-hero-secondary{background:transparent;border:1px solid rgba(247,243,236,.3);color:#F7F3EC;border-radius:4px;padding:13px 24px;font-size:12px;font-weight:600;letter-spacing:.04em;display:inline-block}
        .hero-dots{display:flex;gap:6px;align-items:center}
        .hero-dot{width:18px;height:2px;border-radius:2px;background:rgba(247,243,236,.3);cursor:pointer;transition:all .3s}
        .hero-dot.active{background:#B59A68;width:32px}
        @media(min-width:900px){
            .hero-section{background:#F7F3EC;padding:80px 0}
            .hero-desktop-inner{display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:80px;max-width:1200px;margin:0 auto;padding:0 60px;position:static}
            .hero-carousel{aspect-ratio:4/5;border-radius:2px;box-shadow:0 32px 80px rgba(31,29,27,.2);order:2}
            .hero-overlay{display:none}
            .hero-text-col{position:static;padding:0;color:#1F1D1B;order:1}
            .hero-eyebrow{color:#B59A68;opacity:1}
            .hero-text-col h1{font-size:52px;line-height:1.08;letter-spacing:-.03em;color:#1F1D1B;max-width:480px;margin-bottom:20px}
            .hero-text-col p{font-size:15px;color:#75685D;max-width:380px;margin-bottom:36px}
            .btn-hero-secondary{border-color:rgba(53,43,37,.25);color:#352B25}
            .hero-dot{background:#DDD4C8}
            .hero-dot.active{background:#A85C43}
        }

        /* ABOUT */
        .about-grid{margin-top:40px}
        .about-img{border-radius:2px;overflow:hidden;margin-bottom:32px;box-shadow:0 8px 32px rgba(31,29,27,.1)}
        .about-img img{width:100%;height:auto;display:block}
        .timeline{margin-top:28px;display:flex;flex-direction:column;gap:0}
        .timeline-item{display:flex;gap:16px;padding-bottom:24px;position:relative}
        .timeline-item:not(:last-child)::before{content:'';position:absolute;left:4px;top:20px;bottom:0;width:1px;background:linear-gradient(#DDD4C8,transparent)}
        .timeline-dot{width:9px;height:9px;border-radius:50%;background:#B59A68;flex-shrink:0;margin-top:4px;box-shadow:0 0 0 3px rgba(181,154,104,.2)}
        .timeline-label{font-size:10px;font-weight:700;color:#B59A68;text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px}
        .timeline-title{font-size:14px;font-weight:700;color:#1F1D1B;margin-bottom:4px;line-height:1.3}
        .timeline-body{font-size:12px;color:#75685D;line-height:1.6}
        @media(min-width:900px){
            .about-grid{display:grid;grid-template-columns:1fr 1.1fr;gap:80px;align-items:start}
            .about-img{margin-bottom:0}
        }

        /* DNA */
        .dna-grid{display:grid;grid-template-columns:1fr;gap:16px;margin-top:32px}
        .dna-card{background:#F7F3EC;border-radius:2px;overflow:hidden;display:flex;border:1px solid rgba(221,212,200,.6)}
        .dna-card-img{width:120px;flex-shrink:0;overflow:hidden}
        .dna-card-img img{width:100%;height:100%;object-fit:cover}
        .dna-card-body{padding:20px;flex:1;display:flex;flex-direction:column;justify-content:center}
        .dna-num{font-size:10px;font-weight:700;color:#B59A68;letter-spacing:.1em;text-transform:uppercase;margin-bottom:6px}
        .dna-title{font-size:16px;font-weight:700;color:#1F1D1B;margin-bottom:8px;line-height:1.3}
        .dna-body{font-size:12px;color:#75685D;line-height:1.6}
        @media(min-width:900px){
            .dna-grid{grid-template-columns:repeat(3,1fr);gap:24px;margin-top:48px}
            .dna-card{flex-direction:column}
            .dna-card-img{width:100%;aspect-ratio:4/5}
            .dna-card-body{padding:28px}
            .dna-title{font-size:20px}
            .dna-body{font-size:13px}
        }

        /* CRAFT */
        .craft-text{margin-bottom:32px}
        .craft-imgs{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .craft-card{border-radius:2px;overflow:hidden;position:relative;aspect-ratio:4/5}
        .craft-card img{width:100%;height:100%;object-fit:cover}
        .craft-card-label{position:absolute;left:0;right:0;bottom:0;padding:16px 14px 14px;background:linear-gradient(0deg,rgba(31,29,27,.9) 0%,rgba(31,29,27,0) 100%);color:#F7F3EC}
        .craft-card-label .t{font-size:13px;font-weight:700;letter-spacing:.02em}
        .craft-card-label .b{font-size:11px;opacity:.75;margin-top:3px;line-height:1.4}
        @media(min-width:900px){
            .craft-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}
            .craft-text{margin-bottom:0}
        }

        /* COLLECTIONS */
        .collections-header{margin-bottom:0}
        .collections-scroll{display:flex;gap:14px;overflow-x:auto;padding:28px 0 4px;scroll-snap-type:x mandatory;scrollbar-width:none}
        .collections-scroll::-webkit-scrollbar{display:none}
        .collection-card{flex:0 0 160px;scroll-snap-align:start;background:#F7F3EC;border-radius:2px;overflow:hidden;border:1px solid rgba(221,212,200,.5)}
        .collection-card-img{aspect-ratio:3/4;overflow:hidden;background:#EDE6DA}
        .collection-card-img img{width:100%;height:100%;object-fit:cover}
        .collection-card-body{padding:12px 13px 14px}
        .collection-cat{font-size:9px;font-weight:700;color:#A85C43;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
        .collection-name{font-size:13px;font-weight:700;color:#1F1D1B;line-height:1.3}
        .collection-price{font-size:12px;color:#75685D;margin-top:3px}
        .collections-cta-wrap{margin-top:24px}
        @media(min-width:900px){
            .collections-header{display:flex;justify-content:space-between;align-items:flex-end}
            .collections-scroll{display:grid;grid-template-columns:repeat(5,1fr);gap:20px;overflow-x:visible;padding:36px 0 4px}
            .collection-card{flex:none}
            .collections-cta-wrap{margin-top:32px;text-align:center}
        }

        /* PROCESS */
        .process-list{margin-top:36px;display:flex;flex-direction:column;gap:0}
        .process-item{display:flex;gap:20px;padding:20px 0;border-bottom:1px solid rgba(221,212,200,.6)}
        .process-item:first-child{border-top:1px solid rgba(221,212,200,.6)}
        .process-img{width:72px;height:72px;border-radius:2px;overflow:hidden;flex-shrink:0;background:#EDE6DA}
        .process-img img{width:100%;height:100%;object-fit:cover}
        .process-body{flex:1;min-width:0;padding-top:4px}
        .process-num{font-size:10px;font-weight:700;color:#B59A68;letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px}
        .process-title{font-size:14px;font-weight:700;color:#1F1D1B;margin-bottom:4px}
        .process-desc{font-size:12px;color:#75685D;line-height:1.55}
        .process-note{margin-top:28px;font-size:12px;color:#75685D;font-style:italic;text-align:center;padding:0 8px;line-height:1.6}
        @media(min-width:900px){
            .process-list{display:grid;grid-template-columns:repeat(5,1fr);gap:0;border-top:1px solid rgba(221,212,200,.6)}
            .process-item{flex-direction:column;padding:28px 24px 28px 0;gap:16px;border-bottom:none;border-left:1px solid rgba(221,212,200,.6);padding-left:24px;border-top:none}
            .process-item:first-child{border-top:none;border-left:none;padding-left:0}
            .process-img{width:56px;height:56px}
        }

        /* PARTNERS */
        .partners-photo{border-radius:2px;overflow:hidden;margin-top:32px;margin-bottom:24px;box-shadow:0 8px 32px rgba(31,29,27,.1)}
        .partners-photo img{width:100%;height:auto;display:block}
        .partners-list{display:flex;flex-direction:column;gap:0}
        .partner-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid rgba(221,212,200,.5)}
        .partner-row:last-child{border-bottom:none}
        .partner-name{font-size:14px;font-weight:700;color:#1F1D1B;margin-bottom:2px}
        .partner-desc{font-size:12px;color:#75685D}
        .partner-badge{font-size:9px;font-weight:700;color:#B59A68;border:1px solid rgba(181,154,104,.4);border-radius:100px;padding:3px 8px;letter-spacing:.06em;text-transform:uppercase;flex-shrink:0;margin-left:12px}
        @media(min-width:900px){
            .partners-grid{display:grid;grid-template-columns:1.15fr 1fr;gap:80px;align-items:center;margin-top:48px}
            .partners-photo{margin:0}
        }

        /* TESTIMONIALS */
        .testi-scroll-wrap{overflow:hidden;margin-top:32px}
        .testi-track{display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;padding:4px 4px 8px}
        .testi-track::-webkit-scrollbar{display:none}
        .testi-card{flex:0 0 82%;scroll-snap-align:center;border-radius:2px;overflow:hidden;box-shadow:0 4px 24px rgba(31,29,27,.12);border:1px solid rgba(221,212,200,.5)}
        .testi-card img{width:100%;height:auto;display:block}
        @media(min-width:900px){
            .testi-track{display:grid;grid-template-columns:repeat(3,1fr);overflow-x:visible;gap:24px;padding:4px}
            .testi-card{flex:none}
        }

        /* CONTACT */
        .contact-section{background:#1F1D1B;color:#F7F3EC;padding:72px 24px 48px}
        .footer-wordmark{font-size:13px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:#B59A68;margin-bottom:24px}
        .contact-section .section-title{color:#F7F3EC;font-size:26px;margin-bottom:10px}
        .contact-section .section-subtitle{color:rgba(247,243,236,.6);margin-bottom:0}
        .contact-cta-row{display:flex;flex-direction:column;gap:10px;margin:28px 0}
        .btn-contact-primary{display:inline-block;text-align:center;background:#A85C43;color:#fff;border-radius:4px;padding:14px 28px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
        .btn-contact-wa{display:flex;align-items:center;justify-content:center;gap:9px;background:rgba(37,211,102,.12);border:1px solid rgba(37,211,102,.3);color:#25d366;border-radius:4px;padding:14px 28px;font-size:12px;font-weight:700}
        .contact-info{display:flex;flex-direction:column;gap:12px}
        .contact-info-row{display:flex;align-items:center;gap:12px;font-size:13px;color:rgba(247,243,236,.6)}
        .contact-info-row svg{width:15px;height:15px;flex-shrink:0;color:#B59A68}
        .footer-copy{font-size:11px;color:rgba(247,243,236,.3);margin-top:36px;text-align:center;letter-spacing:.04em}
        @media(min-width:900px){
            .contact-section{padding:100px 60px 72px}
            .contact-inner{display:grid;grid-template-columns:1fr 1fr;gap:100px;align-items:start}
            .contact-cta-row{flex-direction:row;max-width:420px}
        }
    </style>
</head>
<body x-data="{ lang: 'en', heroSlide: 0, _t: null, init(){ this.startAuto(); }, toggleLang(){ this.lang = this.lang==='id'?'en':'id'; }, startAuto(){ const n={{ $section->activeItems->count() }}; if(n<=1)return; this._t=setInterval(()=>{this.heroSlide=(this.heroSlide+1)%n;},4500); } }">

{{-- ══ HERO ══ --}}
@if($key === 'hero')
<section class="hero-section">
    <div class="hero-desktop-inner">
        <div class="hero-text-col">
            <div class="hero-eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
            <h1 x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></h1>
            <p x-text="lang==='id' ? {{ Js::from($section->subtitle_id) }} : {{ Js::from($section->subtitle_en) }}"></p>
            <div class="hero-cta-row">
                <a href="{{ $section->cta_url }}" class="btn-hero-primary" x-text="lang==='id' ? {{ Js::from($section->cta_label_id) }} : {{ Js::from($section->cta_label_en) }}"></a>
                <a href="#" class="btn-hero-secondary" x-text="lang==='id' ? 'Kisah Kami' : 'Our Story'"></a>
            </div>
            <div class="hero-dots">
                @foreach($section->activeItems as $i => $item)
                <div class="hero-dot" :class="heroSlide === {{ $i }} ? 'active' : ''" @click="heroSlide = {{ $i }}"></div>
                @endforeach
            </div>
        </div>
        <div class="hero-carousel">
            <div class="hero-overlay"></div>
            @foreach($section->activeItems as $i => $item)
            <div class="hero-slide" :class="heroSlide === {{ $i }} ? 'active' : ''">
                <img src="{{ $item->imageUrl() }}" alt="{{ $item->title('en') }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ ABOUT ══ --}}
@if($key === 'about')
<section class="section section-light">
    <div class="section-inner">
        <div class="about-grid">
            @if($firstImg = $section->activeItems->first(fn($i) => $i->image_path))
            <div class="about-img">
                <img src="{{ $firstImg->imageUrl() }}" alt="Atelier" loading="lazy">
            </div>
            @endif
            <div>
                <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
                <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
                <div class="section-body" x-text="lang==='id' ? {{ Js::from($section->body_id) }} : {{ Js::from($section->body_en) }}"></div>
                <div class="timeline">
                    @foreach($section->activeItems as $item)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div>
                            <div class="timeline-label" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                            <div class="timeline-title" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                            <div class="timeline-body" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ BRAND DNA ══ --}}
@if($key === 'dna')
<section class="section section-sand">
    <div class="section-inner">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
        <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
        @if($section->subtitle_en || $section->subtitle_id)
        <div class="section-subtitle" x-text="lang==='id' ? {{ Js::from($section->subtitle_id) }} : {{ Js::from($section->subtitle_en) }}"></div>
        @endif
        <div class="dna-grid">
            @foreach($section->activeItems as $item)
            <div class="dna-card">
                @if($item->imageUrl())
                <div class="dna-card-img"><img src="{{ $item->imageUrl() }}" alt="" loading="lazy"></div>
                @endif
                <div class="dna-card-body">
                    <div class="dna-num" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                    <div class="dna-title" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                    <div class="dna-body" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ CRAFT ══ --}}
@if($key === 'craft')
<section class="section section-light">
    <div class="section-inner">
        <div class="craft-grid">
            <div class="craft-text">
                <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
                <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
                <div class="section-body" x-text="lang==='id' ? {{ Js::from($section->body_id) }} : {{ Js::from($section->body_en) }}"></div>
            </div>
            <div class="craft-imgs">
                @foreach($section->activeItems as $item)
                <div class="craft-card">
                    <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                    <div class="craft-card-label">
                        <div class="t" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                        <div class="b" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ COLLECTIONS ══ --}}
@if($key === 'collections')
<section class="section section-sand" style="padding-bottom:52px">
    <div class="section-inner">
        <div class="collections-header">
            <div>
                <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
                <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
            </div>
        </div>
        <div class="collections-scroll">
            @foreach($featuredProducts as $product)
            <a href="/produk/{{ $product->product_code }}" class="collection-card">
                <div class="collection-card-img">
                    @if($product->photos->first())
                    <img src="{{ asset('storage/' . $product->photos->first()->photo_path) }}" alt="{{ $product->nama }}" loading="lazy">
                    @else
                    <div style="width:100%;height:100%;background:#EDE6DA"></div>
                    @endif
                </div>
                <div class="collection-card-body">
                    <div class="collection-cat">{{ $product->garmentCategory?->nama ?? '' }}</div>
                    <div class="collection-name">{{ $product->nama }}</div>
                    @if($product->harga)
                    <div class="collection-price">IDR {{ number_format($product->harga, 0, ',', '.') }}</div>
                    @endif
                </div>
            </a>
            @endforeach
            @if($featuredProducts->isEmpty())
            @foreach($section->activeItems as $item)
            <a href="{{ $item->link_url ?: '#' }}" class="collection-card">
                <div class="collection-card-img">
                    @if($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                    @endif
                </div>
                <div class="collection-card-body">
                    <div class="collection-cat" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                    <div class="collection-name" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                </div>
            </a>
            @endforeach
            @endif
        </div>
        <div class="collections-cta-wrap">
            <a href="{{ $section->cta_url }}" class="btn-outline" x-text="lang==='id' ? {{ Js::from($section->cta_label_id) }} : {{ Js::from($section->cta_label_en) }}"></a>
        </div>
    </div>
</section>
@endif

{{-- ══ PROCESS ══ --}}
@if($key === 'process')
<section class="section section-light">
    <div class="section-inner">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
        <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
        <div class="process-list">
            @foreach($section->activeItems as $item)
            <div class="process-item">
                <div class="process-img">
                    @if($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                    @endif
                </div>
                <div class="process-body">
                    <div class="process-num" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                    <div class="process-title" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                    <div class="process-desc" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        @if($section->body_en)
        <div class="process-note" x-text="lang==='id' ? {{ Js::from($section->body_id) }} : {{ Js::from($section->body_en) }}"></div>
        @endif
    </div>
</section>
@endif

{{-- ══ PARTNERS ══ --}}
@if($key === 'partners')
<section class="section section-sand">
    <div class="section-inner">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
        <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
        @php $partnerPhoto = $section->activeItems->first(fn($i) => $i->image_path); @endphp
        <div class="partners-grid">
            @if($partnerPhoto)
            <div class="partners-photo"><img src="{{ $partnerPhoto->imageUrl() }}" alt="" loading="lazy"></div>
            @endif
            <div class="partners-list">
                @foreach($section->activeItems->filter(fn($i) => !$i->image_path) as $item)
                <div class="partner-row">
                    <div>
                        <div class="partner-name" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                        <div class="partner-desc" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                    </div>
                    @if($item->label_en)
                    <span class="partner-badge" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ TESTIMONIALS ══ --}}
@if($key === 'testimonials')
<section class="section section-light" style="overflow:hidden">
    <div class="section-inner">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($section->eyebrow_id) }} : {{ Js::from($section->eyebrow_en) }}"></div>
        <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
        <div class="testi-scroll-wrap">
            <div class="testi-track">
                @foreach($section->activeItems as $item)
                <div class="testi-card"><img src="{{ $item->imageUrl() }}" alt="" loading="lazy"></div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ CONTACT ══ --}}
@if($key === 'contact')
<footer class="contact-section">
    <div class="contact-inner section-inner" style="padding-top:0;padding-bottom:0">
        <div>
            <div class="footer-wordmark">ATELIER</div>
            <div class="section-title" x-text="lang==='id' ? {{ Js::from($section->title_id) }} : {{ Js::from($section->title_en) }}"></div>
            <div class="section-subtitle" x-text="lang==='id' ? {{ Js::from($section->subtitle_id) }} : {{ Js::from($section->subtitle_en) }}"></div>
            <div class="contact-cta-row">
                <a href="{{ $section->cta_url }}" class="btn-contact-primary" x-text="lang==='id' ? {{ Js::from($section->cta_label_id) }} : {{ Js::from($section->cta_label_en) }}"></a>
                <a href="https://wa.me/{{ $waNumber }}" class="btn-contact-wa">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.55 4.107 1.514 5.833L.057 23.394a.5.5 0 00.549.549l5.56-1.457A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.866 9.866 0 01-5.031-1.377l-.36-.214-3.731.979.994-3.634-.234-.374A9.867 9.867 0 012.118 12C2.118 6.534 6.534 2.118 12 2.118c5.466 0 9.882 4.416 9.882 9.882 0 5.466-4.416 9.882-9.882 9.882z"/></svg>
                    <span>Chat via WhatsApp</span>
                </a>
            </div>
            <div class="footer-copy">&copy; {{ date('Y') }} Atelier. All rights reserved.</div>
        </div>
        <div>
            <div class="contact-info">
                <div class="contact-info-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z"/></svg>
                    <span>example.com</span>
                </div>
                <div class="contact-info-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                    <span>&#64;atelier.demo</span>
                </div>
                <div class="contact-info-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>hello@example.com</span>
                </div>
                <div class="contact-info-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Sample City, Indonesia</span>
                </div>
            </div>
        </div>
    </div>
</footer>
@endif

<script src="/js/alpine.min.js" defer></script>
</body>
</html>
