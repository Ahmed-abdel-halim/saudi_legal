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

{{-- ── PRODUCT SECTION ───────────────────────────────────── --}}
<section id="product" class="py-20 relative overflow-hidden transition-colors duration-300" style="background: radial-gradient(ellipse at 50% 10%, rgba(13, 148, 136, 0.12) 0%, #0b1120 70%);" dir="{{ $direction }}">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1350px] relative z-10">

        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-14">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-green/10 border border-brand-green/30 text-brand-green text-xs font-black uppercase tracking-wider mb-4">
                <i class="fa-solid fa-layer-group text-sm animate-pulse"></i>
                <span>{{ $currentLang === 'en' ? 'Product & Technology' : 'واجهة المنتج والتقنية | Product' }}</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-5">
                {{ $currentLang === 'en' 
                    ? 'State-of-the-Art Legal Ingestion & Annotation Pipeline' 
                    : 'لوحة تحكم ذكية لسحب، توسيم، وفهرسة الأنظمة والسوابق القضائية' }}
            </h2>
            
            <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                {{ $currentLang === 'en'
                    ? 'Explore how Radiif automates the ingestion of Saudi statutes, court rulings, and commercial regulations with automated OCR, deep NLP tagging, and vector knowledge graphs.'
                    : 'تعرّف على كيفية قيام منصة "رديف" بسحب نصوص الأحكام القضائية والأنظمة السعودية من مصادرها الرسمية وتوسيمها دلالياً بالذكاء الاصطناعي لتقديم أدق استدلال قانوني فوري.' }}
            </p>
        </div>

        {{-- Product Feature Showcase Window --}}
        <div class="rounded-3xl bg-slate-900/90 border border-slate-700/60 shadow-[0_25px_70px_rgba(0,0,0,0.6)] overflow-hidden backdrop-blur-xl mb-12"
             x-data="{ activeView: 'overview' }">

            {{-- Mock Browser / App Titlebar --}}
            <div class="flex flex-wrap items-center justify-between px-6 py-4 bg-slate-950/80 border-b border-slate-800 gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                    <span class="text-xs font-mono text-slate-400 mr-2 ml-2 hidden sm:inline">radiif-control-center.internal/v2/ingestion-pipeline</span>
                </div>

                {{-- Interactive Tabs --}}
                <div class="flex items-center gap-1 sm:gap-2 text-xs">
                    <button @click="activeView = 'overview'" 
                            :class="activeView === 'overview' ? 'bg-brand-green/20 text-brand-green border-brand-green/40' : 'text-slate-400 hover:text-white border-transparent'"
                            class="px-3 py-1.5 rounded-lg border font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-chart-pie text-[11px]"></i>
                        <span>لوحة الاستيعاب</span>
                    </button>
                    <button @click="activeView = 'tagging'" 
                            :class="activeView === 'tagging' ? 'bg-brand-green/20 text-brand-green border-brand-green/40' : 'text-slate-400 hover:text-white border-transparent'"
                            class="px-3 py-1.5 rounded-lg border font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-tags text-[11px]"></i>
                        <span>التوسيم الدلالي</span>
                    </button>
                    <button @click="activeView = 'terminal'" 
                            :class="activeView === 'terminal' ? 'bg-brand-green/20 text-brand-green border-brand-green/40' : 'text-slate-400 hover:text-white border-transparent'"
                            class="px-3 py-1.5 rounded-lg border font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-terminal text-[11px]"></i>
                        <span>سجل السحب المباشر</span>
                    </button>
                </div>
            </div>

            {{-- Main Visual Display --}}
            <div class="relative bg-[#060c17]">

                {{-- View 1: Overview (Generated Dashboard Screenshot with glowing badge) --}}
                <div x-show="activeView === 'overview'" class="relative group">
                    <img src="{{ asset('images/product-dashboard.png') }}" 
                         alt="Radiif Legal AI Ingestion Dashboard" 
                         class="w-full h-auto max-h-[640px] object-cover object-top transition duration-500 group-hover:scale-[1.01]">

                    {{-- Floating Ingestion Pulse Overlay --}}
                    <div class="absolute bottom-6 {{ $direction === 'rtl' ? 'right-6' : 'left-6' }} bg-slate-950/85 backdrop-blur-md p-4 rounded-2xl border border-brand-green/30 shadow-2xl max-w-sm hidden sm:block">
                        <div class="flex items-center gap-3">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-white">سحب وتوسيم مباشر (Live Ingestion)</h4>
                                <p class="text-[11px] text-slate-400">تمت معالجة وتوسيم أكثر من 50,000 سابقة قضائية ونظام</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- View 2: Tagging Inspector Simulation --}}
                <div x-show="activeView === 'tagging'" class="p-6 sm:p-10 min-h-[460px] flex flex-col justify-center" style="display: none;">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                        <div class="space-y-4 text-right">
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 inline-block">
                                محرك التوسيم والتصنيف الدلالي بالـ AI
                            </span>
                            <h3 class="text-2xl font-black text-white">تحليل أركان النزاع وربطها بنصوص الأنظمة</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                يقوم النموذج بفحص نصوص الأحكام، واستخراج: (الوقائع، الدفوع، الأساس النظامي، منطوق الحكم)، وتصنيفها تحت وسوم قانونية محكمة لتسهيل الاسترجاع الفوري.
                            </p>

                            <div class="flex flex-wrap gap-2 pt-2">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">#نظام_المحاكم_التجارية</span>
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/30">#عقد_توريد_ومقاولات</span>
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">#فسخ_لعدم_السداد</span>
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">#قيمة_سابقة_عالية</span>
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-purple-500/15 text-purple-400 border border-purple-500/30">#محكمة_الاستئناف_بالرياض</span>
                            </div>
                        </div>

                        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 font-mono text-xs text-slate-300 text-left space-y-2" dir="ltr">
                            <div class="text-slate-500">// Real-time Tagging Metadata Output</div>
                            <div><span class="text-indigo-400">"document_id"</span>: <span class="text-emerald-400">"KSA-COMM-2026-9481"</span>,</div>
                            <div><span class="text-indigo-400">"primary_domain"</span>: <span class="text-emerald-400">"Commercial Law"</span>,</div>
                            <div><span class="text-indigo-400">"sub_categories"</span>: [<span class="text-amber-300">"Supply Dispute"</span>, <span class="text-amber-300">"Default Payment"</span>],</div>
                            <div><span class="text-indigo-400">"matched_statutes"</span>: [</div>
                            <div class="pl-4 text-teal-300">{"system": "Commercial Courts Law", "article": 19, "confidence": 0.994},</div>
                            <div class="pl-4 text-teal-300">{"system": "Civil Transactions Law", "article": 107, "confidence": 0.982}</div>
                            <div>],</div>
                            <div><span class="text-indigo-400">"precedent_weight"</span>: <span class="text-cyan-400">0.96</span>,</div>
                            <div><span class="text-indigo-400">"status"</span>: <span class="text-emerald-400">"INDEXED_READY"</span></div>
                        </div>
                    </div>
                </div>

                {{-- View 3: Live Ingestion Terminal Simulation --}}
                <div x-show="activeView === 'terminal'" class="p-6 sm:p-10 min-h-[460px] bg-slate-950 font-mono text-xs text-slate-300" dir="ltr" style="display: none;">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4 text-slate-500">
                        <span>LIVE_LOGSTREAM: saudi-legal-crawler-worker-01</span>
                        <span class="text-emerald-400">● 240 req/min (HEALTHY)</span>
                    </div>
                    <div class="space-y-3 leading-relaxed">
                        <p><span class="text-slate-500">[22:24:01]</span> <span class="text-cyan-400">INFO:</span> Connecting to Saudi Official Gazette & MoJ Legal Precedent Registry...</p>
                        <p><span class="text-slate-500">[22:24:04]</span> <span class="text-emerald-400">SUCCESS:</span> Ingested 142 new appellate rulings from Commercial Courts.</p>
                        <p><span class="text-slate-500">[22:24:08]</span> <span class="text-indigo-400">NLP_ENGINE:</span> Extracting statutory citations & applying BERT-Arabic legal embeddings.</p>
                        <p><span class="text-slate-500">[22:24:12]</span> <span class="text-amber-400">QDRANT_VECTOR:</span> Indexed vector payloads to collection <code class="text-teal-300">'saudi_legal_tasks'</code>.</p>
                        <p><span class="text-slate-500">[22:24:15]</span> <span class="text-emerald-400">READY:</span> Hybrid search clusters synced across Azure AI & Qdrant with sub-800ms latency.</p>
                        <p class="text-brand-green animate-pulse">>>> Ready for real-time inference via Web Interface & Developer REST API...</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Feature Highlights Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-brand-green/30 transition">
                <div class="w-10 h-10 rounded-xl bg-brand-green/10 flex items-center justify-center text-brand-green text-lg mb-4">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                </div>
                <h4 class="text-base font-bold text-white mb-2">{{ $currentLang === 'en' ? 'Automated Ingestion' : 'سحب واستيعاب آلي مستمر' }}</h4>
                <p class="text-slate-400 text-xs leading-relaxed">
                    تحديث يومي دوري لكافة التعديلات التشريعية والقرارات الوزارية الصادرة حديثاً لضمان حداثة البيانات.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-brand-green/30 transition">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 text-lg mb-4">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h4 class="text-base font-bold text-white mb-2">{{ $currentLang === 'en' ? 'Citation Graph' : 'ربط شبكي بين المواد والأحكام' }}</h4>
                <p class="text-slate-400 text-xs leading-relaxed">
                    خوارزميات متقدمة تربط كل مادة نظامية بالأحكام القضائية التي طبقتها في أرض الواقع وسوابق الاستئناف.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-brand-green/30 transition">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400 text-lg mb-4">
                    <i class="fa-solid fa-code"></i>
                </div>
                <h4 class="text-base font-bold text-white mb-2">{{ $currentLang === 'en' ? 'Dual-Mode Assistant' : 'مساعد ذكي مزدوج الصياغة' }}</h4>
                <p class="text-slate-400 text-xs leading-relaxed">
                    إمكانية استخراج الإجابات بأسلوب مبسط للأفراد وغير المحامين، أو بأسلوب احترافي قضائي للمحامين والشركات.
                </p>
            </div>
        </div>

        {{-- Call To Action row --}}
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('legal_assistant.public') }}" class="px-8 py-3.5 bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black rounded-xl shadow-green-glow hover:scale-105 transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-robot"></i>
                <span>{{ $currentLang === 'en' ? 'Try AI Assistant Free' : 'تجربة المساعد القانوني مجاناً' }}</span>
            </a>
            <a href="#developer-portal" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-terminal text-cyan-400"></i>
                <span>{{ $currentLang === 'en' ? 'Developer API & Beta Key' : 'بوابة المطورين ومفتاح التجربة (API)' }}</span>
            </a>
        </div>

    </div>
</section>

{{-- ── DEVELOPER PORTAL & API BETA WIDGET SECTION ─────────── --}}
<section id="developer-portal" class="py-24 relative overflow-hidden transition-colors duration-300" style="background: #060b16; border-top: 1px solid rgba(6, 182, 212, 0.15);" dir="{{ $direction }}">
    {{-- Ambient Glow Behind Widget --}}
    <div class="absolute top-1/4 -right-40 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-[1350px] relative z-10">

        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-cyan-950/60 border border-cyan-500/30 text-cyan-300 text-xs font-black uppercase tracking-wider mb-4 shadow-sm">
                <i class="fa-solid fa-code text-xs text-cyan-400"></i>
                <span>{{ $currentLang === 'en' ? 'Developer Portal & API Widget' : 'بوابة المطورين والـ API | ودجت التسجيل' }}</span>
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
            </div>
            
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-5">
                {{ $currentLang === 'en' 
                    ? 'Integrate Saudi Legal Intelligence Directly Into Your Apps' 
                    : 'اربط تطبيقاتك القانونية بمحرك رديف الذكي عبر الـ API' }}
            </h2>
            
            <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                {{ $currentLang === 'en'
                    ? 'Seamless RESTful endpoints for legal question answering, citations extraction, and statutory search. Request instant sandbox access below to start testing within seconds.'
                    : 'واجهات برمجية RESTful متقدمة للاستعلام القانوني، واستخراج أرقام المواد، والمطابقة الدلالية للأحكام. اطلب وصولاً تجريبياً فورياً للـ Sandbox وابدأ التجربة في ثوانٍ.' }}
            </p>
        </div>

        {{-- 2-Column Interactive Developer Widget Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch mb-14"
             x-data="{
                lang: 'curl',
                copied: false,
                form: {
                    name: '',
                    email: '',
                    company: '',
                    organization_type: 'law_firm',
                    use_case: '',
                    expected_volume: '10k_to_100k'
                },
                isSubmitting: false,
                isSubmitted: false,
                sandboxKey: '',
                errorMessage: '',
                copyKey() {
                    navigator.clipboard.writeText(this.sandboxKey);
                    this.copied = true;
                    setTimeout(() => { this.copied = false }, 2500);
                },
                submitApplication() {
                    this.isSubmitting = true;
                    this.errorMessage = '';
                    fetch('{{ route('developers.beta.submit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(({ status, body }) => {
                        this.isSubmitting = false;
                        if (status === 200 && body.success) {
                            this.isSubmitted = true;
                            this.sandboxKey = body.sandbox_key;
                        } else {
                            this.errorMessage = body.message || (body.errors ? Object.values(body.errors).flat().join(' - ') : 'حدث خطأ أثناء إرسال الطلب، يرجى المحاولة ثانية.');
                        }
                    })
                    .catch(err => {
                        this.isSubmitting = false;
                        this.errorMessage = 'تعذر الاتصال بالخادم، يرجى التحقق من الشبكة.';
                    });
                }
             }">

            {{-- Column 1: Live Interactive Code Explorer & API Schema (7 Cols) --}}
            <div class="lg:col-span-7 flex flex-col rounded-3xl bg-slate-900/90 border border-slate-700/60 shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden backdrop-blur-xl">
                {{-- Code Header / Topbar --}}
                <div class="flex flex-wrap items-center justify-between px-6 py-4 bg-slate-950 border-b border-slate-800 gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                        <span class="text-xs font-mono font-bold text-cyan-400 bg-cyan-950/60 border border-cyan-500/20 px-2 py-0.5 rounded ml-2 mr-2">POST</span>
                        <span class="text-xs font-mono text-slate-300">/api/v1/legal/ask</span>
                    </div>

                    {{-- Language Selector Tabs --}}
                    <div class="flex items-center gap-1 text-xs">
                        <button type="button" @click="lang = 'curl'" :class="lang === 'curl' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white border-transparent'" class="px-2.5 py-1 rounded-md border font-mono font-bold transition">cURL</button>
                        <button type="button" @click="lang = 'python'" :class="lang === 'python' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white border-transparent'" class="px-2.5 py-1 rounded-md border font-mono font-bold transition">Python</button>
                        <button type="button" @click="lang = 'nodejs'" :class="lang === 'nodejs' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white border-transparent'" class="px-2.5 py-1 rounded-md border font-mono font-bold transition">Node.js</button>
                        <button type="button" @click="lang = 'php'" :class="lang === 'php' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'text-slate-400 hover:text-white border-transparent'" class="px-2.5 py-1 rounded-md border font-mono font-bold transition">PHP</button>
                    </div>
                </div>

                {{-- Code Box Display --}}
                <div class="p-6 bg-slate-950 font-mono text-xs text-slate-200 overflow-x-auto flex-1 leading-relaxed" dir="ltr">
                    {{-- cURL --}}
                    <pre x-show="lang === 'curl'" class="text-slate-300"><span class="text-cyan-400">curl</span> -X POST https://api.radiif.com/v1/legal/ask \
  -H <span class="text-amber-300">"Authorization: Bearer <span class="text-emerald-400" x-text="sandboxKey || 'radif_test_YOUR_SANDBOX_KEY'"></span>"</span> \
  -H <span class="text-amber-300">"Content-Type: application/json"</span> \
  -d <span class="text-teal-300">'{
    "question": "ما هي شروط إنهاء العقد في فترة التجربة وفق نظام العمل؟",
    "mode": "simplified",
    "include_citations": true
  }'</span></pre>

                    {{-- Python --}}
                    <pre x-show="lang === 'python'" class="text-slate-300" style="display: none;"><span class="text-indigo-400">import</span> requests

url = <span class="text-amber-300">"https://api.radiif.com/v1/legal/ask"</span>
headers = {
    <span class="text-amber-300">"Authorization"</span>: f<span class="text-amber-300">"Bearer {<span class="text-emerald-400" x-text="sandboxKey ? `'` + sandboxKey + `'` : `'radif_test_YOUR_SANDBOX_KEY'`"></span>}"</span>,
    <span class="text-amber-300">"Content-Type"</span>: <span class="text-amber-300">"application/json"</span>
}
payload = {
    <span class="text-amber-300">"question"</span>: <span class="text-amber-300">"ما هي شروط إنهاء العقد في فترة التجربة وفق نظام العمل؟"</span>,
    <span class="text-amber-300">"mode"</span>: <span class="text-amber-300">"simplified"</span>,
    <span class="text-amber-300">"include_citations"</span>: <span class="text-cyan-400">True</span>
}

response = requests.post(url, json=payload, headers=headers)
data = response.json()
print(data[<span class="text-amber-300">"answer"</span>])</pre>

                    {{-- Node.js --}}
                    <pre x-show="lang === 'nodejs'" class="text-slate-300" style="display: none;"><span class="text-indigo-400">const</span> response = <span class="text-indigo-400">await</span> fetch(<span class="text-amber-300">'https://api.radiif.com/v1/legal/ask'</span>, {
  method: <span class="text-amber-300">'POST'</span>,
  headers: {
    <span class="text-amber-300">'Authorization'</span>: <span class="text-amber-300">`Bearer ${<span class="text-emerald-400" x-text="sandboxKey ? `'` + sandboxKey + `'` : `'radif_test_YOUR_SANDBOX_KEY'`"></span>}`</span>,
    <span class="text-amber-300">'Content-Type'</span>: <span class="text-amber-300">'application/json'</span>
  },
  body: JSON.stringify({
    question: <span class="text-amber-300">'ما هي شروط إنهاء العقد في فترة التجربة وفق نظام العمل؟'</span>,
    mode: <span class="text-amber-300">'simplified'</span>,
    include_citations: <span class="text-cyan-400">true</span>
  })
});
<span class="text-indigo-400">const</span> data = <span class="text-indigo-400">await</span> response.json();
console.log(data);</pre>

                    {{-- PHP --}}
                    <pre x-show="lang === 'php'" class="text-slate-300" style="display: none;"><span class="text-cyan-400">$response</span> = Http::withToken(<span class="text-emerald-400" x-text="sandboxKey ? `'` + sandboxKey + `'` : `'radif_test_YOUR_SANDBOX_KEY'`"></span>)
    ->post(<span class="text-amber-300">'https://api.radiif.com/v1/legal/ask'</span>, [
        <span class="text-amber-300">'question'</span> => <span class="text-amber-300">'ما هي شروط إنهاء العقد في فترة التجربة وفق نظام العمل؟'</span>,
        <span class="text-amber-300">'mode'</span> => <span class="text-amber-300">'simplified'</span>,
        <span class="text-amber-300">'include_citations'</span> => <span class="text-cyan-400">true</span>,
    ]);

<span class="text-indigo-400">echo</span> <span class="text-cyan-400">$response</span>->json(<span class="text-amber-300">'answer'</span>);</pre>
                </div>

                {{-- Live Response JSON Simulation Box --}}
                <div class="p-4 bg-slate-950/90 border-t border-slate-800 text-xs font-mono" dir="ltr">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-emerald-400 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span> 200 OK — Response Payload (520ms)</span>
                        <span>JSON</span>
                    </div>
                    <div class="text-slate-400 overflow-x-auto text-[11px] leading-relaxed max-h-36 custom-scrollbar">
                        <span class="text-slate-300">{</span><br>
                        &nbsp;&nbsp;<span class="text-indigo-400">"status"</span>: <span class="text-emerald-400">"success"</span>,<br>
                        &nbsp;&nbsp;<span class="text-indigo-400">"mode"</span>: <span class="text-amber-300">"simplified"</span>,<br>
                        &nbsp;&nbsp;<span class="text-indigo-400">"citations"</span>: [<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;{<span class="text-indigo-400">"statute"</span>: <span class="text-emerald-400">"نظام العمل السعودي"</span>, <span class="text-indigo-400">"article"</span>: <span class="text-cyan-400">53</span>, <span class="text-indigo-400">"confidence"</span>: <span class="text-cyan-400">0.992</span>}<br>
                        &nbsp;&nbsp;],<br>
                        &nbsp;&nbsp;<span class="text-indigo-400">"answer"</span>: <span class="text-slate-300">"وفقاً للمادة 53 من نظام العمل، يجوز لأي من الطرفين إنهاء العقد خلال فترة التجربة ما لم يتضمن العقد نصاً يعطي الحق لأحدهما فقط..."</span><br>
                        <span class="text-slate-300">}</span>
                    </div>
                </div>

                {{-- Footer Link to Full Documentation --}}
                <div class="px-6 py-3.5 bg-slate-900 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-400">{{ $currentLang === 'en' ? 'Explore all endpoints & SDKs:' : 'استكشف كافة نقاط النهاية والمكتبات:' }}</span>
                    <a href="{{ route('developers.index') }}" class="text-xs font-bold text-cyan-400 hover:text-cyan-300 flex items-center gap-1.5 group">
                        <span>{{ $currentLang === 'en' ? 'Open API Documentation' : 'فتح التوثيق الكامل للـ API' }}</span>
                        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition rtl:rotate-0 rotate-180"></i>
                    </a>
                </div>
            </div>

            {{-- Column 2: Interactive Request Beta Access Widget (5 Cols) --}}
            <div class="lg:col-span-5 flex flex-col rounded-3xl bg-gradient-to-b from-slate-900 to-slate-950 border border-cyan-500/30 shadow-[0_20px_50px_rgba(0,0,0,0.5)] p-6 sm:p-8 relative">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                    <div>
                        <span class="text-xs font-black text-cyan-400 uppercase tracking-wider block mb-1">
                            {{ $currentLang === 'en' ? 'Instant Sandbox Access' : 'التسجيل والوصول الفوري' }}
                        </span>
                        <h3 class="text-lg sm:text-xl font-black text-white">
                            {{ $currentLang === 'en' ? 'Request Beta API Key' : 'طلب مفتاح API تجريبي (Beta)' }}
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-lg shrink-0">
                        <i class="fa-solid fa-key"></i>
                    </div>
                </div>

                {{-- State 1: Form (Before submission) --}}
                <form x-show="!isSubmitted" @submit.prevent="submitApplication" class="space-y-4 flex-1 flex flex-col justify-between">
                    <div>
                        <div x-show="errorMessage" class="p-3 mb-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2" style="display: none;">
                            <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        <div class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">
                                    {{ $currentLang === 'en' ? 'Full Name' : 'الاسم الكامل' }} *
                                </label>
                                <input type="text" x-model="form.name" required placeholder="{{ $currentLang === 'en' ? 'e.g. Abdullah Al-Harbi' : 'مثال: عبدالله الحربي' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-cyan-500 focus:outline-none transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">
                                    {{ $currentLang === 'en' ? 'Work Email' : 'البريد الإلكتروني المهني' }} *
                                </label>
                                <input type="email" x-model="form.email" required placeholder="name@company.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-cyan-500 focus:outline-none transition" dir="ltr">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 mb-1">
                                        {{ $currentLang === 'en' ? 'Organization Type' : 'نوع الجهة' }} *
                                    </label>
                                    <select x-model="form.organization_type" required class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-cyan-500 focus:outline-none transition">
                                        <option value="law_firm">مكتب محاماة</option>
                                        <option value="legaltech">شركة LegalTech</option>
                                        <option value="enterprise">شركة / قطاع أعمال</option>
                                        <option value="developer">مطور مستقل</option>
                                        <option value="individual">باحث قانوني</option>
                                        <option value="other">جهة أخرى</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 mb-1">
                                        {{ $currentLang === 'en' ? 'Expected Queries' : 'حجم الاستعلام' }} *
                                    </label>
                                    <select x-model="form.expected_volume" required class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-cyan-500 focus:outline-none transition">
                                        <option value="under_10k">&lt; 10,000 / شهر</option>
                                        <option value="10k_to_100k">10K - 100K / شهر</option>
                                        <option value="over_100k">&gt; 100,000 / شهر</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">
                                    {{ $currentLang === 'en' ? 'Company Name (Optional)' : 'اسم الشركة / المكتب (اختياري)' }}
                                </label>
                                <input type="text" x-model="form.company" placeholder="{{ $currentLang === 'en' ? 'Company or Firm' : 'مثال: شركة الحلول الرقمية' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-cyan-500 focus:outline-none transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">
                                    {{ $currentLang === 'en' ? 'Use Case Description' : 'نبذة عن فكرة المشروع أو التكامل' }} *
                                </label>
                                <textarea x-model="form.use_case" required rows="2" placeholder="{{ $currentLang === 'en' ? 'How do you plan to use Radiif API?' : 'مثال: نود ربط نظام إدارة القضايا للبحث في المواد والأنظمة آلياً' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-cyan-500 focus:outline-none transition"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="isSubmitting" class="w-full py-3.5 px-4 bg-gradient-to-r from-cyan-500 to-teal-400 hover:from-cyan-400 hover:to-teal-300 text-dark-navy font-black rounded-xl shadow-lg shadow-cyan-500/20 active:scale-[0.98] transition flex items-center justify-center gap-2 cursor-pointer text-sm">
                            <span x-show="!isSubmitting"><i class="fa-solid fa-bolt text-xs"></i> {{ $currentLang === 'en' ? 'Generate Instant Sandbox Key' : 'توليد مفتاح تجريبي فوري' }}</span>
                            <span x-show="isSubmitting" style="display: none;"><i class="fa-solid fa-spinner fa-spin"></i> جاري الإنشاء...</span>
                        </button>
                        <p class="text-[11px] text-center text-slate-500 mt-2">
                            🔒 وصول فوري لبيئة الاختبار التجريبية (Sandbox) بدون رسوم أو بطاقة بنكية
                        </p>
                    </div>
                </form>

                {{-- State 2: Success & Generated Key Box --}}
                <div x-show="isSubmitted" style="display: none;" class="flex-1 flex flex-col justify-between text-center py-4">
                    <div>
                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-2xl mx-auto mb-4 animate-bounce">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h4 class="text-xl font-black text-white mb-2">تم إصدار مفتاح الـ API بنجاح!</h4>
                        <p class="text-xs text-slate-400 mb-6">
                            مفتاح الاختبار التجريبي جاهز للاستخدام المباشر في بيئة Sandbox. تم حفظ طلبك وسيتواصل معك فريقنا لترقية الحساب.
                        </p>

                        {{-- Key Display Container --}}
                        <div class="p-4 rounded-2xl bg-slate-950 border border-cyan-500/40 text-left mb-4 shadow-inner" dir="ltr">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 mb-2 font-mono">
                                <span>API_SANDBOX_KEY</span>
                                <span class="text-emerald-400">● ACTIVE</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <code class="font-mono text-xs text-teal-300 font-bold truncate select-all" x-text="sandboxKey"></code>
                                <button type="button" @click="copyKey" class="px-3 py-1.5 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 font-bold text-xs flex items-center gap-1.5 shrink-0 transition">
                                    <i class="fa-regular fa-copy" x-show="!copied"></i>
                                    <i class="fa-solid fa-check text-emerald-400" x-show="copied" style="display: none;"></i>
                                    <span x-text="copied ? 'تم النسخ!' : 'نسخ'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-right text-[11px] text-slate-400 space-y-1">
                            <div class="text-slate-300 font-bold">💡 كيف تبدأ؟</div>
                            <div>انسخ المفتاح واستخدمه في ترويسة الطلب: <code class="text-cyan-400" dir="ltr">Authorization: Bearer [KEY]</code></div>
                        </div>
                    </div>

                    <div class="pt-6 space-y-2">
                        <a href="{{ route('developers.index') }}" class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl border border-slate-700 transition flex items-center justify-center gap-2 text-xs">
                            <i class="fa-solid fa-book-open text-cyan-400"></i>
                            <span>تصفح وثائق الـ API ونماذج الاستجابة الكاملة</span>
                        </a>
                        <button type="button" @click="isSubmitted = false; form.name = ''; form.email = ''; form.use_case = '';" class="text-xs text-slate-500 hover:text-slate-400 underline">
                            تقديم طلب جهة أخرى
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Enterprise & Developer Highlights Row --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 pt-4 border-t border-slate-800/80">
            <div class="text-center p-4 rounded-2xl bg-slate-900/40 border border-slate-800/60">
                <div class="text-2xl font-black text-cyan-400 mb-1">&lt; 800ms</div>
                <div class="text-xs text-slate-400 font-medium">زمن استجابة فائق السرعة</div>
            </div>
            <div class="text-center p-4 rounded-2xl bg-slate-900/40 border border-slate-800/60">
                <div class="text-2xl font-black text-emerald-400 mb-1">99.9%</div>
                <div class="text-xs text-slate-400 font-medium">جاهزية تشغيل مستقرة (SLA)</div>
            </div>
            <div class="text-center p-4 rounded-2xl bg-slate-900/40 border border-slate-800/60">
                <div class="text-2xl font-black text-indigo-400 mb-1">100%</div>
                <div class="text-xs text-slate-400 font-medium">توثيق بمواد الأنظمة والسوابق</div>
            </div>
            <div class="text-center p-4 rounded-2xl bg-slate-900/40 border border-slate-800/60">
                <div class="text-2xl font-black text-amber-400 mb-1">PDPL</div>
                <div class="text-xs text-slate-400 font-medium">توافق مع حماية البيانات السعودية</div>
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
