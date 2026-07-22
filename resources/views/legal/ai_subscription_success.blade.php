@extends('layouts.app')

@section('title', 'تم تفعيل اشتراكك - رديف')

@section('content')
<main class="min-h-screen flex items-center justify-center py-20" dir="rtl"
      style="background: radial-gradient(ellipse at 50% 0%, rgba(16,185,129,0.12) 0%, transparent 60%), #0b1120;">

    <div class="text-center max-w-lg mx-auto px-6">
        {{-- Success Icon --}}
        <div class="w-24 h-24 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center mx-auto mb-6 animate-bounce-once">
            <i class="fa-solid fa-circle-check text-emerald-400 text-4xl"></i>
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
                <i class="fa-solid fa-robot"></i> ابدأ الاستخدام الآن
            </a>
            <a href="{{ route('ai.packages') }}"
               class="px-6 py-3 rounded-2xl border border-slate-600 text-slate-300 hover:bg-slate-700/50 font-semibold text-sm transition">
                عرض الباقات
            </a>
        </div>

        <p class="text-slate-600 text-xs mt-8">
            <i class="fa-brands fa-stripe text-slate-500 me-1"></i>
            تمت عملية الدفع بأمان عبر Stripe
        </p>
    </div>
</main>
@endsection
