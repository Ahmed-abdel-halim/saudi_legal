@extends('layouts.app')

@section('title', 'باقات المساعد القانوني الذكي - رديف')
@section('meta_description', 'استثمر في أدواتك الذكية واختصر ساعات البحث في السوابق القضائية بأقل تكلفة تضمن لك جودة العمل.')

@push('styles')
<style>
    body { background: #0b1120; }

    .pricing-hero {
        background: radial-gradient(ellipse at 50% -20%, rgba(16, 185, 129, 0.15) 0%, transparent 60%),
                    radial-gradient(ellipse at 80% 50%, rgba(79, 70, 229, 0.07) 0%, transparent 50%),
                    #0b1120;
    }

    .package-card {
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.9) 0%, rgba(11, 17, 32, 0.95) 100%);
        border: 1px solid rgba(255,255,255,0.06);
        transition: all 0.3s ease;
    }

    .package-card:hover {
        transform: translateY(-4px);
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 20px 60px -20px rgba(16, 185, 129, 0.2);
    }

    .package-card.popular {
        background: linear-gradient(135deg, #0b2b40 0%, #0a1f35 100%);
        border-color: rgba(16, 185, 129, 0.4);
        box-shadow: 0 0 40px -10px rgba(16, 185, 129, 0.2), inset 0 1px 0 rgba(255,255,255,0.05);
    }

    .package-card.popular:hover {
        box-shadow: 0 25px 60px -15px rgba(16, 185, 129, 0.35);
    }

    .check-item { animation: fadeInUp 0.4s ease both; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .glow-btn {
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
    }
    .glow-btn:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.6);
        transform: translateY(-1px);
    }

    .badge-popular {
        background: linear-gradient(90deg, #10b981, #06b6d4);
        animation: pulse-badge 2s ease-in-out infinite;
    }

    @keyframes pulse-badge {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    }
</style>
@endpush

@section('content')
<main class="pricing-hero min-h-screen pb-20 pt-28" dir="rtl">
    <div class="container mx-auto px-4 max-w-5xl">

        {{-- Early Access Badge --}}
        <div class="text-center mb-4">
            <span class="inline-flex items-center gap-1.5 px-4 py-1 rounded-full border border-emerald-500/30 bg-emerald-500/5 text-emerald-400 text-xs font-bold tracking-widest uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                (EARLY ACCESS) إطلاق البيتا المعلنة
            </span>
        </div>

        {{-- Heading --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-black text-white leading-tight mb-4">
                باقات استثنائية لرواد المهنة القانونية
            </h1>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
                استثمر في أدواتك الذكية، واختصر ساعات البحث في السوابق القضائية<br>
                بأقل تكلفة تضمن لك <span class="text-emerald-400 font-bold">جودة العمل</span>.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl px-5 py-4 text-center font-semibold mb-8 flex items-center justify-center gap-2">
                <i class="fa-solid fa-party-horn text-xl"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Current Subscription Banner --}}
        @if($currentSubscription)
            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl px-6 py-4 mb-8 flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-crown text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-white font-bold">اشتراكك الحالي: {{ $currentSubscription->package->name }}</p>
                        <p class="text-slate-400 text-sm">
                            @if($currentSubscription->package->is_unlimited)
                                استخدام غير محدود
                            @else
                                {{ $currentSubscription->queries_used }} / {{ $currentSubscription->package->query_limit }} استعلام مستخدم
                            @endif
                            @if($currentSubscription->ends_at)
                                — ينتهي {{ $currentSubscription->ends_at->format('Y/m/d') }}
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('legal_assistant.public') }}"
                   class="px-5 py-2 rounded-xl bg-emerald-500 text-white font-bold text-sm glow-btn">
                    <i class="fa-solid fa-robot me-1"></i> الذهاب للمساعد
                </a>
            </div>
        @endif

        {{-- Pricing Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-{{ $packages->count() == 2 ? '2' : ($packages->count() >= 3 ? '3' : '1') }} gap-6 items-stretch">
            @forelse($packages as $package)
                @php
                    $colorMap = [
                        'emerald' => ['border' => 'border-emerald-500/30', 'text' => 'text-emerald-400', 'btn' => 'glow-btn bg-emerald-500 hover:bg-emerald-400', 'check' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10'],
                        'indigo'  => ['border' => 'border-indigo-500/30', 'text' => 'text-indigo-400', 'btn' => 'bg-indigo-500 hover:bg-indigo-400 shadow-indigo-500/30', 'check' => 'text-indigo-400', 'bg' => 'bg-indigo-500/10'],
                        'gold'    => ['border' => 'border-amber-500/30', 'text' => 'text-amber-400', 'btn' => 'bg-amber-500 hover:bg-amber-400 shadow-amber-500/30', 'check' => 'text-amber-400', 'bg' => 'bg-amber-500/10'],
                        'slate'   => ['border' => 'border-slate-500/30', 'text' => 'text-slate-300', 'btn' => 'bg-slate-600 hover:bg-slate-500', 'check' => 'text-slate-300', 'bg' => 'bg-slate-500/10'],
                    ];
                    $c = $colorMap[$package->color_scheme] ?? $colorMap['emerald'];
                @endphp

                <div class="package-card rounded-3xl p-7 flex flex-col relative {{ $package->is_popular ? 'popular' : '' }}">

                    {{-- Popular Badge --}}
                    @if($package->is_popular && $package->badge_text)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                            <span class="badge-popular px-4 py-1 rounded-full text-white text-xs font-black shadow-lg whitespace-nowrap">
                                {{ $package->badge_text }}
                            </span>
                        </div>
                    @endif

                    {{-- Free Badge --}}
                    @if($package->is_free)
                        <div class="absolute top-4 left-5">
                            <span class="bg-blue-500/20 text-blue-400 border border-blue-500/30 px-2.5 py-0.5 rounded-full text-xs font-bold">مجاناً</span>
                        </div>
                    @endif

                    <div class="mb-5">
                        <h2 class="text-white font-black text-xl mb-1">{{ $package->name }}</h2>
                        @if($package->description)
                            <p class="text-slate-400 text-sm leading-relaxed">{{ $package->description }}</p>
                        @endif
                    </div>

                    {{-- Price --}}
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="text-5xl font-black text-white">{{ $package->is_free ? '0' : number_format($package->price, 0) }}</span>
                        <div>
                            <span class="{{ $c['text'] }} font-bold text-lg">ر.س</span>
                            <span class="text-slate-500 text-sm block leading-none">{{ $package->billing_period_label }}</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-3 flex-1 mb-7">
                        @if($package->features)
                            @foreach($package->features as $i => $feature)
                                <li class="check-item flex items-start gap-2.5 text-sm" style="animation-delay: {{ $i * 0.08 }}s">
                                    <i class="fa-solid fa-check {{ $c['check'] }} mt-0.5 flex-shrink-0 text-xs"></i>
                                    <span class="text-slate-300 leading-snug">{{ $feature }}</span>
                                </li>
                            @endforeach
                        @else
                            <li class="check-item flex items-start gap-2.5 text-sm">
                                <i class="fa-solid fa-check {{ $c['check'] }} mt-0.5 flex-shrink-0 text-xs"></i>
                                <span class="text-slate-300">{{ $package->query_limit_display }}</span>
                            </li>
                        @endif
                    </ul>

                    {{-- CTA Button --}}
                    @if($currentSubscription && $currentSubscription->ai_package_id == $package->id)
                        <div class="w-full py-3 rounded-2xl {{ $c['bg'] }} {{ $c['text'] }} font-bold text-center text-sm border {{ $c['border'] }}">
                            <i class="fa-solid fa-circle-check me-1"></i> اشتراكك الحالي
                        </div>
                    @elseif($package->is_free)
                        @auth
                            <a href="{{ route('ai.subscription.checkout', $package) }}"
                               class="w-full py-3 rounded-2xl border border-slate-600 text-slate-300 hover:bg-slate-700/50 font-bold text-center text-sm transition block">
                                ابدأ تجربتك المجانية
                            </a>
                        @else
                            <a href="{{ route('legal_assistant.public') }}"
                               class="w-full py-3 rounded-2xl border border-slate-600 text-slate-300 hover:bg-slate-700/50 font-bold text-center text-sm transition block">
                                ابدأ تجربتك المجانية
                            </a>
                        @endauth
                    @else
                        @auth
                            <a href="{{ route('ai.subscription.checkout', $package) }}"
                               class="w-full py-3 rounded-2xl {{ $c['btn'] }} text-white font-black text-center text-sm transition block shadow-lg">
                                <i class="fa-solid fa-bolt me-1"></i> اشترك وفعّل حسابك الآن
                            </a>
                        @else
                            <a href="{{ route('login') }}?redirect={{ route('ai.packages') }}"
                               class="w-full py-3 rounded-2xl {{ $c['btn'] }} text-white font-black text-center text-sm transition block shadow-lg">
                                <i class="fa-solid fa-bolt me-1"></i> سجّل دخولك واشترك الآن
                            </a>
                        @endauth
                    @endif
                </div>
            @empty
                <div class="col-span-3 text-center py-20 text-slate-500">
                    <i class="fa-solid fa-box-open text-4xl mb-4 block"></i>
                    <p>لا توجد باقات متاحة حالياً. تواصل معنا للمزيد.</p>
                </div>
            @endforelse
        </div>

        {{-- Guarantee Section --}}
        <div class="mt-14 text-center">
            <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl border border-slate-700/50 bg-slate-800/20">
                <i class="fa-solid fa-shield-check text-emerald-400 text-xl"></i>
                <div class="text-right">
                    <p class="text-white font-bold text-sm">ضمان الاسترداد خلال 7 أيام</p>
                    <p class="text-slate-400 text-xs">إذا لم تكن راضياً سنعيد لك المبلغ بالكامل بدون أسئلة.</p>
                </div>
            </div>
        </div>

        {{-- FAQ --}}
        <div class="mt-14 grid grid-cols-1 md:grid-cols-2 gap-5 text-right">
            <div class="bg-slate-800/20 rounded-2xl border border-slate-700/40 p-5">
                <h3 class="text-white font-bold text-sm mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-question-circle text-emerald-400 text-xs"></i>
                    ما هي الاستعلامات المقصودة؟
                </h3>
                <p class="text-slate-400 text-xs leading-relaxed">كل سؤال تطرحه على المساعد الذكي يُحسب استعلاماً واحداً. يشمل ذلك البحث في السوابق القضائية والأنظمة القانونية.</p>
            </div>
            <div class="bg-slate-800/20 rounded-2xl border border-slate-700/40 p-5">
                <h3 class="text-white font-bold text-sm mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-question-circle text-emerald-400 text-xs"></i>
                    هل يمكنني الإلغاء في أي وقت؟
                </h3>
                <p class="text-slate-400 text-xs leading-relaxed">نعم، يمكنك إلغاء اشتراكك في أي وقت. ستبقى مزايا الباقة مفعلة حتى نهاية فترة الاشتراك الحالية.</p>
            </div>
            <div class="bg-slate-800/20 rounded-2xl border border-slate-700/40 p-5">
                <h3 class="text-white font-bold text-sm mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-question-circle text-emerald-400 text-xs"></i>
                    هل الدفع آمن؟
                </h3>
                <p class="text-slate-400 text-xs leading-relaxed">نعم، نستخدم بوابة Stripe الآمنة عالمياً. بياناتك المصرفية محمية بتشفير SSL ولا نحتفظ بها على خوادمنا.</p>
            </div>
            <div class="bg-slate-800/20 rounded-2xl border border-slate-700/40 p-5">
                <h3 class="text-white font-bold text-sm mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-question-circle text-emerald-400 text-xs"></i>
                    هل تدعمون البطاقات السعودية؟
                </h3>
                <p class="text-slate-400 text-xs leading-relaxed">نعم، ندعم جميع البطاقات البنكية السعودية (فيزا، ماستركارد)، وكذلك Apple Pay و Google Pay.</p>
            </div>
        </div>

    </div>
</main>
@endsection
