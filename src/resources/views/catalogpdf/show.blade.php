@php
    $title  = $pdf['title'] ?? 'E-Catalog';
    $sizeMb = $pdf ? number_format($pdf['size'] / 1048576, 1) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#1A1F2E">
    <title>{{ $title }} — Atelier</title>
    <meta name="description" content="Browse the Atelier e-catalog — Indonesian modern ethnic &amp; modest fashion. View online or download the PDF.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }} — Atelier">
    <meta property="og:description" content="Browse the Atelier e-catalog online or download the PDF.">
    <meta property="og:url" content="{{ url('/katalogpdf') }}">
    <link rel="icon" type="image/jpeg" href="/images/logo.svg">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap">
    <style>
        :root {
            --night: #1A1F2E;
            --night-2: #222840;
            --panel: #1e2436;
            --text: #EDE8E0;
            --dim: rgba(237,232,224,.62);
            --terra: #B85C3A;
            --terra-deep: #9A4A2E;
            --gold: #C9A86C;
            --line: rgba(237,232,224,.12);
            --display: 'Cormorant Garamond', Georgia, 'Times New Roman', serif;
            --sans: 'DM Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; background: var(--night); overscroll-behavior: none; }
        body {
            font-family: var(--sans); color: var(--text); font-size: 14px; line-height: 1.5;
            -webkit-font-smoothing: antialiased; -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none; user-select: none;
        }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; color: inherit; background: none; border: 0; cursor: pointer; }
        .sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

        .app {
            position: fixed; inset: 0; display: grid; grid-template-rows: auto minmax(0, 1fr) auto;
            background: radial-gradient(120% 70% at 50% 0%, #283050 0%, var(--night) 62%);
        }

        /* ── Top bar ── */
        .top {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: calc(env(safe-area-inset-top, 0px) + 10px) max(16px, env(safe-area-inset-right, 0px)) 10px max(16px, env(safe-area-inset-left, 0px));
            background: rgba(26,31,46,.92); border-bottom: 1px solid var(--line);
            -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px); z-index: 5;
        }
        .brand { display: flex; align-items: center; gap: 11px; min-width: 0; }
        .brand img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex: none; border: 1px solid var(--line); }
        .brand-text { display: flex; flex-direction: column; min-width: 0; line-height: 1.15; }
        .brand-text b { font-family: var(--display); font-size: 19px; font-weight: 600; letter-spacing: .24em; text-transform: uppercase; }
        .brand-text small { font-size: 10.5px; letter-spacing: .1em; text-transform: uppercase; color: var(--gold); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 3px; }
        .dl {
            flex: none; display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 17px 0 14px;
            border-radius: 999px; background: var(--terra); color: #fff; font-weight: 600; font-size: 13px; letter-spacing: .01em;
            box-shadow: 0 8px 20px -8px rgba(184,92,58,.8); transition: transform .12s, background .12s;
        }
        .dl:active { transform: scale(.96); background: var(--terra-deep); }
        .dl svg { width: 18px; height: 18px; }
        .dl em { font-style: normal; font-weight: 500; opacity: .72; }
        @media (max-width: 430px) { .dl em { display: none; } }
        @media (max-width: 340px) { .dl span { display: none; } .dl { padding: 0 12px; } }

        /* ── Stage ── */
        .stage { position: relative; min-height: 0; overflow: hidden; }
        .track {
            position: absolute; inset: 0; display: flex; overflow-x: auto; overflow-y: hidden;
            scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain; touch-action: pan-x;
        }
        .track::-webkit-scrollbar { display: none; }
        .slide {
            flex: 0 0 100%; height: 100%; display: grid; place-items: center; overflow: hidden;
            scroll-snap-align: center; scroll-snap-stop: always; position: relative;
        }
        .unit { display: flex; transform-origin: 50% 50%; will-change: transform; }
        .unit.anim { transition: transform .26s cubic-bezier(.2,.8,.2,1); }
        .pg {
            position: relative; flex: none; background: #f3eee4; overflow: hidden; border-radius: 2px;
            box-shadow: 0 18px 50px -10px rgba(0,0,0,.6), 0 2px 8px rgba(0,0,0,.35);
        }
        .pg canvas { display: block; width: 100%; height: 100%; opacity: 0; transition: opacity .3s; }
        .pg.ready canvas { opacity: 1; }
        .pg .sk {
            position: absolute; inset: 0; transition: opacity .3s;
            background: linear-gradient(100deg, #ece5d8 30%, #f8f4ec 50%, #ece5d8 70%); background-size: 220% 100%;
            animation: sh 1.5s linear infinite;
        }
        .pg.ready .sk { opacity: 0; pointer-events: none; }
        @keyframes sh { from { background-position: 120% 0; } to { background-position: -120% 0; } }
        .unit.spread .pg.l { border-radius: 2px 0 0 2px; }
        .unit.spread .pg.r { border-radius: 0 2px 2px 0; }
        .unit.spread .pg.l::after, .unit.spread .pg.r::before {
            content: ''; position: absolute; top: 0; bottom: 0; width: 7%; pointer-events: none; z-index: 2;
        }
        .unit.spread .pg.l::after { right: 0; background: linear-gradient(to left, rgba(0,0,0,.2), transparent); }
        .unit.spread .pg.r::before { left: 0; background: linear-gradient(to right, rgba(0,0,0,.2), transparent); }
        .stage.zoomed .track { cursor: grab; }

        /* ── Loader / error ── */
        .overlay {
            position: absolute; inset: 0; z-index: 4; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 14px; padding: 24px; text-align: center; background: var(--night); transition: opacity .45s;
        }
        .overlay.hide { opacity: 0; pointer-events: none; }
        .overlay img { width: 64px; height: 64px; border-radius: 50%; border: 1px solid var(--line); animation: breathe 2.2s ease-in-out infinite; }
        @keyframes breathe { 50% { transform: scale(1.06); opacity: .8; } }
        .overlay p { color: var(--dim); font-size: 13px; letter-spacing: .04em; max-width: 280px; }
        .lbar { width: 160px; height: 3px; border-radius: 3px; background: rgba(255,255,255,.1); overflow: hidden; }
        .lbar i { display: block; height: 100%; width: 8%; border-radius: 3px; background: linear-gradient(90deg, var(--gold), var(--terra)); transition: width .25s; }
        .overlay h2 { font-family: var(--display); font-size: 24px; font-weight: 600; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; height: 42px; padding: 0 20px; border-radius: 999px;
            background: var(--terra); color: #fff; font-weight: 600; font-size: 13.5px;
        }
        .btn.ghost { background: transparent; border: 1px solid var(--line); color: var(--text); }
        .row { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 4px; }

        .hint {
            position: absolute; left: 50%; bottom: 16px; z-index: 3; transform: translate(-50%, 12px); opacity: 0; pointer-events: none;
            max-width: calc(100% - 32px); padding: 10px 16px; border-radius: 999px; font-size: 12.5px; text-align: center;
            background: rgba(10,12,20,.82); border: 1px solid var(--line); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
            transition: opacity .35s, transform .35s;
        }
        .hint.show { opacity: 1; transform: translate(-50%, 0); }

        /* ── Bottom bar ── */
        .bottom {
            position: relative; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 8px;
            padding: 8px max(14px, env(safe-area-inset-right, 0px)) calc(env(safe-area-inset-bottom, 0px) + 8px) max(14px, env(safe-area-inset-left, 0px));
            background: rgba(26,31,46,.94); border-top: 1px solid var(--line);
            -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px); z-index: 5;
        }
        .progress { position: absolute; left: 0; right: 0; top: -1px; height: 2px; background: rgba(255,255,255,.07); }
        .progress i { display: block; height: 100%; width: 0; background: linear-gradient(90deg, var(--gold), var(--terra)); transition: width .3s; }
        .ib {
            width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center;
            background: rgba(255,255,255,.06); border: 1px solid var(--line); transition: background .12s, transform .12s, opacity .12s;
        }
        .ib:active { transform: scale(.92); background: rgba(255,255,255,.14); }
        .ib[disabled] { opacity: .3; pointer-events: none; }
        .ib svg { width: 21px; height: 21px; }
        .ib.on { background: var(--gold); color: var(--night); border-color: var(--gold); }
        .left { justify-self: start; } .right { justify-self: end; }
        .pager { display: flex; align-items: center; gap: 6px; }
        .count { min-width: 92px; text-align: center; font-family: var(--display); font-size: 21px; font-weight: 600; font-variant-numeric: tabular-nums; letter-spacing: .02em; }
        .count span { color: var(--dim); font-weight: 500; margin: 0 4px; }

        @media (max-height: 480px) {
            .brand-text small { display: none; }
            .top { padding-top: calc(env(safe-area-inset-top, 0px) + 6px); padding-bottom: 6px; }
            .brand img { width: 30px; height: 30px; }
            .dl { height: 34px; }
            .ib { width: 38px; height: 38px; }
            .bottom { padding-top: 5px; padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 5px); }
        }

        /* ── Page grid sheet ── */
        .sheet { position: fixed; inset: 0; z-index: 50; visibility: hidden; pointer-events: none; transition: visibility 0s .3s; }
        .sheet.open { visibility: visible; pointer-events: auto; transition-delay: 0s; }
        .sheet-bg { position: absolute; inset: 0; background: rgba(8,10,18,.62); opacity: 0; transition: opacity .28s; }
        .sheet.open .sheet-bg { opacity: 1; }
        .panel {
            position: absolute; left: 0; right: 0; bottom: 0; max-height: 86%; display: flex; flex-direction: column;
            background: var(--panel); border-radius: 22px 22px 0 0; border-top: 1px solid var(--line);
            transform: translateY(100%); transition: transform .32s cubic-bezier(.2,.8,.2,1);
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .sheet.open .panel { transform: none; }
        .panel-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 12px 8px 20px; }
        .panel-head h2 { font-family: var(--display); font-size: 22px; font-weight: 600; letter-spacing: .02em; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 6px 18px 22px; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .th { position: relative; display: block; border-radius: 6px; overflow: hidden; background: #2b3250; outline: 2px solid transparent; outline-offset: 1px; transition: outline-color .15s, transform .12s; }
        .th:active { transform: scale(.96); }
        .th.on { outline-color: var(--gold); }
        .th canvas { display: block; width: 100%; height: auto; opacity: 0; transition: opacity .3s; }
        .th canvas.ready { opacity: 1; }
        .th span {
            position: absolute; left: 6px; bottom: 6px; font-size: 11px; font-weight: 600; line-height: 1;
            padding: 4px 8px; border-radius: 999px; background: rgba(26,31,46,.85); font-variant-numeric: tabular-nums;
        }
        .th.on span { background: var(--gold); color: var(--night); }
        @media (min-width: 700px) {
            .panel { left: 50%; right: auto; width: min(780px, 92vw); transform: translate(-50%, 100%); border-radius: 22px; bottom: 16px; max-height: 80%; }
            .sheet.open .panel { transform: translate(-50%, 0); }
            .grid { grid-template-columns: repeat(5, 1fr); }
        }

        .empty .overlay { position: static; min-height: 100%; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
@if(!$pdf)
    <div class="app empty" style="display:block">
        <div class="overlay">
            <img src="/images/logo.svg" alt="Atelier" style="animation:none">
            <h2>Our e-catalog is being updated</h2>
            <p>Please check back soon. In the meantime, browse our collection online.</p>
            <div class="row"><a class="btn" href="/produk">Browse products</a><a class="btn ghost" href="/">Visit Atelier</a></div>
        </div>
    </div>
@else
    <div class="app" id="app">
        <header class="top">
            <a class="brand" href="/" aria-label="Atelier">
                <img src="/images/logo.svg" alt="">
                <span class="brand-text"><b>Atelier</b><small>{{ $title }}</small></span>
            </a>
            <a class="dl" href="{{ $downloadUrl }}" download>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4.5-4.5M12 15l4.5-4.5M5 20h14"/></svg>
                <span data-t="download">Download PDF</span>
                <em>{{ $sizeMb }} MB</em>
            </a>
        </header>

        <main class="stage" id="stage">
            <h1 class="sr">{{ $title }}</h1>
            <div class="track" id="track"></div>

            <div class="overlay" id="overlay">
                <img src="/images/logo.svg" alt="Atelier">
                <p data-t="loading">Opening catalog…</p>
                <div class="lbar"><i id="lbar"></i></div>
                <noscript><p>Please enable JavaScript to view the catalog, or <a href="{{ $downloadUrl }}" style="color:var(--gold);text-decoration:underline">download the PDF</a>.</p></noscript>
            </div>

            <div class="hint" id="hint" data-t="hint"></div>
        </main>

        <footer class="bottom">
            <div class="progress"><i id="bar"></i></div>
            <div class="left">
                <button class="ib" id="btnGrid" type="button" data-ta="pages" aria-label="All pages">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><rect x="4" y="4" width="6.5" height="6.5" rx="1.4"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.4"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.4"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.4"/></svg>
                </button>
            </div>
            <div class="pager">
                <button class="ib" id="btnPrev" type="button" data-ta="prev" aria-label="Previous page">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
                </button>
                <div class="count" aria-live="polite"><b id="cur" style="font-weight:600">–</b><span>/</span><b id="tot" style="font-weight:600">–</b></div>
                <button class="ib" id="btnNext" type="button" data-ta="next" aria-label="Next page">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
            <div class="right">
                <button class="ib" id="btnZoom" type="button" data-ta="zoom" aria-label="Zoom">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2M8.5 11h5"/><path id="zoomPlus" d="M11 8.5v5"/></svg>
                </button>
            </div>
        </footer>
    </div>

    <div class="sheet" id="sheet" aria-hidden="true">
        <div class="sheet-bg" data-close></div>
        <div class="panel" role="dialog" aria-modal="true">
            <div class="panel-head">
                <h2 data-t="pages">All pages</h2>
                <button class="ib" type="button" data-close data-ta="close" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <div class="grid" id="grid"></div>
        </div>
    </div>

    <script src="/js/pdfjs/pdf.min.js?v=3.11.174" defer></script>
    <script>
    window.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const FILE_URL = @json($fileUrl);
        const DOWNLOAD_URL = @json($downloadUrl);
        const WORKER_URL = '/js/pdfjs/pdf.worker.min.js?v=3.11.174';
        const MAX_ZOOM = 4, DBL_ZOOM = 2.6;

        const STR = {
            en: { download: 'Download PDF', pages: 'All pages', loading: 'Opening catalog…', hint: 'Swipe to turn pages · Pinch or double-tap to zoom',
                  prev: 'Previous page', next: 'Next page', zoom: 'Zoom', close: 'Close', failed: 'We couldn’t open the catalog here', retry: 'Try again', direct: 'Download PDF' },
            id: { download: 'Unduh PDF', pages: 'Semua halaman', loading: 'Membuka katalog…', hint: 'Geser untuk ganti halaman · Cubit atau ketuk 2x untuk zoom',
                  prev: 'Halaman sebelumnya', next: 'Halaman berikutnya', zoom: 'Zoom', close: 'Tutup', failed: 'Katalog tidak bisa dibuka di sini', retry: 'Coba lagi', direct: 'Unduh PDF' }
        };
        const q = /[?&]lang=(id|en)/.exec(location.search);
        const lang = q ? q[1] : (/^id/i.test(navigator.language || '') ? 'id' : 'en');
        const tr = STR[lang];
        document.documentElement.lang = lang;
        document.querySelectorAll('[data-t]').forEach(e => { e.textContent = tr[e.dataset.t]; });
        document.querySelectorAll('[data-ta]').forEach(e => { e.setAttribute('aria-label', tr[e.dataset.ta]); });

        const $ = id => document.getElementById(id);
        const stage = $('stage'), track = $('track'), overlay = $('overlay'), lbar = $('lbar'), hint = $('hint');
        const curEl = $('cur'), totEl = $('tot'), bar = $('bar');
        const btnPrev = $('btnPrev'), btnNext = $('btnNext'), btnZoom = $('btnZoom'), btnGrid = $('btnGrid');
        const sheet = $('sheet'), grid = $('grid'), zoomPlus = $('zoomPlus');

        let pdf = null, N = 0, ratio = 9 / 16;
        let spread = false, units = [], cur = 0, boxW = 0, boxH = 0;
        let gen = 0, done = new Set(), queued = new Set(), queue = [], running = false, task = null;
        let z = 1, tx = 0, ty = 0, ready = false, firstShown = false, initialPage = 1;

        function showError() {
            overlay.classList.remove('hide');
            overlay.style.display = '';
            overlay.innerHTML = '<img src="/images/logo.svg" alt="" style="animation:none"><h2>' + tr.failed + '</h2>' +
                '<div class="row"><button class="btn" type="button" onclick="location.reload()">' + tr.retry + '</button>' +
                '<a class="btn ghost" href="' + DOWNLOAD_URL + '">' + tr.direct + '</a></div>';
        }

        // ── layout ──
        function computeLayout() {
            const w = stage.clientWidth, h = stage.clientHeight;
            spread = N > 2 && w >= 720 && h >= 540 && w / h > 1.15;
            const padX = w < 500 ? 14 : 32, padY = w < 500 ? 16 : 26;
            const perUnit = spread ? 2 : 1;
            boxH = Math.min(h - padY * 2, (w - padX * 2) / (ratio * perUnit));
            boxW = boxH * ratio;
            boxH = Math.floor(boxH); boxW = Math.floor(boxW);
        }
        function buildUnits() {
            units = [];
            if (!spread) { for (let i = 1; i <= N; i++) units.push([i]); return; }
            units.push([1]);
            for (let i = 2; i <= N; i += 2) units.push(i + 1 <= N ? [i, i + 1] : [i]);
        }
        const unitOf = p => Math.max(0, units.findIndex(u => u.indexOf(p) !== -1));

        function buildDom() {
            track.innerHTML = '';
            const frag = document.createDocumentFragment();
            units.forEach(u => {
                const s = document.createElement('section'); s.className = 'slide';
                const unit = document.createElement('div');
                const pair = spread && u.length === 2;
                unit.className = 'unit' + (pair ? ' spread' : '');
                u.forEach((p, k) => {
                    const pg = document.createElement('div');
                    pg.className = 'pg' + (pair ? (k === 0 ? ' l' : ' r') : '');
                    pg.dataset.p = p; pg.style.width = boxW + 'px'; pg.style.height = boxH + 'px';
                    pg.innerHTML = '<canvas role="img" aria-label="Page ' + p + '"></canvas><i class="sk"></i>';
                    unit.appendChild(pg);
                });
                s.appendChild(unit); frag.appendChild(s);
            });
            track.appendChild(frag);
        }

        function relayout(page) {
            gen++; queue = []; queued.clear(); done.clear();
            if (task) { try { task.cancel(); } catch (e) {} }
            z = 1; tx = 0; ty = 0;
            computeLayout(); buildUnits(); buildDom();
            cur = unitOf(page);
            track.style.touchAction = 'pan-x'; stage.classList.remove('zoomed');
            track.scrollTo({ left: cur * track.clientWidth, behavior: 'auto' });
            setCur(cur, true);
        }

        // ── rendering ──
        function schedule(p) {
            if (done.has(p) || queued.has(p)) return;
            queued.add(p); queue.push(p); pump();
        }
        async function pump() {
            if (running) return;
            running = true;
            const g = gen;
            while (queue.length && g === gen) {
                const base = units[cur] ? units[cur][0] : 1;
                queue.sort((a, b) => Math.abs(a - base) - Math.abs(b - base));
                const p = queue.shift(); queued.delete(p);
                if (done.has(p)) continue;
                try { await renderPage(p, g); }
                catch (e) { if (!e || e.name !== 'RenderingCancelledException') console.warn('render', p, e); }
            }
            running = false;
            if (g !== gen && queue.length) pump();
        }
        async function renderPage(p, g) {
            const page = await pdf.getPage(p);
            if (g !== gen) return;
            const pg = track.querySelector('.pg[data-p="' + p + '"]');
            if (!pg) return;
            const vp1 = page.getViewport({ scale: 1 });
            const dpr = Math.min(window.devicePixelRatio || 1, 3);
            let scale = (boxW * dpr * 1.5) / vp1.width;
            const px = vp1.width * vp1.height * scale * scale;
            if (px > 3.4e6) scale *= Math.sqrt(3.4e6 / px);
            const vp = page.getViewport({ scale });
            const canvas = pg.firstChild;
            canvas.width = Math.floor(vp.width); canvas.height = Math.floor(vp.height);
            task = page.render({ canvasContext: canvas.getContext('2d', { alpha: false }), viewport: vp });
            await task.promise;
            if (g !== gen) return;
            pg.classList.add('ready'); done.add(p);
            if (!firstShown && units[cur] && units[cur].indexOf(p) !== -1) revealFirst();
        }
        function evict() {
            const keep = new Set();
            for (let u = cur - 2; u <= cur + 2; u++) (units[u] || []).forEach(p => keep.add(p));
            done.forEach(p => {
                if (keep.has(p)) return;
                const pg = track.querySelector('.pg[data-p="' + p + '"]');
                if (pg) { pg.firstChild.width = 1; pg.firstChild.height = 1; pg.classList.remove('ready'); }
                done.delete(p);
            });
        }
        let hiTimer;
        function scheduleHiRes() {
            clearTimeout(hiTimer);
            if (z <= 1.3) return;
            hiTimer = setTimeout(hiRes, 300);
        }
        async function hiRes() {
            const g = gen, pages = units[cur];
            if (!pages || z <= 1.3) return;
            const dpr = Math.min(window.devicePixelRatio || 1, 3);
            for (const p of pages) {
                const page = await pdf.getPage(p);
                const pg = track.querySelector('.pg[data-p="' + p + '"]');
                if (!pg) continue;
                const vp1 = page.getViewport({ scale: 1 });
                let scale = Math.min(boxW * z * dpr, 3600) / vp1.width;
                const px = vp1.width * vp1.height * scale * scale;
                if (px > 9e6) scale *= Math.sqrt(9e6 / px);
                const vp = page.getViewport({ scale });
                const off = document.createElement('canvas');
                off.width = Math.floor(vp.width); off.height = Math.floor(vp.height);
                try { await page.render({ canvasContext: off.getContext('2d', { alpha: false }), viewport: vp }).promise; } catch (e) { return; }
                if (g !== gen || z <= 1.3 || units[cur] !== pages) return;
                const main = pg.firstChild;
                main.width = off.width; main.height = off.height;
                main.getContext('2d').drawImage(off, 0, 0);
            }
        }

        function revealFirst() {
            firstShown = true;
            overlay.classList.add('hide');
            setTimeout(() => { overlay.style.display = 'none'; }, 500);
            try {
                if (!localStorage.getItem('atelier_catalog_hint')) {
                    localStorage.setItem('atelier_catalog_hint', '1');
                    setTimeout(() => hint.classList.add('show'), 600);
                    setTimeout(() => hint.classList.remove('show'), 4600);
                }
            } catch (e) {}
        }

        // ── navigation ──
        let hashTimer;
        function setCur(i, force) {
            i = Math.max(0, Math.min(units.length - 1, i));
            if (i === cur && !force) return;
            if (i !== cur && z !== 1) resetZoom(false);
            cur = i;
            const u = units[cur];
            curEl.textContent = u.length === 2 ? u[0] + '–' + u[1] : u[0];
            totEl.textContent = N;
            bar.style.width = ((cur + 1) / units.length * 100) + '%';
            btnPrev.disabled = cur === 0; btnNext.disabled = cur === units.length - 1;
            [0, 1, -1, 2, -2].forEach(d => { (units[cur + d] || []).forEach(schedule); });
            evict();
            document.querySelectorAll('.th').forEach(t => t.classList.toggle('on', u.indexOf(+t.dataset.p) !== -1));
            clearTimeout(hashTimer);
            hashTimer = setTimeout(() => { try { history.replaceState(null, '', '#p' + u[0]); } catch (e) {} }, 350);
        }
        function goto(i, smooth) {
            i = Math.max(0, Math.min(units.length - 1, i));
            if (z !== 1) resetZoom(false);
            track.scrollTo({ left: i * track.clientWidth, behavior: smooth === false ? 'auto' : 'smooth' });
            if (smooth === false) setCur(i);
        }
        let scrollRaf = 0;
        track.addEventListener('scroll', () => {
            if (scrollRaf) return;
            scrollRaf = requestAnimationFrame(() => {
                scrollRaf = 0;
                const w = track.clientWidth; if (!w) return;
                setCur(Math.round(track.scrollLeft / w));
            });
        }, { passive: true });
        btnPrev.addEventListener('click', () => goto(cur - 1));
        btnNext.addEventListener('click', () => goto(cur + 1));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') return closeSheet();
            if (sheet.classList.contains('open')) return;
            if (e.key === 'ArrowRight' || e.key === 'PageDown') goto(cur + 1);
            else if (e.key === 'ArrowLeft' || e.key === 'PageUp') goto(cur - 1);
            else if (e.key === 'Home') goto(0);
            else if (e.key === 'End') goto(units.length - 1);
            else if (e.key === '+' || e.key === '=') { const c = center(); zoomAt(c.x, c.y, z * 1.4, true); }
            else if (e.key === '-') { const c = center(); zoomAt(c.x, c.y, z / 1.4, true); }
            else if (e.key === '0') resetZoom(true);
        });

        // ── zoom & pan ──
        const unitEl = () => track.children[cur] && track.children[cur].firstChild;
        function center() { const r = stage.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2, w: r.width, h: r.height }; }
        function clampT() {
            const u = unitEl(); if (!u) return;
            const c = center(), uw = u.offsetWidth * z, uh = u.offsetHeight * z;
            const mx = uw > c.w ? (uw - c.w) / 2 + 24 : 0, my = uh > c.h ? (uh - c.h) / 2 + 24 : 0;
            tx = Math.max(-mx, Math.min(mx, tx)); ty = Math.max(-my, Math.min(my, ty));
        }
        let animTimer;
        function apply(anim) {
            const u = unitEl(); if (!u) return;
            if (anim) { u.classList.add('anim'); clearTimeout(animTimer); animTimer = setTimeout(() => u.classList.remove('anim'), 300); }
            else { u.classList.remove('anim'); }
            u.style.transform = (z === 1 && !tx && !ty) ? '' : 'translate3d(' + tx + 'px,' + ty + 'px,0) scale(' + z + ')';
            const zoomed = z > 1.001;
            track.style.touchAction = zoomed ? 'none' : 'pan-x';
            stage.classList.toggle('zoomed', zoomed);
            btnZoom.classList.toggle('on', zoomed);
            zoomPlus.style.display = zoomed ? 'none' : '';
        }
        function resetZoom(anim) { z = 1; tx = 0; ty = 0; apply(anim); }
        function zoomAt(fx, fy, nz, anim) {
            const c = center(), ux = (fx - c.x - tx) / z, uy = (fy - c.y - ty) / z;
            z = Math.max(1, Math.min(MAX_ZOOM, nz));
            tx = fx - c.x - z * ux; ty = fy - c.y - z * uy;
            if (z <= 1.001) { z = 1; tx = 0; ty = 0; } else clampT();
            apply(anim); scheduleHiRes();
        }
        function toggleZoomAt(x, y) { if (z > 1.001) resetZoom(true); else zoomAt(x, y, DBL_ZOOM, true); }
        btnZoom.addEventListener('click', () => { const c = center(); toggleZoomAt(c.x, c.y); });

        let pinch = null, pan = null, tapStart = null, lastTap = 0, lastTapX = 0, lastTapY = 0, lastTouch = 0;
        const dist = (a, b) => Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY);
        stage.addEventListener('touchstart', e => {
            lastTouch = Date.now();
            if (e.touches.length === 2) {
                const a = e.touches[0], b = e.touches[1], c = center();
                const mx = (a.clientX + b.clientX) / 2, my = (a.clientY + b.clientY) / 2;
                pinch = { d0: dist(a, b), z0: z, ux: (mx - c.x - tx) / z, uy: (my - c.y - ty) / z };
                pan = null; tapStart = null;
            } else if (e.touches.length === 1) {
                const t = e.touches[0];
                pan = z > 1.001 ? { x: t.clientX, y: t.clientY } : null;
                pinch = null; tapStart = { x: t.clientX, y: t.clientY, t: Date.now() };
            }
        }, { passive: true });
        stage.addEventListener('touchmove', e => {
            if (pinch && e.touches.length === 2) {
                if (e.cancelable) e.preventDefault();
                const a = e.touches[0], b = e.touches[1], c = center();
                const mx = (a.clientX + b.clientX) / 2, my = (a.clientY + b.clientY) / 2;
                z = Math.max(1, Math.min(MAX_ZOOM + 0.6, pinch.z0 * dist(a, b) / pinch.d0));
                tx = mx - c.x - z * pinch.ux; ty = my - c.y - z * pinch.uy;
                apply(false);
            } else if (pan && e.touches.length === 1) {
                if (e.cancelable) e.preventDefault();
                const t = e.touches[0];
                tx += t.clientX - pan.x; ty += t.clientY - pan.y; pan.x = t.clientX; pan.y = t.clientY;
                clampT(); apply(false);
            } else if (tapStart && e.touches.length === 1) {
                const t = e.touches[0];
                if (Math.hypot(t.clientX - tapStart.x, t.clientY - tapStart.y) > 12) tapStart = null;
            }
        }, { passive: false });
        function endTouch(e) {
            if (pinch && e.touches.length < 2) {
                pinch = null;
                if (z < 1.05) resetZoom(true); else { z = Math.min(z, MAX_ZOOM); clampT(); apply(true); scheduleHiRes(); }
                pan = (e.touches.length === 1 && z > 1.001) ? { x: e.touches[0].clientX, y: e.touches[0].clientY } : null;
                tapStart = null; return;
            }
            if (e.touches.length === 0) {
                if (tapStart && e.changedTouches.length === 1 && Date.now() - tapStart.t < 300) {
                    const t = e.changedTouches[0], now = Date.now();
                    if (now - lastTap < 320 && Math.hypot(t.clientX - lastTapX, t.clientY - lastTapY) < 40) { lastTap = 0; toggleZoomAt(t.clientX, t.clientY); }
                    else { lastTap = now; lastTapX = t.clientX; lastTapY = t.clientY; }
                }
                tapStart = null; pan = null;
            }
        }
        stage.addEventListener('touchend', endTouch, { passive: true });
        stage.addEventListener('touchcancel', () => { pinch = null; pan = null; tapStart = null; }, { passive: true });
        ['gesturestart', 'gesturechange', 'gestureend'].forEach(ev => stage.addEventListener(ev, e => e.preventDefault()));

        stage.addEventListener('dblclick', e => { if (Date.now() - lastTouch > 700) toggleZoomAt(e.clientX, e.clientY); });
        stage.addEventListener('wheel', e => {
            if (!e.ctrlKey && !e.metaKey) return;
            e.preventDefault(); zoomAt(e.clientX, e.clientY, z * Math.exp(-e.deltaY * 0.01));
        }, { passive: false });
        let drag = null;
        stage.addEventListener('mousedown', e => { if (z > 1.001 && Date.now() - lastTouch > 700) drag = { x: e.clientX, y: e.clientY }; });
        window.addEventListener('mousemove', e => {
            if (!drag) return;
            tx += e.clientX - drag.x; ty += e.clientY - drag.y; drag.x = e.clientX; drag.y = e.clientY; clampT(); apply(false);
        });
        window.addEventListener('mouseup', () => { if (drag) { drag = null; scheduleHiRes(); } });

        // ── page grid ──
        let thumbsBuilt = false;
        function buildThumbs() {
            if (thumbsBuilt) return; thumbsBuilt = true;
            const frag = document.createDocumentFragment();
            for (let p = 1; p <= N; p++) {
                const b = document.createElement('button');
                b.type = 'button'; b.className = 'th'; b.dataset.p = p; b.style.aspectRatio = String(ratio);
                b.innerHTML = '<canvas></canvas><span>' + p + '</span>';
                b.addEventListener('click', () => { closeSheet(); goto(unitOf(p)); });
                frag.appendChild(b);
            }
            grid.appendChild(frag);
            renderThumbs();
        }
        async function renderThumbs() {
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            for (let p = 1; p <= N; p++) {
                try {
                    const page = await pdf.getPage(p), vp1 = page.getViewport({ scale: 1 });
                    const vp = page.getViewport({ scale: (180 * dpr) / vp1.width });
                    const c = grid.children[p - 1].firstChild;
                    c.width = Math.floor(vp.width); c.height = Math.floor(vp.height);
                    await page.render({ canvasContext: c.getContext('2d', { alpha: false }), viewport: vp }).promise;
                    c.classList.add('ready');
                } catch (e) {}
            }
        }
        function openSheet() {
            buildThumbs();
            document.querySelectorAll('.th').forEach(t => t.classList.toggle('on', units[cur].indexOf(+t.dataset.p) !== -1));
            sheet.classList.add('open'); sheet.setAttribute('aria-hidden', 'false');
            const on = grid.querySelector('.th.on'); if (on) setTimeout(() => on.scrollIntoView({ block: 'center' }), 60);
        }
        function closeSheet() { sheet.classList.remove('open'); sheet.setAttribute('aria-hidden', 'true'); }
        btnGrid.addEventListener('click', openSheet);
        sheet.querySelectorAll('[data-close]').forEach(e => e.addEventListener('click', closeSheet));

        // ── boot ──
        async function init() {
            if (!window.pdfjsLib) return showError();
            try {
                pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_URL;
                const lt = pdfjsLib.getDocument({ url: FILE_URL });
                lt.onProgress = p => { if (p.total) lbar.style.width = Math.max(8, p.loaded / p.total * 100) + '%'; };
                pdf = await lt.promise;
                N = pdf.numPages;
                const v = (await pdf.getPage(1)).getViewport({ scale: 1 });
                ratio = v.width / v.height;
                const m = /^#p(\d+)$/.exec(location.hash);
                initialPage = m ? Math.min(N, Math.max(1, +m[1])) : 1;
                if (N < 2) { btnGrid.style.visibility = 'hidden'; btnPrev.style.visibility = 'hidden'; btnNext.style.visibility = 'hidden'; }
                lbar.style.width = '100%';
                relayout(initialPage);
                ready = true;
                let t;
                const onResize = () => {
                    clearTimeout(t);
                    t = setTimeout(() => { if (ready) relayout(units[cur] ? units[cur][0] : 1); }, 160);
                };
                if (window.ResizeObserver) new ResizeObserver(onResize).observe(stage);
                else window.addEventListener('resize', onResize);
            } catch (e) {
                console.error(e); showError();
            }
        }
        init();
    });
    </script>
@endif
</body>
</html>
