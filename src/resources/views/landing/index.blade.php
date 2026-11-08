@php
    $lang = 'en';
    $get = function(string $key, $fallback = null) use ($sections) {
        return $sections[$key] ?? $fallback;
    };
@endphp
<!DOCTYPE html>
<html lang="en" x-data="landingApp()" :lang="lang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1A1F2E">
    <title>Atelier — Indonesian Modern Ethnic & Modest Fashion</title>
    <link rel="icon" type="image/jpeg" href="/images/logo.svg">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    <meta name="description" content="Indonesian textile heritage, reimagined for today's modest wardrobe. Wear heritage, live purpose.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap">
    <style>
        /* ─── DESIGN TOKENS ─── */
        :root {
            /* Backgrounds */
            --bg-primary:  #FAFAF8;   /* warm white — main ground */
            --bg-linen:    #F0EBE1;   /* linen — alternating sections */
            --bg-night:    #1A1F2E;   /* midnight slate — hero + contact */
            --bg-night-2:  #222840;   /* lighter midnight for hover/card */

            /* Text */
            --text-main:   #1E1C1A;   /* charcoal warm */
            --text-muted:  #6B6560;   /* warm grey */
            --text-night:  #EDE8E0;   /* on dark bg */
            --text-dim:    rgba(237,232,224,.55);

            /* Accents */
            --terra:       #B85C3A;   /* terracotta — primary CTA */
            --terra-deep:  #9A4A2E;   /* hover state */
            --gold:        #C9A86C;   /* antique brass — eyebrow, ornament */
            --gold-faint:  rgba(201,168,108,.18);

            /* Borders */
            --border:      rgba(30,28,26,.1);
            --border-night:rgba(237,232,224,.1);

            /* Type */
            --display: 'Cormorant Garamond', Georgia, 'Times New Roman', serif;
            --sans:    'DM Sans', system-ui, -apple-system, sans-serif;
        }

        /* ─── RESET ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: var(--sans);
            background: var(--bg-primary);
            color: var(--text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.6;
            font-size: 15px;
        }
        img { display: block; max-width: 100%; }
        a { text-decoration: none; color: inherit; }
        button { font-family: inherit; cursor: pointer; border: none; background: none; padding: 0; }
        [x-cloak] { display: none !important; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }

        /* ─── SHELL (mobile phone frame) ─── */
        @media (max-width: 899px) {
            body { display: flex; justify-content: center; background: #BDB5AC; }
            .shell {
                width: 100%; max-width: 480px;
                background: var(--bg-primary); min-height: 100vh;
                box-shadow: 0 0 80px rgba(0,0,0,.28);
                position: relative;
            }
        }
        @media (min-width: 900px) {
            .shell { width: 100%; }
            .wrap { max-width: 1280px; margin: 0 auto; padding: 0 72px; }
        }

        /* ─── FOCUS ─── */
        :focus-visible { outline: 2px solid var(--terra); outline-offset: 3px; border-radius: 2px; }

        /* ════════════════════════════
           HEADER
        ════════════════════════════ */
        .hd {
            position: sticky; top: 0; z-index: 200;
            background: rgba(250,250,248,.92);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }
        .hd-inner {
            display: flex; align-items: center; justify-content: space-between;
            padding: 13px 20px; gap: 12px;
        }
        .hd-brand { display: flex; align-items: center; gap: 10px; }
        .hd-logo { width: 32px; height: 32px; object-fit: contain; border-radius: 1px; }
        .hd-wordmark {
            font-family: var(--display);
            font-size: 20px; font-weight: 400; letter-spacing: .18em;
            color: var(--text-main); text-transform: uppercase;
        }
        .hd-nav { display: flex; align-items: center; gap: 4px; }
        .hd-link {
            font-size: 11px; font-weight: 500; letter-spacing: .1em; text-transform: uppercase;
            color: var(--text-muted); padding: 7px 14px; border-radius: 100px;
            transition: color .2s, background .2s;
        }
        .hd-link:hover { color: var(--text-main); background: rgba(30,28,26,.05); }
        .lang-btn {
            display: flex; align-items: center; gap: 5px;
            height: 30px; padding: 0 12px;
            border: 1.5px solid rgba(30,28,26,.15); border-radius: 100px;
            font-size: 11px; font-weight: 500; letter-spacing: .04em;
            color: var(--text-muted); transition: all .2s;
        }
        .lang-btn:hover { border-color: var(--terra); color: var(--terra); }
        @media (min-width: 900px) {
            .hd-inner { padding: 15px 72px; }
            .hd-logo { width: 38px; height: 38px; }
            .hd-wordmark { font-size: 22px; }
        }

        /* ════════════════════════════
           SHARED SECTION PARTS
        ════════════════════════════ */
        .sec { padding: 80px 22px; }
        .sec-white  { background: var(--bg-primary); }
        .sec-linen  { background: var(--bg-linen); }
        .sec-night  { background: var(--bg-night);  color: var(--text-night); }

        .eyebrow {
            display: inline-flex; align-items: center; gap: 10px;
            font-family: var(--sans);
            font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase;
            color: var(--gold); margin-bottom: 14px;
        }
        .eyebrow::before { content: ''; display: inline-block; width: 26px; height: 1px; background: var(--gold); }

        .h2 {
            font-family: var(--display);
            font-size: clamp(30px, 4.5vw, 46px);
            font-weight: 300; line-height: 1.1; letter-spacing: -.02em;
            color: var(--text-main); text-wrap: balance; margin-bottom: 14px;
        }
        .h2 em { font-style: italic; color: var(--terra); font-weight: 300; }
        .sec-night .h2 { color: var(--text-night); }

        .body-text { font-size: 14px; color: var(--text-muted); line-height: 1.78; }
        .sub-text  { font-size: 14px; color: var(--text-muted); line-height: 1.72; max-width: 52ch; margin-bottom: 8px; }
        .sec-night .body-text,
        .sec-night .sub-text { color: var(--text-dim); }

        @media (min-width: 900px) {
            .sec { padding: 112px 72px; }
            .body-text { font-size: 15px; }
        }

        /* thin gold rule between sections */
        .gold-rule {
            display: flex; align-items: center; justify-content: center;
            gap: 14px; padding: 2px 22px;
            color: var(--gold); font-family: var(--display);
            font-size: 17px; letter-spacing: .06em; line-height: 1;
        }
        .gold-rule::before, .gold-rule::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201,168,108,.3));
        }
        .gold-rule::after { transform: scaleX(-1); }
        @media (min-width: 900px) { .gold-rule { padding: 0 72px; max-width: 1280px; margin: 0 auto; } }

        /* ════════════════════════════
           HERO — cinematic split
        ════════════════════════════ */
        .hero { background: var(--bg-night); position: relative; overflow: hidden; }

        /* carousel engine */
        .hc { position: relative; width: 100%; height: 100%; }
        .hs {
            position: absolute; inset: 0;
            opacity: 0; transition: opacity .95s ease;
        }
        .hs.on { opacity: 1; }
        .hs img { width: 100%; height: 100%; object-fit: cover; object-position: top center; }

        /* mobile layout */
        .hero-mob {
            position: relative; aspect-ratio: 3/4; overflow: hidden;
        }
        .hero-mob .hc { position: absolute; inset: 0; }
        .hero-vignette {
            position: absolute; inset: 0;
            background: linear-gradient(170deg,
                rgba(26,31,46,0) 20%,
                rgba(26,31,46,.5) 58%,
                rgba(26,31,46,.94) 100%);
        }
        .hero-copy {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 0 22px 44px; color: var(--text-night); z-index: 2;
        }

        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 10px;
            font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase;
            color: var(--gold); margin-bottom: 14px;
        }
        .hero-eyebrow::before { content: ''; display: inline-block; width: 22px; height: 1px; background: var(--gold); }

        .hero-h1 {
            font-family: var(--display);
            font-size: clamp(38px, 9vw, 56px);
            font-weight: 300; line-height: 1.06; letter-spacing: -.03em;
            color: var(--text-night); max-width: 300px;
            margin-bottom: 14px; text-wrap: balance;
        }
        .hero-h1 em { font-style: italic; color: #E8A882; }
        .hero-sub {
            font-size: 13px; line-height: 1.65; color: var(--text-dim);
            max-width: 280px; margin-bottom: 28px;
        }
        .hero-ctas { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 30px; }

        /* shared button styles */
        .btn-solid {
            background: var(--terra); color: #fff;
            border-radius: 2px; padding: 13px 26px;
            font-size: 11px; font-weight: 600; letter-spacing: .09em; text-transform: uppercase;
            display: inline-block; transition: background .2s, transform .15s;
        }
        .btn-solid:hover { background: var(--terra-deep); transform: translateY(-1px); }
        .btn-wire {
            background: transparent; border: 1px solid rgba(237,232,224,.25);
            color: var(--text-night); border-radius: 2px; padding: 13px 26px;
            font-size: 11px; font-weight: 400; letter-spacing: .06em;
            display: inline-block; transition: border-color .2s, background .2s;
        }
        .btn-wire:hover { border-color: rgba(237,232,224,.5); background: rgba(237,232,224,.07); }

        .hero-dots { display: flex; gap: 6px; align-items: center; }
        .hero-dot {
            width: 20px; height: 2px; border-radius: 2px;
            background: rgba(237,232,224,.22); cursor: pointer;
            transition: all .35s;
        }
        .hero-dot.on { background: var(--gold); width: 38px; }

        /* DESKTOP hero */
        @media (min-width: 900px) {
            .hero { min-height: 100vh; display: flex; }
            .hero-mob { display: none; }
            .hero-desk {
                display: grid; grid-template-columns: 44fr 56fr;
                width: 100%; min-height: 100vh;
            }
            .hero-txt-col {
                display: flex; flex-direction: column; justify-content: flex-end;
                padding: 88px 64px 88px 72px;
                background: var(--bg-night);
                position: relative; z-index: 2;
            }
            /* angled cut */
            .hero-txt-col::after {
                content: '';
                position: absolute; right: -48px; top: 0; bottom: 0; width: 96px;
                background: var(--bg-night);
                clip-path: polygon(0 0, 48px 0, 100% 100%, 0 100%);
                z-index: 1;
            }
            .hero-h1 {
                font-size: clamp(56px, 5.8vw, 92px);
                max-width: 540px; margin-bottom: 22px;
            }
            .hero-sub { font-size: 15px; max-width: 340px; margin-bottom: 40px; }
            .hero-ctas { margin-bottom: 46px; }
            .hero-img-col { position: relative; overflow: hidden; }
            .hero-img-col .hc { position: absolute; inset: 0; }
        }
        @media (max-width: 899px) {
            .hero-desk { display: none; }
        }

        /* ════════════════════════════
           MARQUEE STRIP
        ════════════════════════════ */
        .marquee {
            background: var(--terra); overflow: hidden; padding: 11px 0;
        }
        .marquee-track {
            display: flex; gap: 0; width: max-content;
            animation: scroll-marquee 24s linear infinite;
        }
        .marquee-track:hover { animation-play-state: paused; }
        @keyframes scroll-marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }
        .marquee-item {
            display: inline-flex; align-items: center; gap: 18px;
            white-space: nowrap; padding: 0 28px;
            font-size: 10px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase;
            color: rgba(255,255,255,.9);
        }
        .marquee-sep { color: rgba(255,255,255,.45); font-size: 13px; }

        /* ════════════════════════════
           ABOUT
        ════════════════════════════ */
        .about-grid { margin-top: 44px; }
        .about-photo {
            border-radius: 2px; overflow: hidden;
            margin-bottom: 32px;
            box-shadow: 0 20px 60px rgba(30,28,26,.1);
            position: relative;
        }
        .about-photo img { width: 100%; height: auto; display: block; }
        .about-photo-cap {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 36px 18px 16px;
            background: linear-gradient(transparent, rgba(26,31,46,.75));
            font-family: var(--display); font-style: italic;
            font-size: 13px; color: rgba(237,232,224,.8); letter-spacing: .02em;
        }

        .timeline { margin-top: 30px; display: flex; flex-direction: column; gap: 0; }
        .tl-row { display: flex; gap: 17px; padding-bottom: 24px; position: relative; }
        .tl-row:not(:last-child)::before {
            content: ''; position: absolute; left: 4px; top: 20px; bottom: 0;
            width: 1px; background: linear-gradient(rgba(201,168,108,.3), transparent);
        }
        .tl-dot {
            width: 9px; height: 9px; border-radius: 50%; background: var(--gold);
            flex-shrink: 0; margin-top: 5px;
            box-shadow: 0 0 0 3px var(--gold-faint);
        }
        .tl-label { font-size: 9px; font-weight: 600; color: var(--gold); text-transform: uppercase; letter-spacing: .14em; margin-bottom: 3px; }
        .tl-name  { font-family: var(--display); font-size: 16px; font-weight: 400; color: var(--text-main); margin-bottom: 4px; line-height: 1.25; }
        .tl-desc  { font-size: 12px; color: var(--text-muted); line-height: 1.62; }

        @media (min-width: 900px) {
            .about-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 88px; align-items: start; margin-top: 60px; }
            .about-photo { margin-bottom: 0; }
            .tl-name { font-size: 18px; }
        }

        /* ════════════════════════════
           DNA — editorial grid
        ════════════════════════════ */
        .dna-grid {
            display: grid; grid-template-columns: 1fr; gap: 1px;
            margin-top: 44px; background: rgba(201,168,108,.2);
            border: 1px solid rgba(201,168,108,.2); border-radius: 2px; overflow: hidden;
        }
        .dna-card {
            background: var(--bg-linen); display: flex;
            transition: background .25s;
        }
        .dna-card:hover { background: var(--bg-primary); }
        .dna-photo { width: 115px; flex-shrink: 0; overflow: hidden; }
        .dna-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
        .dna-card:hover .dna-photo img { transform: scale(1.06); }
        .dna-info { padding: 22px 20px; display: flex; flex-direction: column; justify-content: center; flex: 1; }
        .dna-num  { font-size: 9px; font-weight: 600; color: var(--gold); letter-spacing: .14em; text-transform: uppercase; margin-bottom: 5px; }
        .dna-name { font-family: var(--display); font-size: 21px; font-weight: 400; color: var(--text-main); margin-bottom: 7px; line-height: 1.18; }
        .dna-desc { font-size: 12px; color: var(--text-muted); line-height: 1.62; }

        @media (min-width: 900px) {
            .dna-grid { grid-template-columns: repeat(3, 1fr); }
            .dna-card { flex-direction: column; }
            .dna-photo { width: 100%; height: 270px; flex-shrink: unset; }
            .dna-info { padding: 28px 26px; }
            .dna-name { font-size: 25px; }
            .dna-desc { font-size: 13px; }
        }

        /* ════════════════════════════
           CRAFT
        ════════════════════════════ */
        .craft-txt { margin-bottom: 36px; }
        .craft-pair {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
        }
        .craft-photo {
            border-radius: 2px; overflow: hidden; position: relative; aspect-ratio: 4/5;
        }
        .craft-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
        .craft-photo:hover img { transform: scale(1.05); }
        .craft-label {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 44px 14px 14px;
            background: linear-gradient(transparent, rgba(26,31,46,.82));
            color: var(--text-night);
        }
        .craft-label .t { font-family: var(--display); font-size: 15px; font-weight: 400; line-height: 1.2; }
        .craft-label .b { font-size: 11px; opacity: .7; margin-top: 4px; line-height: 1.4; }

        @media (min-width: 900px) {
            .craft-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 88px; align-items: center; }
            .craft-txt { margin-bottom: 0; }
            .craft-pair { gap: 14px; }
        }

        /* ════════════════════════════
           COLLECTIONS
        ════════════════════════════ */
        .coll-head { margin-bottom: 0; }
        .coll-scroll {
            display: flex; gap: 12px; overflow-x: auto;
            padding: 32px 0 8px; scroll-snap-type: x mandatory; scrollbar-width: none;
        }
        .coll-scroll::-webkit-scrollbar { display: none; }
        .coll-card {
            flex: 0 0 150px; scroll-snap-align: start;
            border-radius: 2px; overflow: hidden;
            border: 1px solid var(--border);
            background: var(--bg-primary);
            transition: box-shadow .25s, transform .25s;
        }
        .coll-card:hover { box-shadow: 0 14px 40px rgba(30,28,26,.1); transform: translateY(-3px); }
        .coll-photo { aspect-ratio: 3/4; overflow: hidden; background: var(--bg-linen); }
        .coll-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s; }
        .coll-card:hover .coll-photo img { transform: scale(1.07); }
        .coll-body { padding: 12px 13px 15px; }
        .coll-cat  { font-size: 9px; font-weight: 600; color: var(--terra); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 4px; }
        .coll-name { font-family: var(--display); font-size: 15px; font-weight: 400; color: var(--text-main); line-height: 1.22; }
        .coll-price { font-size: 12px; color: var(--text-muted); margin-top: 3px; font-variant-numeric: tabular-nums; }
        .coll-cta { margin-top: 28px; }
        .btn-outline {
            display: inline-block;
            border: 1.5px solid var(--text-main); color: var(--text-main);
            border-radius: 2px; padding: 12px 32px;
            font-size: 11px; font-weight: 600; letter-spacing: .09em; text-transform: uppercase;
            transition: background .2s, color .2s;
        }
        .btn-outline:hover { background: var(--text-main); color: var(--bg-primary); }

        @media (min-width: 900px) {
            .coll-head { display: flex; justify-content: space-between; align-items: flex-end; }
            .coll-head-txt { max-width: 520px; }
            .coll-scroll {
                display: grid; grid-template-columns: repeat(5, 1fr);
                overflow-x: visible; padding: 40px 0 8px; gap: 18px;
            }
            .coll-card { flex: none; }
            .coll-cta { margin-top: 36px; text-align: center; }
        }

        /* ════════════════════════════
           PROCESS
        ════════════════════════════ */
        .proc-list { margin-top: 40px; display: flex; flex-direction: column; }
        .proc-row {
            display: flex; gap: 18px; padding: 22px 0;
            border-bottom: 1px solid var(--border);
        }
        .proc-row:first-child { border-top: 1px solid var(--border); }
        .proc-thumb {
            width: 66px; height: 66px; border-radius: 2px;
            overflow: hidden; flex-shrink: 0; background: var(--bg-linen);
        }
        .proc-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .proc-copy { flex: 1; min-width: 0; padding-top: 2px; }
        .proc-num  { font-size: 9px; font-weight: 600; color: var(--gold); letter-spacing: .16em; text-transform: uppercase; margin-bottom: 4px; }
        .proc-name { font-family: var(--display); font-size: 17px; font-weight: 400; color: var(--text-main); margin-bottom: 4px; line-height: 1.22; }
        .proc-desc { font-size: 12px; color: var(--text-muted); line-height: 1.58; }
        .proc-note {
            margin-top: 28px;
            font-family: var(--display); font-style: italic;
            font-size: 14px; color: var(--text-muted);
            text-align: center; line-height: 1.65;
        }

        @media (min-width: 900px) {
            .proc-list {
                display: grid; grid-template-columns: repeat(5, 1fr);
                border-top: 1px solid var(--border);
            }
            .proc-row {
                flex-direction: column; padding: 30px 28px 30px 0; gap: 14px;
                border-bottom: none;
                border-left: 1px solid var(--border); padding-left: 28px;
            }
            .proc-row:first-child { border-top: none; border-left: none; padding-left: 0; }
            .proc-thumb { width: 52px; height: 52px; }
            .proc-name { font-size: 19px; }
            .proc-note { max-width: 580px; margin: 28px auto 0; }
        }

        /* ════════════════════════════
           PARTNERS
        ════════════════════════════ */
        .part-layout { margin-top: 44px; }
        .part-photo {
            border-radius: 2px; overflow: hidden;
            margin-bottom: 28px;
            box-shadow: 0 16px 56px rgba(30,28,26,.1);
        }
        .part-photo img { width: 100%; height: auto; display: block; }
        .part-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 0; border-bottom: 1px solid var(--border);
        }
        .part-row:last-child { border-bottom: none; }
        .part-name { font-family: var(--display); font-size: 17px; font-weight: 400; color: var(--text-main); margin-bottom: 2px; }
        .part-desc { font-size: 12px; color: var(--text-muted); }
        .part-badge {
            font-size: 9px; font-weight: 600; color: var(--gold);
            border: 1px solid var(--gold-faint); border-radius: 100px;
            padding: 4px 9px; letter-spacing: .08em; text-transform: uppercase;
            flex-shrink: 0; margin-left: 14px;
        }

        @media (min-width: 900px) {
            .part-layout { display: grid; grid-template-columns: 1.15fr 1fr; gap: 88px; align-items: center; margin-top: 60px; }
            .part-photo { margin-bottom: 0; }
        }

        /* ════════════════════════════
           TESTIMONIALS
        ════════════════════════════ */
        .testi-outer { overflow: hidden; margin-top: 36px; }
        .testi-track {
            display: flex; gap: 14px; overflow-x: auto;
            scroll-snap-type: x mandatory; scrollbar-width: none; padding: 4px 4px 10px;
        }
        .testi-track::-webkit-scrollbar { display: none; }
        .testi-card {
            flex: 0 0 82%; scroll-snap-align: center;
            border-radius: 2px; overflow: hidden;
            box-shadow: 0 6px 28px rgba(30,28,26,.1);
            border: 1px solid var(--border);
        }
        .testi-card img { width: 100%; height: auto; display: block; }
        .testi-dots { display: flex; justify-content: center; gap: 6px; margin-top: 16px; }
        .testi-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: rgba(30,28,26,.15); transition: all .3s;
        }
        .testi-dot.on { background: var(--terra); width: 22px; border-radius: 3px; }

        @media (min-width: 900px) {
            .testi-outer { overflow: visible; margin-top: 48px; }
            .testi-track {
                display: grid; grid-template-columns: repeat(3, 1fr);
                overflow-x: visible; gap: 24px; padding: 4px;
            }
            .testi-card { flex: none; }
            .testi-dots { display: none; }
        }

        /* ════════════════════════════
           CONTACT / FOOTER — midnight
        ════════════════════════════ */
        .contact {
            background: var(--bg-night); color: var(--text-night);
            padding: 80px 22px 56px;
        }
        .ft-brand {
            font-family: var(--display);
            font-size: 13px; font-weight: 400; letter-spacing: .3em; text-transform: uppercase;
            color: var(--gold); margin-bottom: 28px;
        }
        .contact-h2 {
            font-family: var(--display);
            font-size: clamp(30px, 5.5vw, 52px);
            font-weight: 300; line-height: 1.08; letter-spacing: -.02em;
            color: var(--text-night); margin-bottom: 10px; text-wrap: balance;
        }
        .contact-h2 em { font-style: italic; color: #E8A882; }
        .contact-sub { font-size: 14px; color: var(--text-dim); line-height: 1.7; }
        .contact-ctas { display: flex; flex-direction: column; gap: 10px; margin: 32px 0; }
        .btn-wa {
            display: flex; align-items: center; justify-content: center; gap: 9px;
            background: rgba(37,211,102,.08); border: 1px solid rgba(37,211,102,.22);
            color: #25d366; border-radius: 2px;
            padding: 13px 24px; font-size: 11px; font-weight: 600; letter-spacing: .06em;
            transition: background .2s;
        }
        .btn-wa:hover { background: rgba(37,211,102,.16); }
        .btn-wa svg { width: 16px; height: 16px; flex-shrink: 0; }
        .ft-divider { height: 1px; background: var(--border-night); margin: 30px 0; }
        .ft-info { display: flex; flex-direction: column; gap: 13px; }
        .ft-row {
            display: flex; align-items: center; gap: 12px;
            font-size: 13px; color: var(--text-dim);
        }
        .ft-row svg { width: 14px; height: 14px; flex-shrink: 0; color: var(--gold); }
        .ft-copy {
            font-size: 11px; color: rgba(237,232,224,.2);
            margin-top: 40px; text-align: center; letter-spacing: .06em;
        }

        @media (min-width: 900px) {
            .contact { padding: 112px 72px 80px; }
            .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 100px; align-items: start; }
            .contact-ctas { flex-direction: row; max-width: 400px; }
            .ft-divider { display: none; }
            .ft-info { padding-top: 28px; }
            .ft-copy { text-align: left; margin-top: 52px; }
        }
    </style>
</head>
<body>
<div class="shell">

{{-- HEADER --}}
<header class="hd">
    <div class="hd-inner">
        <a href="/" class="hd-brand">
            <img src="/images/logo.svg" alt="Atelier" class="hd-logo">
            <span class="hd-wordmark">Atelier</span>
        </a>
        <nav class="hd-nav">
            <a href="/produk" class="hd-link" x-text="lang==='id' ? 'Katalog' : 'Catalog'"></a>
            <button class="lang-btn" @click="toggleLang()">
                <span x-text="lang==='id' ? '🇮🇩' : '🇬🇧'"></span>
                <span x-text="lang==='id' ? 'EN' : 'ID'"></span>
            </button>
        </nav>
    </div>
</header>

{{-- ══ HERO ══ --}}
@if(($hero = $get('hero')) && $hero->is_active)
<section class="hero">

    {{-- Mobile --}}
    <div class="hero-mob">
        <div class="hc">
            @foreach($hero->activeItems as $i => $item)
            <div class="hs" :class="heroSlide === {{ $i }} ? 'on' : ''">
                <img src="{{ $item->imageUrl() }}" alt="{{ $item->title('en') }}" loading="{{ $i===0?'eager':'lazy' }}">
            </div>
            @endforeach
        </div>
        <div class="hero-vignette"></div>
        <div class="hero-copy">
            <div class="hero-eyebrow" x-text="lang==='id' ? {{ Js::from($hero->eyebrow_id) }} : {{ Js::from($hero->eyebrow_en) }}"></div>
            <h1 class="hero-h1" x-text="lang==='id' ? {{ Js::from($hero->title_id) }} : {{ Js::from($hero->title_en) }}"></h1>
            <p class="hero-sub" x-text="lang==='id' ? {{ Js::from($hero->subtitle_id) }} : {{ Js::from($hero->subtitle_en) }}"></p>
            <div class="hero-ctas">
                <a href="{{ $hero->cta_url }}" class="btn-solid" x-text="lang==='id' ? {{ Js::from($hero->cta_label_id) }} : {{ Js::from($hero->cta_label_en) }}"></a>
                <a href="#about-anchor" class="btn-wire" x-text="lang==='id' ? 'Kisah Kami' : 'Our Story'"></a>
            </div>
            <div class="hero-dots">
                @foreach($hero->activeItems as $i => $item)
                <button class="hero-dot" :class="heroSlide === {{ $i }} ? 'on' : ''" @click="heroSlide = {{ $i }}" aria-label="Slide {{ $i+1 }}"></button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Desktop --}}
    <div class="hero-desk">
        <div class="hero-txt-col">
            <div style="flex:1; min-height:80px"></div>
            <div class="hero-eyebrow" x-text="lang==='id' ? {{ Js::from($hero->eyebrow_id) }} : {{ Js::from($hero->eyebrow_en) }}"></div>
            <h1 class="hero-h1" x-text="lang==='id' ? {{ Js::from($hero->title_id) }} : {{ Js::from($hero->title_en) }}"></h1>
            <p class="hero-sub" x-text="lang==='id' ? {{ Js::from($hero->subtitle_id) }} : {{ Js::from($hero->subtitle_en) }}"></p>
            <div class="hero-ctas">
                <a href="{{ $hero->cta_url }}" class="btn-solid" x-text="lang==='id' ? {{ Js::from($hero->cta_label_id) }} : {{ Js::from($hero->cta_label_en) }}"></a>
                <a href="#about-anchor" class="btn-wire" x-text="lang==='id' ? 'Kisah Kami' : 'Our Story'"></a>
            </div>
            <div class="hero-dots">
                @foreach($hero->activeItems as $i => $item)
                <button class="hero-dot" :class="heroSlide === {{ $i }} ? 'on' : ''" @click="heroSlide = {{ $i }}" aria-label="Slide {{ $i+1 }}"></button>
                @endforeach
            </div>
        </div>
        <div class="hero-img-col">
            <div class="hc">
                @foreach($hero->activeItems as $i => $item)
                <div class="hs" :class="heroSlide === {{ $i }} ? 'on' : ''">
                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title('en') }}" loading="{{ $i===0?'eager':'lazy' }}" style="width:100%;height:100%;object-fit:cover;object-position:top center">
                </div>
                @endforeach
            </div>
        </div>
    </div>

</section>
@endif

{{-- MARQUEE --}}
<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        @php $words = ['Modest Fashion','Indonesian Heritage','Handcrafted Textiles','Ethnic & Contemporary','Wear Heritage','Live Purpose','Modest Fashion','Indonesian Heritage','Handcrafted Textiles','Ethnic & Contemporary','Wear Heritage','Live Purpose']; @endphp
        @foreach($words as $w)
        <span class="marquee-item"><span>{{ $w }}</span><span class="marquee-sep">✦</span></span>
        @endforeach
    </div>
</div>

{{-- ══ ABOUT ══ --}}
@if(($about = $get('about')) && $about->is_active)
<section class="sec sec-white" id="about-anchor">
    <div class="wrap">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($about->eyebrow_id) }} : {{ Js::from($about->eyebrow_en) }}"></div>
        <h2 class="h2" x-text="lang==='id' ? {{ Js::from($about->title_id) }} : {{ Js::from($about->title_en) }}"></h2>
        <div class="about-grid">
            @if($firstAboutImg = $about->activeItems->first(fn($i) => $i->image_path))
            <div class="about-photo">
                <img src="{{ $firstAboutImg->imageUrl() }}" alt="Atelier" loading="lazy">
                @if($firstAboutImg->title('en'))
                <div class="about-photo-cap" x-text="lang==='id' ? {{ Js::from($firstAboutImg->title_id) }} : {{ Js::from($firstAboutImg->title_en) }}"></div>
                @endif
            </div>
            @endif
            <div>
                <div class="body-text" x-text="lang==='id' ? {{ Js::from($about->body_id) }} : {{ Js::from($about->body_en) }}"></div>
                <div class="timeline">
                    @foreach($about->activeItems as $item)
                    <div class="tl-row">
                        <div class="tl-dot"></div>
                        <div>
                            <div class="tl-label" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                            <div class="tl-name"  x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                            <div class="tl-desc"  x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<div class="gold-rule">✦</div>

{{-- ══ DNA ══ --}}
@if(($dna = $get('dna')) && $dna->is_active)
<section class="sec sec-linen">
    <div class="wrap">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($dna->eyebrow_id) }} : {{ Js::from($dna->eyebrow_en) }}"></div>
        <h2 class="h2" x-text="lang==='id' ? {{ Js::from($dna->title_id) }} : {{ Js::from($dna->title_en) }}"></h2>
        @if($dna->subtitle_en || $dna->subtitle_id)
        <div class="sub-text" x-text="lang==='id' ? {{ Js::from($dna->subtitle_id) }} : {{ Js::from($dna->subtitle_en) }}"></div>
        @endif
        <div class="dna-grid">
            @foreach($dna->activeItems as $item)
            <div class="dna-card">
                @if($item->imageUrl())
                <div class="dna-photo"><img src="{{ $item->imageUrl() }}" alt="" loading="lazy"></div>
                @endif
                <div class="dna-info">
                    <div class="dna-num"  x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                    <div class="dna-name" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                    <div class="dna-desc" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ CRAFT ══ --}}
@if(($craft = $get('craft')) && $craft->is_active)
<section class="sec sec-white">
    <div class="wrap">
        <div class="craft-layout">
            <div class="craft-txt">
                <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($craft->eyebrow_id) }} : {{ Js::from($craft->eyebrow_en) }}"></div>
                <h2 class="h2" x-text="lang==='id' ? {{ Js::from($craft->title_id) }} : {{ Js::from($craft->title_en) }}"></h2>
                <div class="body-text" x-text="lang==='id' ? {{ Js::from($craft->body_id) }} : {{ Js::from($craft->body_en) }}"></div>
            </div>
            <div class="craft-pair">
                @foreach($craft->activeItems as $item)
                <div class="craft-photo">
                    <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                    <div class="craft-label">
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
@if(($collections = $get('collections')) && $collections->is_active)
<section class="sec sec-linen" style="padding-bottom:60px">
    <div class="wrap">
        <div class="coll-head">
            <div class="coll-head-txt">
                <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($collections->eyebrow_id) }} : {{ Js::from($collections->eyebrow_en) }}"></div>
                <h2 class="h2" x-text="lang==='id' ? {{ Js::from($collections->title_id) }} : {{ Js::from($collections->title_en) }}"></h2>
                @if($collections->subtitle_en)
                <div class="sub-text" x-text="lang==='id' ? {{ Js::from($collections->subtitle_id) }} : {{ Js::from($collections->subtitle_en) }}"></div>
                @endif
            </div>
        </div>
        <div class="coll-scroll">
            @foreach($featuredProducts as $product)
            <a href="/produk/{{ $product->product_code }}" class="coll-card">
                <div class="coll-photo">
                    @if($product->photos->first())
                    <img src="{{ asset('storage/'.$product->photos->first()->photo_path) }}" alt="{{ $product->nama }}" loading="lazy">
                    @else
                    <div style="width:100%;height:100%;background:var(--bg-linen)"></div>
                    @endif
                </div>
                <div class="coll-body">
                    <div class="coll-cat">{{ $product->garmentCategory?->nama ?? '' }}</div>
                    <div class="coll-name">{{ $product->nama }}</div>
                    @if($product->harga)
                    <div class="coll-price">IDR {{ number_format($product->harga,0,',','.') }}</div>
                    @endif
                </div>
            </a>
            @endforeach
            @if($featuredProducts->isEmpty())
            @foreach($collections->activeItems as $item)
            <a href="{{ $item->link_url ?: $collections->cta_url }}" class="coll-card">
                <div class="coll-photo">
                    @if($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                    @endif
                </div>
                <div class="coll-body">
                    <div class="coll-cat"  x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                    <div class="coll-name" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                </div>
            </a>
            @endforeach
            @endif
        </div>
        <div class="coll-cta">
            <a href="{{ $collections->cta_url }}" class="btn-outline" x-text="lang==='id' ? {{ Js::from($collections->cta_label_id) }} : {{ Js::from($collections->cta_label_en) }}"></a>
        </div>
    </div>
</section>
@endif

<div class="gold-rule">✦</div>

{{-- ══ PROCESS ══ --}}
@if(($process = $get('process')) && $process->is_active)
<section class="sec sec-white">
    <div class="wrap">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($process->eyebrow_id) }} : {{ Js::from($process->eyebrow_en) }}"></div>
        <h2 class="h2" x-text="lang==='id' ? {{ Js::from($process->title_id) }} : {{ Js::from($process->title_en) }}"></h2>
        @if($process->subtitle_en)
        <div class="sub-text" x-text="lang==='id' ? {{ Js::from($process->subtitle_id) }} : {{ Js::from($process->subtitle_en) }}"></div>
        @endif
        <div class="proc-list">
            @foreach($process->activeItems as $item)
            <div class="proc-row">
                <div class="proc-thumb">
                    @if($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                    @endif
                </div>
                <div class="proc-copy">
                    <div class="proc-num"  x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></div>
                    <div class="proc-name" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                    <div class="proc-desc" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        @if($process->body_en)
        <div class="proc-note" x-text="lang==='id' ? {{ Js::from($process->body_id) }} : {{ Js::from($process->body_en) }}"></div>
        @endif
    </div>
</section>
@endif

{{-- ══ PARTNERS ══ --}}
@if(($partners = $get('partners')) && $partners->is_active)
<section class="sec sec-linen">
    <div class="wrap">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($partners->eyebrow_id) }} : {{ Js::from($partners->eyebrow_en) }}"></div>
        <h2 class="h2" x-text="lang==='id' ? {{ Js::from($partners->title_id) }} : {{ Js::from($partners->title_en) }}"></h2>
        @php $partnerPhoto = $partners->activeItems->first(fn($i) => $i->image_path); @endphp
        <div class="part-layout">
            @if($partnerPhoto)
            <div class="part-photo"><img src="{{ $partnerPhoto->imageUrl() }}" alt="" loading="lazy"></div>
            @endif
            <div>
                @foreach($partners->activeItems->filter(fn($i) => !$i->image_path) as $item)
                <div class="part-row">
                    <div>
                        <div class="part-name" x-text="lang==='id' ? {{ Js::from($item->title_id) }} : {{ Js::from($item->title_en) }}"></div>
                        <div class="part-desc" x-text="lang==='id' ? {{ Js::from($item->body_id) }} : {{ Js::from($item->body_en) }}"></div>
                    </div>
                    @if($item->label_en)
                    <span class="part-badge" x-text="lang==='id' ? {{ Js::from($item->label_id) }} : {{ Js::from($item->label_en) }}"></span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ TESTIMONIALS ══ --}}
@if(($testimonials = $get('testimonials')) && $testimonials->is_active)
<section class="sec sec-white" style="overflow:hidden">
    <div class="wrap">
        <div class="eyebrow" x-text="lang==='id' ? {{ Js::from($testimonials->eyebrow_id) }} : {{ Js::from($testimonials->eyebrow_en) }}"></div>
        <h2 class="h2" x-text="lang==='id' ? {{ Js::from($testimonials->title_id) }} : {{ Js::from($testimonials->title_en) }}"></h2>
        <div class="testi-outer">
            <div class="testi-track" x-ref="testiTrack" @scroll="onTestiScroll()">
                @foreach($testimonials->activeItems as $item)
                <div class="testi-card"><img src="{{ $item->imageUrl() }}" alt="" loading="lazy"></div>
                @endforeach
            </div>
        </div>
        <div class="testi-dots">
            @foreach($testimonials->activeItems as $i => $item)
            <div class="testi-dot" :class="testiSlide === {{ $i }} ? 'on' : ''"></div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ CONTACT ══ --}}
@if(($contact = $get('contact')) && $contact->is_active)
<footer class="contact">
    <div class="wrap contact-grid" style="padding-top:0;padding-bottom:0">
        <div>
            <div class="ft-brand">Atelier</div>
            <h2 class="contact-h2" x-text="lang==='id' ? {{ Js::from($contact->title_id) }} : {{ Js::from($contact->title_en) }}"></h2>
            <div class="contact-sub" x-text="lang==='id' ? {{ Js::from($contact->subtitle_id) }} : {{ Js::from($contact->subtitle_en) }}"></div>
            <div class="contact-ctas">
                <a href="{{ $contact->cta_url }}" class="btn-solid" x-text="lang==='id' ? {{ Js::from($contact->cta_label_id) }} : {{ Js::from($contact->cta_label_en) }}"></a>
                <a :href="'https://wa.me/{{ $waNumber }}'" target="_blank" rel="noopener" class="btn-wa">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.55 4.107 1.514 5.833L.057 23.394a.5.5 0 00.549.549l5.56-1.457A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.866 9.866 0 01-5.031-1.377l-.36-.214-3.731.979.994-3.634-.234-.374A9.867 9.867 0 012.118 12C2.118 6.534 6.534 2.118 12 2.118c5.466 0 9.882 4.416 9.882 9.882 0 5.466-4.416 9.882-9.882 9.882z"/></svg>
                    <span>Chat via WhatsApp</span>
                </a>
            </div>
            <div class="ft-copy">&copy; {{ date('Y') }} Atelier. All rights reserved.</div>
        </div>
        <div>
            <div class="ft-divider"></div>
            <div class="ft-info">
                <div class="ft-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z"/></svg>
                    <span>example.com</span>
                </div>
                <div class="ft-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                    <span>@atelier.demo</span>
                </div>
                <div class="ft-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>hello@example.com</span>
                </div>
                <div class="ft-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Sample City, Indonesia</span>
                </div>
            </div>
        </div>
    </div>
</footer>
@endif

</div>{{-- /shell --}}

<script>
function landingApp() {
    return {
        lang: (function(){ try{ return localStorage.getItem('atelier_lang')||'en' }catch(e){ return 'en' } })(),
        heroSlide: 0,
        testiSlide: 0,
        _heroTimer: null,
        init(){
            this.startHeroAutoplay();
        },
        toggleLang(){
            this.lang = this.lang==='id' ? 'en' : 'id';
            try{ localStorage.setItem('atelier_lang', this.lang) }catch(e){}
        },
        startHeroAutoplay(){
            const n = {{ ($get('hero') && $get('hero')->activeItems) ? $get('hero')->activeItems->count() : 0 }};
            if(n <= 1) return;
            this._heroTimer = setInterval(() => { this.heroSlide = (this.heroSlide + 1) % n; }, 4800);
        },
        onTestiScroll(){
            const t = this.$refs.testiTrack;
            if(!t) return;
            const w = t.firstElementChild ? t.firstElementChild.offsetWidth + 14 : 1;
            this.testiSlide = Math.round(t.scrollLeft / w);
        }
    }
}
</script>
<script src="/js/alpine.min.js" defer></script>
</body>
</html>
