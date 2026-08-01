@extends('layouts.app')

@section('title', 'تم تفعيل اشتراكك - رديف')

@section('content')
<main class="min-h-screen flex items-center justify-center py-20" dir="rtl"
      style="background: radial-gradient(ellipse at 50% 0%, rgba(16,185,129,0.12) 0%, transparent 60%), #0b1120;">

    <div class="text-center max-w-lg mx-auto px-6">
        {{-- Success Icon --}}
        <div class="w-24 h-24 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/20">
            <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-black text-white mb-3">🎉 تم تفعيل اشتراكك!</h1>

        @if($subscription && $subscription->package)
            <p class="text-slate-300 text-base mb-2">
                أنت الآن مشترك في <span class="text-emerald-400 font-bold">{{ $subscription->package->name }}</span>
            </p>
            <p class="text-slate-500 text-sm mb-8">
                @if($subscription->package->is_unlimited)
                    يمكنك الآن استخدام المساعد الذكي بشكل غير محدود.
                @else
                    لديك <span class="text-white font-bold">{{ $subscription->package->query_limit }}</span> استعلاماً لاستخدامها مع المساعد الذكي.
                @endif
            </p>
        @else
            <p class="text-slate-300 text-base mb-8">جاري تفعيل اشتراكك... سيتم الإشعار فور اكتمال التحقق من Stripe.</p>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('legal_assistant.public') }}"
               class="px-8 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-white font-black text-base shadow-lg shadow-emerald-500/30 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                ابدأ الاستخدام الآن
            </a>
            <a href="{{ route('ai.packages') }}"
               class="px-6 py-3 rounded-2xl border border-slate-600 text-slate-300 hover:bg-slate-700/50 font-semibold text-sm transition">
                عرض الباقات
            </a>
        </div>

        <p class="text-slate-500 text-xs mt-8 flex items-center justify-center gap-1">
            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            تمت عملية الدفع بأمان عبر Stripe
        </p>
    </div>
</main>
@endsection
