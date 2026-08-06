@extends('layouts.app')

@php
    $currentLang = app()->getLocale();
    $isArabic = $currentLang === 'ar';
    $dir = $isArabic ? 'rtl' : 'ltr';
@endphp

@section('content')
<div class="min-h-screen pt-28 pb-20" dir="{{ $dir }}" lang="{{ $currentLang }}">

    {{-- ══ Hero Header ══ --}}
    <section class="relative overflow-hidden mb-12">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 {{ $isArabic ? 'right-10' : 'left-10' }} w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 {{ $isArabic ? 'left-10' : 'right-10' }} w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 mb-6 shadow-green-glow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                {{ $isArabic ? 'مكاتب معتمدة وموثقة' : 'Verified Law Firms' }}
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                {{ $isArabic ? 'قائمة المكاتب الاستشارية المعتمدة' : 'Accredited Law Offices & Consultants' }}
            </h1>
            <p class="text-gray-300 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed mb-8">
                {{ $isArabic
                    ? 'نخبة من مكاتب المحاماة والاستشارات القانونية المعتمدة في المملكة العربية السعودية لتقديم الحلول والاستشارات المباشرة لقضيتك.'
                    : 'A curated list of accredited law firms and legal consultants in Saudi Arabia providing direct legal solutions for your case.'
                }}
            </p>

            {{-- Trust Badges --}}
            <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-gray-400 border-t border-b border-dark-border py-4 max-w-3xl mx-auto">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isArabic ? 'محامون ترخيص وزارة العدل' : 'MoJ Licensed Lawyers' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isArabic ? 'خبرات تخصصية موثقة' : 'Verified Domain Expertise' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isArabic ? 'استجابة سريعة ومضمونة' : 'Fast Response Guaranteed' }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Offices Grid ══ --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">

            {{-- Office Card 1 --}}
            <div class="bg-dark-card border border-dark-border rounded-2xl p-6 sm:p-8 hover:border-brand-primary/50 transition-all duration-300 shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 {{ $isArabic ? 'left-0' : 'right-0' }} w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-brand-primary/10 transition-all"></div>
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                {{ $isArabic ? 'مكتب معتمد - ترخيص #38291' : 'Accredited Office - License #38291' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-gray-300 border border-slate-700">
                                📍 {{ $isArabic ? 'الرياض' : 'Riyadh' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                ⭐ 4.9 (128 {{ $isArabic ? 'تقييم' : 'reviews' }})
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white group-hover:text-brand-primary transition-colors">
                            {{ $isArabic ? 'مكتب السفير للاستشارات القانونية والمحاماة' : 'Al-Safeer Law Firm & Legal Consultations' }}
                        </h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $isArabic
                                ? 'مكتب رائد متخصص في الأنظمة التجارية السعودية، النزاعات الاستثمارية، صياغة العقود التجارية المعقدة، وحوكمة الشركات.'
                                : 'A leading Saudi law firm specializing in commercial regulations, investment disputes, complex contract drafting, and corporate governance.'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">الأنظمة التجارية</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">قضايا الشركات</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">النزاعات الاستثمارية</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">خبرة +15 سنة</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto flex-shrink-0">
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-green-glow transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ $isArabic ? 'طلب استشارة مع المكتب' : 'Request Consultation' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Office Card 2 --}}
            <div class="bg-dark-card border border-dark-border rounded-2xl p-6 sm:p-8 hover:border-brand-primary/50 transition-all duration-300 shadow-xl relative overflow-hidden group">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                {{ $isArabic ? 'شركة معتمدة - ترخيص #49102' : 'Accredited Company - License #49102' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-gray-300 border border-slate-700">
                                📍 {{ $isArabic ? 'جدة' : 'Jeddah' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                ⭐ 4.8 (96 {{ $isArabic ? 'تقييم' : 'reviews' }})
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white group-hover:text-brand-primary transition-colors">
                            {{ $isArabic ? 'شركة النخبة السعودية للمحاماة والاستشارات القانونية' : 'Saudi Nokhba Law Firm & Legal Consultants' }}
                        </h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $isArabic
                                ? 'خبرة متقدمة في التمثيل القضائي أمام المحاكم العمالية والإدارية، تأسيس اللوائح الداخلية، وقضايا الامتثال التنظيمي.'
                                : 'Advanced expertise in judicial representation before labor and administrative courts, internal regulation setup, and compliance.'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">القضايا العمالية</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">المحاكم الإدارية</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">صياغة اللوائح</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">خبرة +12 سنة</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto flex-shrink-0">
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-green-glow transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ $isArabic ? 'طلب استشارة مع المكتب' : 'Request Consultation' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Office Card 3 --}}
            <div class="bg-dark-card border border-dark-border rounded-2xl p-6 sm:p-8 hover:border-brand-primary/50 transition-all duration-300 shadow-xl relative overflow-hidden group">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                {{ $isArabic ? 'مؤسسة معتمدة - ترخيص #21904' : 'Accredited Firm - License #21904' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-gray-300 border border-slate-700">
                                📍 {{ $isArabic ? 'الدمام' : 'Dammam' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                ⭐ 4.9 (142 {{ $isArabic ? 'تقييم' : 'reviews' }})
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white group-hover:text-brand-primary transition-colors">
                            {{ $isArabic ? 'مؤسسة العدالة الموجزة للاستشارات النظامية' : 'Moojaz Al-Adalah Legal Advisory' }}
                        </h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $isArabic
                                ? 'متخصصون في القضايا العقارية، نزاعات المقاولات والتمويل، والتوثيق الشرعي والنظامي للتصرفات التجارية.'
                                : 'Specialized in real estate disputes, contracting/financing litigation, and legal notary documentation.'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">النزاعات العقارية</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">التوثيق والمقاولات</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">التمويل العقاري</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">خبرة +18 سنة</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto flex-shrink-0">
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-green-glow transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ $isArabic ? 'طلب استشارة مع المكتب' : 'Request Consultation' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Office Card 4 --}}
            <div class="bg-dark-card border border-dark-border rounded-2xl p-6 sm:p-8 hover:border-brand-primary/50 transition-all duration-300 shadow-xl relative overflow-hidden group">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                {{ $isArabic ? 'مكتب معتمد - ترخيص #55129' : 'Accredited Office - License #55129' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-gray-300 border border-slate-700">
                                📍 {{ $isArabic ? 'الرياض' : 'Riyadh' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                ⭐ 4.7 (88 {{ $isArabic ? 'تقييم' : 'reviews' }})
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white group-hover:text-brand-primary transition-colors">
                            {{ $isArabic ? 'مكتب الرازي للمحاماة والتحكيم التجاري' : 'Al-Razi Arbitration & Legal Practice' }}
                        </h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $isArabic
                                ? 'خبرة راسخة في قضايا التحكيم التجاري المحلي والدولي، الملكية الفكرية، وحماية العلامات التجارية والاندماج والاستحواذ.'
                                : 'Solid expertise in commercial arbitration, intellectual property protection, trademarks, and mergers & acquisitions.'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">التحكيم التجاري</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">الملكية الفكرية</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">الاندماج والاستحواذ</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">خبرة +10 سنوات</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto flex-shrink-0">
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-green-glow transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ $isArabic ? 'طلب استشارة مع المكتب' : 'Request Consultation' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Office Card 5 --}}
            <div class="bg-dark-card border border-dark-border rounded-2xl p-6 sm:p-8 hover:border-brand-primary/50 transition-all duration-300 shadow-xl relative overflow-hidden group">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                {{ $isArabic ? 'شركة معتمدة - ترخيص #31092' : 'Accredited Company - License #31092' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-gray-300 border border-slate-700">
                                📍 {{ $isArabic ? 'الخبر' : 'Khobar' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                ⭐ 4.9 (110 {{ $isArabic ? 'تقييم' : 'reviews' }})
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white group-hover:text-brand-primary transition-colors">
                            {{ $isArabic ? 'شركة الحكمة والبيان للمحاماة والاستشارات' : 'Al-Hikmah & Al-Bayan Law Firm' }}
                        </h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $isArabic
                                ? 'قسم متخصص في قسمة التركات المعقدة والأوقاف، صياغة الوصايا والاستحقاقات الشرعية، وتقديم الاستشارات الحوكمية.'
                                : 'Specialized division for complex inheritance distribution, endowments (Waqf), wills, and corporate governance compliance.'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">قضايا التركات</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">تأسيس الأوقاف</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">الامتثال القانوني</span>
                            <span class="text-xs px-2.5 py-1 rounded-md bg-dark-navy text-gray-300 border border-dark-border">خبرة +14 سنة</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto flex-shrink-0">
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-green-glow transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ $isArabic ? 'طلب استشارة مع المكتب' : 'Request Consultation' }}
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom CTA Box --}}
        <div class="mt-12 p-8 rounded-2xl bg-gradient-to-r from-brand-primary/20 via-brand-secondary/15 to-brand-primary/10 border border-brand-primary/30 text-center">
            <h3 class="text-xl font-bold text-white mb-2">
                {{ $isArabic ? 'هل تمثل مكتب محاماة معتمد وتود الانضمام إلى رديف؟' : 'Are you an accredited law firm wanting to join Radiif?' }}
            </h3>
            <p class="text-gray-300 text-sm max-w-xl mx-auto mb-6">
                {{ $isArabic
                    ? 'انضم إلى شبكة رديف للمكاتب والخبراء القانونيين المعتمدين واستقبل طلبات الاستشارات المباشرة من الزوار العملاء.'
                    : 'Join Radiif network of accredited law firms and legal experts to receive direct consultation requests.'
                }}
            </p>
            <a href="{{ route('freelancer.register.form') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-bold px-6 py-3 rounded-xl shadow-glow hover:scale-105 transition-all text-sm">
                {{ $isArabic ? 'تسجيل مكتب استشاري جديد' : 'Register Law Office' }}
            </a>
        </div>
    </section>
</div>
@endsection
