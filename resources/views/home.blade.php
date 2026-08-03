@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    /* Full-width home page overrides */
    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
        padding: 0;
        background-color: #0b1120 !important;
    }

    body {
        min-height: 100vh;
        background-color: #0b1120 !important;
    }

    /* Remove main wrapper constraints */
    main {
        padding-top: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        flex: 1;
    }

    /* All sections fill the viewport width */
    section {
        width: 100vw;
        max-width: 100vw;
        margin-left: 0;
        margin-right: 0;
        box-sizing: border-box;
        overflow-x: hidden;
    }


    /* ─── HERO SECTION ──────────────────────────────────── */
    .hero-bg {
        background-color: var(--bg);
        background-image:
            radial-gradient(ellipse at 70% 50%, var(--radial-hero-1) 0%, transparent 60%),
            radial-gradient(ellipse at 20% 80%, var(--radial-hero-2) 0%, transparent 55%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        padding-top: 72px;
        width: 100%;
        box-sizing: border-box;
        transition: background-color 0.3s ease;
    }

    /* Dot-grid overlay */
    .hero-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, var(--grid-dot-hero) 1px, transparent 1px);
        background-size: 28px 28px;
        pointer-events: none;
        z-index: 0;
    }

    /* Top glow accent */
    .hero-bg::after {
        content: '';
        position: absolute;
        top: -120px;
        left: 50%;
        transform: translateX(-50%);
        width: 900px;
        height: 400px;
        background: radial-gradient(ellipse, rgba(13,148,136,0.07) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }

    .hero-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2rem;
        padding: 4rem clamp(1rem, 3vw, 3rem);
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
    }

    @media (min-width: 1024px) {
        .hero-content {
            flex-direction: row;
            align-items: center;
            gap: 4rem;
        }
    }

    /* ── Text Side ── */
    .hero-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    [dir="rtl"] .hero-text { align-items: flex-start; text-align: right; }
    [dir="ltr"] .hero-text { align-items: flex-start; text-align: left; }

    @media (max-width: 1023px) {
        .hero-bg {
            min-height: auto;
            padding-top: 80px;
            padding-bottom: 3rem;
        }
        .hero-text { align-items: center; text-align: center; }
        .hero-visual { order: -1; }
    }

    /* Tagline badge */
    .tagline-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        background: rgba(13, 148, 136, 0.08);
        border: 1px solid rgba(13, 148, 136, 0.25);
        border-radius: 9999px;
        color: #2dd4bf;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        letter-spacing: 0.01em;
        backdrop-filter: blur(4px);
    }

    .tagline-badge svg { color: #0d9488; flex-shrink: 0; }

    /* main title */
    .hero-title {
        font-size: clamp(2.4rem, 4.5vw, 3.8rem);
        line-height: 1.18;
        font-weight: 900;
        color: #f1f5f9;
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }

    .hero-title .highlight {
        background: linear-gradient(135deg, #0d9488, #2dd4bf);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: block;
    }

    /* description */
    .hero-desc {
        font-size: clamp(0.9rem, 1.4vw, 1.05rem);
        line-height: 1.9;
        color: var(--text-secondary);
        margin-bottom: 2.5rem;
        max-width: 480px;
    }

    /* buttons */
    .btn-group {
        display: flex;
        gap: 0.875rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0f766e, #0d9488) !important;
        color: #0b1120 !important;
        padding: 0.875rem 1.75rem;
        border-radius: 0.625rem;
        font-weight: 800;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
        white-space: nowrap;
        border: none;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 0 22px rgba(13,148,136,0.35);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(13,148,136,0.50);
        filter: brightness(1.08);
    }

    .btn-outline {
        background: var(--bg-card);
        border: 1px solid var(--bg-border);
        color: var(--text-primary);
        padding: 0.875rem 1.75rem;
        border-radius: 0.625rem;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-outline:hover {
        background: var(--bg-card-hover);
        border-color: var(--green);
        color: var(--green);
        transform: translateY(-2px);
    }

    /* ── Stats Card (Visual Side) ── */
    .hero-visual {
        flex: 0 0 auto;
        width: 100%;
        max-width: 500px;
    }

    @media (max-width: 1023px) {
        .hero-visual {
            max-width: 240px;
            overflow: visible;
        }
        .hero-visual img {
            width: 100%;
            max-width: 240px;
            height: auto;
            margin: 0 auto;
            display: block;
        }
    }

    @media (max-width: 640px) {
        .hero-title {
            font-size: clamp(1.8rem, 7vw, 2.4rem);
        }
        .hero-desc {
            font-size: 0.9rem;
            max-width: 100%;
        }
        .btn-group {
            flex-direction: column;
            width: 100%;
            align-items: center;
        }
        .btn-primary, .btn-outline {
            width: 100%;
            justify-content: center;
            font-size: 0.95rem;
            padding: 0.8rem 1.25rem;
        }
        .tagline-badge {
            font-size: 0.75rem;
        }
        .hero-visual {
            max-width: 200px;
        }
        .hero-visual img {
            max-width: 200px;
        }
        .hero-content {
            gap: 1.5rem;
            padding: 2rem 1rem;
        }
    }

    .stats-panel {
        background: rgba(17, 24, 39, 0.9);
        border: 1px solid rgba(13,148,136,0.15);
        border-radius: 1.25rem;
        padding: 2rem;
        width: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 40px rgba(13,148,136,0.08), inset 0 1px 0 rgba(255,255,255,0.05);
    }

    .stats-panel::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(13,148,136,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .stats-panel::after {
        content: '';
        position: absolute;
        bottom: -40px; left: 20px;
        width: 160px; height: 160px;
        background: radial-gradient(circle, rgba(79,70,229,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    .stats-header-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #e2e8f0;
    }

    .stars {
        display: flex;
        gap: 3px;
        color: #0d9488;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        direction: ltr;
    }

    .stat-box {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 0.875rem;
        padding: 1.25rem 1rem;
        text-align: center;
        transition: all 0.25s;
    }

    .stat-box:hover {
        border-color: rgba(13,148,136,0.25);
        background: rgba(13,148,136,0.04);
    }

    .stat-val {
        font-size: 1.7rem;
        font-weight: 900;
        color: #fff;
        margin-bottom: 0.25rem;
        letter-spacing: -0.01em;
    }

    .stat-val.green {
        background: linear-gradient(135deg, #0d9488, #2dd4bf);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-lbl {
        font-size: 0.72rem;
        color: #64748b;
        line-height: 1.5;
    }

    /* ─── Section Headings ─── */
    .section-title {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 900;
        color: #f1f5f9;
        margin-bottom: 1rem;
    }

    .section-subtitle {
        font-size: 1.05rem;
        color: #64748b;
        max-width: 700px;
        margin: 0 auto 3rem;
        line-height: 1.7;
    }

    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(13,148,136,0.08);
        border: 1px solid rgba(13,148,136,0.2);
        color: #2dd4bf;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.9rem;
        border-radius: 9999px;
        margin-bottom: 1rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    /* ─── Why Section ─── */
    .why-section {
        background: var(--bg-why);
        border-top: 1px solid var(--bg-border);
        padding: 5rem clamp(1.5rem, 5vw, 6rem);
        text-align: center;
        width: 100%;
        box-sizing: border-box;
        position: relative;
        transition: background-color 0.3s ease;
    }

    /* Line-grid overlay for why section */
    .why-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(to right, var(--grid-line-why) 1px, transparent 1px),
            linear-gradient(to bottom, var(--grid-line-why) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    .why-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .why-card {
        background: var(--bg-card);
        border: 1px solid var(--bg-border);
        border-radius: 1.25rem;
        padding: 2.5rem 2rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        position: relative;
        overflow: hidden;
    }

    .why-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(13,148,136,0.4), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .why-card:hover {
        border-color: rgba(13,148,136,0.25);
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.4), 0 0 30px rgba(13,148,136,0.06);
    }

    .why-card:hover::before { opacity: 1; }

    .why-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
    }

    .icon-green { background: rgba(13,148,136,0.1); color: #0d9488; border: 1px solid rgba(13,148,136,0.2); }
    .icon-blue  { background: rgba(79,70,229,0.1);  color: #818cf8; border: 1px solid rgba(79,70,229,0.2); }

    .why-card h3 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #f1f5f9;
        margin-bottom: 1rem;
    }

    .why-card p {
        color: #64748b;
        line-height: 1.7;
        font-size: 0.9rem;
    }

    /* ─── Steps Section ─── */
    .steps-section {
        background: var(--bg);
        padding: 5rem clamp(1.5rem, 5vw, 6rem);
        text-align: center;
        width: 100%;
        box-sizing: border-box;
        position: relative;
        transition: background-color 0.3s ease;
    }

    .steps-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, var(--grid-dot-hero) 1px, transparent 1px);
        background-size: 28px 28px;
        pointer-events: none;
    }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .step-item {
        text-align: center;
    }

    .step-circle {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 2px solid var(--bg-border);
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        font-weight: 900;
        color: var(--green);
        box-shadow: 0 0 20px var(--green-glow);
        transition: all 0.3s;
    }

    .step-item:hover .step-circle {
        border-color: #0d9488;
        box-shadow: 0 0 30px rgba(13,148,136,0.35);
        transform: scale(1.05);
    }

    .step-item h3 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #f1f5f9;
        margin-bottom: 1rem;
    }

    .step-item p {
        color: #64748b;
        line-height: 1.7;
        font-size: 0.9rem;
    }

    /* ─── CTA Section ─── */
    .cta-section {
        background: linear-gradient(135deg, #0d1a0f 0%, #0b1120 50%, #0d0f1a 100%);
        border-top: 1px solid rgba(13,148,136,0.1);
        border-bottom: 1px solid rgba(13,148,136,0.1);
        padding: 5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 600px; height: 300px;
        background: radial-gradient(ellipse, rgba(13,148,136,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .cta-content {
        max-width: 750px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .cta-title {
        font-size: clamp(2rem, 3vw, 2.75rem);
        font-weight: 900;
        margin-bottom: 1.5rem;
        color: #f1f5f9;
    }

    .cta-title span {
        background: linear-gradient(135deg, #0d9488, #2dd4bf);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .cta-subtitle {
        font-size: 1.05rem;
        line-height: 1.9;
        color: #64748b;
        margin-bottom: 2.5rem;
    }

    .btn-green {
        background: linear-gradient(135deg, #0f766e, #0d9488);
        color: #0b1120;
        padding: 1rem 2.5rem;
        border-radius: 0.5rem;
        font-weight: 900;
        font-size: 1.125rem;
        display: inline-block;
        transition: all 0.3s;
        box-shadow: 0 0 25px rgba(13,148,136,0.35);
    }

    .btn-green:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 40px rgba(13,148,136,0.5);
        filter: brightness(1.08);
    }

    /* ─── Services Section ─── */
    .services-section {
        background: var(--bg-why);
        border-top: 1px solid var(--bg-border);
        padding: 5rem clamp(1.5rem, 5vw, 6rem);
        width: 100%;
        box-sizing: border-box;
        position: relative;
        transition: background-color 0.3s ease;
    }

    .services-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(to right, var(--grid-line-why) 1px, transparent 1px),
            linear-gradient(to bottom, var(--grid-line-why) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    .services-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        max-width: 1200px;
        margin: 0 auto 3rem;
        position: relative;
        z-index: 1;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .expert-card {
        background: linear-gradient(135deg, var(--bg-card) 0%, rgba(17,24,39,0.3) 100%);
        border: 1px solid rgba(13, 148, 136, 0.12);
        border-radius: 1.5rem;
        padding: 2rem 1.75rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .expert-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(13, 148, 136, 0.4), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .expert-card:hover {
        border-color: rgba(13, 148, 136, 0.25);
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35), 0 0 25px rgba(13, 148, 136, 0.08);
    }

    .expert-card:hover::before {
        opacity: 1;
    }

    .ec-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .ec-avatar {
        display: flex;
        gap: 0.875rem;
        align-items: center;
    }

    .eca-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.18) 0%, rgba(13, 148, 136, 0.06) 100%);
        color: var(--green);
        border: 1px solid rgba(13, 148, 136, 0.28);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.25rem;
        box-shadow: 0 0 10px rgba(13, 148, 136, 0.08);
    }

    .eca-info h4 {
        font-weight: 800;
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .eca-info p {
        font-size: 0.72rem;
        color: var(--text-secondary);
        opacity: 0.85;
    }

    .ec-badge {
        background: rgba(13, 148, 136, 0.08);
        color: var(--green);
        border: 1px solid rgba(13, 148, 136, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 0.625rem;
        font-size: 0.68rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        backdrop-filter: blur(4px);
    }

    .ec-body {
        margin-bottom: 1.5rem;
    }

    .ec-body h3 {
        font-weight: 800;
        font-size: 1.15rem;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .ec-body p {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.8;
        margin-bottom: 1.25rem;
        height: 3.6rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Modern Tags Container */
    .ec-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .ec-tag {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--bg-border);
        color: var(--text-secondary);
    }

    .ec-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--bg-border);
        padding-top: 1.25rem;
    }

    .ecf-price { display: flex; flex-direction: column; }
    .ecf-price strong { font-size: 1.3rem; color: var(--text-primary); font-weight: 900; }
    .ecf-price span { font-size: 0.68rem; color: var(--text-secondary); }

    .ec-btn {
        background: linear-gradient(135deg, var(--green-dim) 0%, var(--green) 100%);
        color: #ffffff !important;
        font-weight: 800;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        transition: all 0.25s ease;
        border: none;
        cursor: pointer;
    }

    /* ─── AI PACKAGES SECTION ──────────────────────────── */
    .ai-packages-section {
        background-color: #0b1120;
        background-image:
            radial-gradient(ellipse at 50% 0%, rgba(16, 185, 129, 0.15) 0%, transparent 60%),
            radial-gradient(ellipse at 80% 50%, rgba(79, 70, 229, 0.08) 0%, transparent 50%);
        padding: 5rem 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .ai-packages-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 32px 32px;
        pointer-events: none;
    }
    .pkg-card {
        background: linear-gradient(145deg, rgba(15, 23, 42, 0.9) 0%, rgba(11, 17, 32, 0.95) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 1.25rem;
        padding: 2.25rem 2rem;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .pkg-card:hover {
        transform: translateY(-8px);
        border-color: rgba(16, 185, 129, 0.35);
        box-shadow: 0 20px 50px -15px rgba(16, 185, 129, 0.25);
    }
    .pkg-card.popular {
        background: linear-gradient(145deg, #0d2b3a 0%, #0a1f33 100%);
        border-color: rgba(16, 185, 129, 0.45);
        box-shadow: 0 10px 40px -10px rgba(16, 185, 129, 0.3);
    }
    .pkg-card.popular:hover {
        box-shadow: 0 25px 60px -10px rgba(16, 185, 129, 0.45);
    }
    .pkg-badge {
        position: absolute;
        top: -14px;
        right: 24px;
        background: linear-gradient(90deg, #10b981, #06b6d4);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.25rem 0.85rem;
        border-radius: 9999px;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }
    .pkg-btn-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff !important;
        font-weight: 800;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        transition: all 0.25s ease;
    }
    .pkg-btn-emerald:hover {
        background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
        transform: translateY(-2px);
    }
    .pkg-btn-outline {
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #e2e8f0 !important;
        background: rgba(255, 255, 255, 0.03);
        font-weight: 700;
        transition: all 0.25s ease;
    }
    .pkg-btn-outline:hover {
        border-color: rgba(16, 185, 129, 0.5);
        background: rgba(16, 185, 129, 0.1);
        color: #34d399 !important;
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="hero-bg" dir="{{ $direction }}">
    <div class="hero-content">

        {{-- ── Text Side (right in RTL) ── --}}
        <div class="hero-text">
            {{-- Tagline badge --}}
            <div class="tagline-badge">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                <span>{{ __('home.HERO_TAGLINE', [], $currentLang) }}</span>
            </div>

            {{-- Main heading --}}
            <h1 class="hero-title">
                {{ __('home.HERO_TITLE_LINE1', [], $currentLang) }}
                <span class="highlight">{{ __('home.HERO_TITLE_HIGHLIGHT', [], $currentLang) }}</span>
            </h1>

            {{-- Description --}}
            <p class="hero-desc">
                {{ __('home.HERO_DESCRIPTION', [], $currentLang) }}
            </p>

            {{-- Buttons --}}
            <div class="btn-group">
                <a href="{{ route('services.browse') }}" class="btn-primary">
                    {{ __('home.TRAIN_MODELS_NOW', [], $currentLang) }}
                    @if($direction === 'rtl')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                    @endif
                </a>
                <a href="{{ route('register.company', ['type' => 'supplier']) }}" class="btn-outline">
                    {{ __('home.INVEST_TEAM_TIME', [], $currentLang) }}
                    @if($direction === 'rtl')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 7L7 17"/><path d="M17 17H7V7"/></svg>
                    @endif
                </a>
            </div>
        </div>

        {{-- ── Saudi Themed Graphic (left in RTL) ── --}}
        <div class="hero-visual flex justify-center items-center">
            <img src="{{ asset('images/saudi_hero.png') }}" alt="Saudi Technology" class="w-full max-w-[450px] object-contain opacity-85 hover:opacity-100 transition-all duration-500 filter drop-shadow-[0_0_35px_rgba(13,148,136,0.22)]">
        </div>

    </div>
</section>

{{-- ── Horizontal Stats Row Section ── --}}
<section class="py-10 border-b transition-colors duration-300" style="background-color: var(--bg-why); border-color: var(--bg-border);">
    <div class="container mx-auto px-6 max-w-[1200px]">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Stat 1 --}}
            <div class="bg-slate-100 dark:bg-gray-900/40 border border-slate-200 dark:border-white/5 rounded-2xl p-6 text-center hover:border-brand-green/25 hover:bg-slate-200 dark:hover:bg-gray-900/60 transition-all duration-300 group">
                <i class="fa-solid fa-users text-2xl text-brand-green mb-3 block group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-2">{{ __('home.STAT_1_NUMBER', [], $currentLang) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('home.STAT_1_TEXT', [], $currentLang) }}</div>
            </div>
            {{-- Stat 2 --}}
            <div class="bg-slate-100 dark:bg-gray-900/40 border border-slate-200 dark:border-white/5 rounded-2xl p-6 text-center hover:border-brand-green/25 hover:bg-slate-200 dark:hover:bg-gray-900/60 transition-all duration-300 group">
                <i class="fa-solid fa-building text-2xl text-brand-green mb-3 block group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-2">{{ __('home.STAT_2_NUMBER', [], $currentLang) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('home.STAT_2_TEXT', [], $currentLang) }}</div>
            </div>
            {{-- Stat 3 --}}
            <div class="bg-slate-100 dark:bg-gray-900/40 border border-slate-200 dark:border-white/5 rounded-2xl p-6 text-center hover:border-brand-green/25 hover:bg-slate-200 dark:hover:bg-gray-900/60 transition-all duration-300 group">
                <i class="fa-solid fa-chart-line text-2xl text-brand-green mb-3 block group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-2">{{ __('home.STAT_3_NUMBER', [], $currentLang) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('home.STAT_3_TEXT', [], $currentLang) }}</div>
            </div>
            {{-- Stat 4 --}}
            <div class="bg-slate-100 dark:bg-gray-900/40 border border-slate-200 dark:border-white/5 rounded-2xl p-6 text-center hover:border-brand-green/25 hover:bg-slate-200 dark:hover:bg-gray-900/60 transition-all duration-300 group">
                <i class="fa-solid fa-file-signature text-2xl text-brand-green mb-3 block group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-2">{{ __('home.STAT_4_NUMBER', [], $currentLang) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('home.STAT_4_TEXT', [], $currentLang) }}</div>
            </div>
        </div>
    </div>
</section>

{{-- Why Radiif Section --}}
<section class="why-section" dir="{{ $direction }}">
    <div class="container mx-auto px-4">
        <h2 class="section-title">{{ __('home.WHY_RADIIF_TITLE', [], $currentLang) }}</h2>
        <p class="section-subtitle">{{ __('home.WHY_RADIIF_SUBTITLE', [], $currentLang) }}</p>

        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                </div>
                <h3>{{ __('home.WHY_CARD_1_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.WHY_CARD_1_DESC', [], $currentLang) }}</p>
            </div>
            <div class="why-card">
                <div class="why-icon icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>{{ __('home.WHY_CARD_2_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.WHY_CARD_2_DESC', [], $currentLang) }}</p>
            </div>
            <div class="why-card">
                <div class="why-icon icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h3>{{ __('home.WHY_CARD_3_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.WHY_CARD_3_DESC', [], $currentLang) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- AI Legal Assistant Packages Section --}}
<section class="ai-packages-section text-white" dir="{{ $direction }}">
    <div class="container mx-auto px-4 max-w-6xl relative z-10">

        {{-- Section Badge & Header --}}
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-xs font-extrabold tracking-wide mb-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                🤖 {{ $currentLang === 'en' ? 'AI Legal Assistant Packages' : 'باقات المساعد القانوني الذكي' }}
            </span>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                {{ $currentLang === 'en' ? 'Exceptional Plans for Legal Professionals' : 'باقات استثنائية لرواد العمل القانوني' }}
            </h2>
            <p class="text-slate-400 text-base md:text-lg leading-relaxed">
                {{ $currentLang === 'en' 
                    ? 'Invest in AI tools to analyze contracts and search Saudi legal precedents in seconds.' 
                    : 'استثمر في أدواتك الذكية، واختصر ساعات البحث في السوابق القضائية والأنظمة السعودية بأعلى دقة وسرعة.' }}
            </p>
        </div>

        {{-- Packages Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch mb-12">
            @if(isset($aiPackages) && count($aiPackages) > 0)
                {{-- Loop from DB packages --}}
                @foreach($aiPackages as $pkg)
                    <div class="pkg-card {{ $pkg->is_popular ? 'popular' : '' }}">
                        @if($pkg->badge_text || $pkg->is_popular)
                            <div class="pkg-badge">
                                {{ $pkg->badge_text ?? ($currentLang === 'en' ? 'Most Popular' : '⭐ الأكثر طلباً') }}
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <h3 class="text-xl font-black text-white">{{ $pkg->name }}</h3>
                                <span class="text-2xl font-black text-emerald-400">{{ $pkg->price_display }}</span>
                            </div>
                            <p class="text-slate-400 text-xs mb-6 leading-relaxed min-h-[36px]">{{ $pkg->description }}</p>

                            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 mb-6 flex items-center justify-between text-xs text-slate-300">
                                <span class="font-bold flex items-center gap-1.5">
                                    <i class="fa-solid fa-bolt text-amber-400"></i>
                                    {{ $currentLang === 'en' ? 'Monthly Limit' : 'الحد الشهري' }}
                                </span>
                                <span class="font-black text-emerald-400">{{ $pkg->query_limit_display }}</span>
                            </div>

                            <ul class="space-y-3 mb-8 text-xs text-slate-300">
                                @if(is_array($pkg->features))
                                    @foreach($pkg->features as $feat)
                                        <li class="flex items-start gap-2.5">
                                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                            <span>{{ $feat }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div>
                            <a href="{{ route('ai.packages') }}" class="w-full py-3.5 px-4 rounded-xl text-center text-sm block transition-all {{ $pkg->is_popular ? 'pkg-btn-emerald' : 'pkg-btn-outline' }}">
                                {{ $pkg->is_free ? ($currentLang === 'en' ? 'Start Free Trial' : 'ابدأ مجاناً') : ($currentLang === 'en' ? 'Subscribe Now' : 'اشترك الآن') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Fallback Default Packages --}}

                {{-- 1. Free Package --}}
                <div class="pkg-card">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="text-xl font-black text-white">الباقة التجريبية</h3>
                            <span class="text-2xl font-black text-emerald-400">مجاناً</span>
                        </div>
                        <p class="text-slate-400 text-xs mb-6 leading-relaxed min-h-[36px]">لتجربة المساعد القانوني الذكي واختبار دقة إجاباته في الأنظمة السعودية</p>

                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 mb-6 flex items-center justify-between text-xs text-slate-300">
                            <span class="font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-amber-400"></i>
                                الحد الشهري
                            </span>
                            <span class="font-black text-emerald-400">20 استعلاماً شهرياً</span>
                        </div>

                        <ul class="space-y-3 mb-8 text-xs text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>الوصول للمساعد القانوني الذكي (النسخة القياسية)</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>الاطلاع والبحث في الأنظمة واللوائح السعودية</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>تحليل الاستفسارات والأسئلة القانونية السريعة</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('ai.packages') }}" class="w-full py-3.5 px-4 rounded-xl text-center text-sm block pkg-btn-outline">
                            ابدأ مجاناً
                        </a>
                    </div>
                </div>

                {{-- 2. Pro Package (Popular) --}}
                <div class="pkg-card popular">
                    <div class="pkg-badge">
                        ⭐ الأكثر طلباً
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="text-xl font-black text-white">باقة المحامي الاحترافية</h3>
                            <div>
                                <span class="text-2xl font-black text-emerald-400">49 ر.س</span>
                                <span class="text-[10px] text-slate-400 block text-left">/ شهرياً</span>
                            </div>
                        </div>
                        <p class="text-slate-400 text-xs mb-6 leading-relaxed min-h-[36px]">المثالية للمحامين والمستشارين للبحث المكثف وصياغة المستندات</p>

                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 mb-6 flex items-center justify-between text-xs text-slate-300">
                            <span class="font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-amber-400"></i>
                                الحد الشهري
                            </span>
                            <span class="font-black text-emerald-400">150 استعلاماً شهرياً</span>
                        </div>

                        <ul class="space-y-3 mb-8 text-xs text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>تحليل وتدقيق العقود والاتفاقيات بالذكاء الاصطناعي</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>البحث المتقدم في السوابق والأحكام القضائية</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>توليد وصياغة المذكرات واللوائح الاعتراضية</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>دعم أولوية وحفظ محادثات البحث القانوني</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('ai.packages') }}" class="w-full py-3.5 px-4 rounded-xl text-center text-sm block pkg-btn-emerald">
                            اشترك الآن
                        </a>
                    </div>
                </div>

                {{-- 3. Enterprise Unlimited Package --}}
                <div class="pkg-card">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="text-xl font-black text-white">الباقة المؤسسية</h3>
                            <div>
                                <span class="text-2xl font-black text-emerald-400">399 ر.س</span>
                                <span class="text-[10px] text-slate-400 block text-left">/ شهرياً</span>
                            </div>
                        </div>
                        <p class="text-slate-400 text-xs mb-6 leading-relaxed min-h-[36px]">شاملة كافة الخصائص بدون قيود لمكاتب المحاماة والشركات الكبرى</p>

                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 mb-6 flex items-center justify-between text-xs text-slate-300">
                            <span class="font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-infinity text-cyan-400"></i>
                                الحد الشهري
                            </span>
                            <span class="font-black text-cyan-400">استعلامات غير محدودة</span>
                        </div>

                        <ul class="space-y-3 mb-8 text-xs text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>جميع ميزات باقة المحامي بدون أي حدود</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>ربط وتكامل API مخصص للمكاتب والشركات</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>تصدير المخرجات والتقارير القانونية بصيغ (PDF / Word)</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                                <span>مدير حساب مخصص ودعم فني على مدار الساعة 24/7</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('ai.packages') }}" class="w-full py-3.5 px-4 rounded-xl text-center text-sm block pkg-btn-outline">
                            ترقية للباقة المؤسسية
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Link --}}
        <div class="text-center">
            <a href="{{ route('ai.packages') }}" class="inline-flex items-center gap-2 text-sm text-emerald-400 hover:text-emerald-300 font-bold transition-all hover:underline">
                <span>{{ $currentLang === 'en' ? 'Explore all packages details & FAQs' : 'عرض كافة تفاصيل الباقات والأسئلة الشائعة' }}</span>
                <i class="fa-solid {{ $direction === 'rtl' ? 'fa-arrow-left' : 'fa-arrow-right' }} text-xs"></i>
            </a>
        </div>

    </div>
</section>

{{-- Services Section --}}
@if(isset($services) && count($services) > 0)
<section class="services-section" dir="{{ $direction }}">
    <div class="services-header">
        <div>
            <h2 class="section-title text-left" style="margin-bottom:0.5rem">{{ __('home.RECENT_SERVICES_TITLE', [], $currentLang) }}</h2>
            <p style="color:#64748b;">{{ __('home.RECENT_SERVICES_SUBTITLE', [], $currentLang) }}</p>
        </div>
        <a href="{{ route('services.browse') }}" class="text-brand-primary font-bold flex items-center gap-2 hover:underline">
            @if($direction === 'rtl')
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            @endif
            {{ __('home.BROWSE_ALL_BTN', [], $currentLang) }}
            @if($direction === 'ltr')
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            @endif
        </a>
    </div>

    <div class="services-grid">
        @foreach($services as $service)
        <div class="expert-card">
            <div class="ec-top">
                <div class="ec-avatar">
                    <div class="eca-circle">
                        @php
                            $names = explode(' ', $service->expert_name);
                            $initials = mb_substr($names[0], 0, 1);
                            if(count($names) > 1) {
                                $initials .= mb_substr(end($names), 0, 1);
                            }
                            echo mb_strtoupper($initials);
                        @endphp
                    </div>
                    <div class="eca-info">
                        <h4>{{ $service->expert_name }}</h4>
                        <div class="flex items-center gap-1 text-[10px] text-amber-500 mt-1">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <span class="text-slate-500 dark:text-slate-400 mr-1 font-bold">5.0</span>
                        </div>
                    </div>
                </div>
                <div class="ec-badge">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ $currentLang === 'en' ? 'Verified B2B' : 'موثق مؤسسياً' }}</span>
                </div>
            </div>
            
            <div class="ec-body">
                <h3>{{ $service->title }}</h3>
                <p>{{ Str::limit($service->description ?? 'مراجعة وتقييم المخرجات البرمجية للنماذج اللغوية الكبيرة', 100) }}</p>
                
                {{-- Dynamic Tech/Validation Tags --}}
                <div class="ec-tags">
                    @if(Str::contains($service->title, ['قانون', 'عقد', 'امتثال', 'Legal', 'Compliance']))
                        <span class="ec-tag">B2B Compliance</span>
                        <span class="ec-tag">Legal AI</span>
                        <span class="ec-tag">RLHF</span>
                    @elseif(Str::contains($service->title, ['تسويق', 'محتوى', 'Marketing', 'SEO']))
                        <span class="ec-tag">Growth</span>
                        <span class="ec-tag">Content QA</span>
                        <span class="ec-tag">HITL</span>
                    @else
                        <span class="ec-tag">AI Training</span>
                        <span class="ec-tag">Data Annotation</span>
                        <span class="ec-tag">RLHF</span>
                    @endif
                </div>
            </div>
            
            <div class="ec-footer">
                <a href="{{ route('services.show', ['id' => $service->service_id]) }}" class="ec-btn">
                    <span>{{ __('home.REQUEST_COMPETENCE', [], $currentLang) }}</span>
                    @if($direction === 'rtl')
                        <i class="fa-solid fa-arrow-left-long text-xs"></i>
                    @else
                        <i class="fa-solid fa-arrow-right-long text-xs"></i>
                    @endif
                </a>
                <div class="ecf-price">
                    <strong>{{ number_format($service->hourly_rate, 2) }}</strong>
                    <span>{{ __('home.CURRENCY_HOUR', [], $currentLang) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- How It Works Section --}}
<section class="steps-section" dir="{{ $direction }}">
    <div class="container mx-auto px-4">
        <h2 class="section-title">{{ __('home.HOW_IT_WORKS_TITLE', [], $currentLang) }}</h2>
        <p class="section-subtitle">{{ __('home.HOW_IT_WORKS_SUBTITLE', [], $currentLang) }}</p>

        <div class="steps-grid">
            <div class="step-item">
                <div class="step-circle">1</div>
                <h3>{{ __('home.STEP_1_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.STEP_1_DESC', [], $currentLang) }}</p>
            </div>
            <div class="step-item">
                <div class="step-circle">2</div>
                <h3>{{ __('home.STEP_2_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.STEP_2_DESC', [], $currentLang) }}</p>
            </div>
            <div class="step-item">
                <div class="step-circle">3</div>
                <h3>{{ __('home.STEP_3_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.STEP_3_DESC', [], $currentLang) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="cta-section" dir="{{ $direction }}">
    <div class="container mx-auto px-4">
        <div class="cta-content">
            <h2 class="cta-title">{{ __('home.CTA_BANNER_TITLE', [], $currentLang) }}</h2>
            <p class="cta-subtitle">{{ __('home.CTA_BANNER_SUBTITLE', [], $currentLang) }}</p>
            <a href="{{ route('register.company', ['type' => 'supplier']) }}" class="btn-green">
                {{ __('home.CTA_BANNER_BTN', [], $currentLang) }}
            </a>
        </div>
    </div>
</section>

@endsection
