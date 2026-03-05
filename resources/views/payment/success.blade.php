@extends('layouts.app')

@section('title', __('Payment Successful'))

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-10px) rotate(3deg); }
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(2); opacity: 0; }
    }
    @keyframes check-draw {
        0% { stroke-dashoffset: 100; }
        100% { stroke-dashoffset: 0; }
    }
    .float-icon { animation: float 4s ease-in-out infinite; }
    .pulse-ring {
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    .check-path {
        stroke-dasharray: 100;
        stroke-dashoffset: 100;
        animation: check-draw 0.8s ease forwards 0.4s;
    }
    .step-done { @apply bg-gradient-to-br from-brand-primary to-brand-secondary text-white shadow-glow; }
</style>
@endpush

@section('content')
@php $direction = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-purple-50/20 py-16 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- ── Hero Card ───────────────────────────────────────────────── --}}
        <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden border border-indigo-100">

            {{-- Gradient top strip --}}
            <div class="h-2 w-full bg-gradient-to-r from-brand-primary via-brand-secondary to-brand-magenta"></div>

            {{-- Background decorative blobs --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-brand-primary/5 to-brand-secondary/10 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-emerald-400/5 to-teal-400/10 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

            <div class="relative px-8 pt-12 pb-10 text-center">

                {{-- Animated success icon --}}
                <div class="relative inline-flex items-center justify-center mb-8">
                    {{-- Pulse rings --}}
                    <span class="absolute inline-flex rounded-full bg-emerald-400/20 w-32 h-32 pulse-ring"></span>
                    <span class="absolute inline-flex rounded-full bg-emerald-400/10 w-32 h-32" style="animation: pulse-ring 2s cubic-bezier(0.215,0.61,0.355,1) 0.5s infinite;"></span>

                    {{-- Circle --}}
                    <div class="relative w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 shadow-xl flex items-center justify-center float-icon">
                        <svg class="w-12 h-12" viewBox="0 0 50 50" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                            <path class="check-path" d="M12 25 L21 34 L38 17"/>
                        </svg>
                    </div>
                </div>

                {{-- Heading --}}
                <h1 class="text-3xl md:text-4xl font-black text-dark-navy tracking-tight leading-tight mb-3">
                    @if(app()->getLocale() === 'ar')
                        تم الدفع بنجاح! 🎉
                    @else
                        Payment Submitted! 🎉
                    @endif
                </h1>
                <p class="text-slate-500 text-base font-medium leading-relaxed max-w-md mx-auto">
                    @if(app()->getLocale() === 'ar')
                        طلبك قيد المعالجة. سيتم تأكيد الدفع تلقائياً وإشعار الخبير فور اكتمال المعالجة.
                    @else
                        Your payment is being processed. Your order will be confirmed automatically and the expert will be notified.
                    @endif
                </p>
            </div>

            {{-- ── Order Details ─────────────────────────────────────────── --}}
            @if($purchase)
            <div class="mx-8 mb-8 rounded-2xl border border-slate-100 bg-slate-50/70 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-brand-primary/5 to-brand-secondary/5 border-b border-slate-100">
                    <h2 class="text-sm font-black text-slate-600 uppercase tracking-widest">
                        {{ app()->getLocale() === 'ar' ? 'تفاصيل الطلب' : 'Order Details' }}
                    </h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">
                            {{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Order Reference' }}
                        </span>
                        <span class="text-sm font-black text-dark-navy font-mono">#{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">
                            {{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}
                        </span>
                        <span class="text-base font-black text-brand-primary">{{ number_format($purchase->total_price, 2) }} SAR</span>
                    </div>
                    @if($purchase->service)
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">
                            {{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}
                        </span>
                        <span class="text-sm font-bold text-slate-700 max-w-[200px] text-end">{{ $purchase->service->title }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">
                            {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
                        </span>
                        @if($purchase->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1.5 text-xs font-black bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ app()->getLocale() === 'ar' ? 'مؤكد' : 'Confirmed' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-black bg-amber-100 text-amber-700 px-3 py-1 rounded-full border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                {{ app()->getLocale() === 'ar' ? 'قيد التأكيد' : 'Processing' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- ── What Happens Next ─────────────────────────────────────── --}}
            <div class="mx-8 mb-8">
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">
                    {{ app()->getLocale() === 'ar' ? 'ماذا يحدث بعد ذلك؟' : "What happens next?" }}
                </h2>
                <div class="space-y-3">
                    @foreach([
                        ['icon'=>'✅', 'title'=> app()->getLocale()==='ar' ? 'تم إرسال الدفعة' : 'Payment submitted to Stripe', 'done'=>true],
                        ['icon'=>'🔔', 'title'=> app()->getLocale()==='ar' ? 'سيتم إشعار الخبير فور التأكيد' : 'Expert notified on confirmation', 'done'=>false],
                        ['icon'=>'🚀', 'title'=> app()->getLocale()==='ar' ? 'تُفعَّل الخدمة تلقائياً' : 'Service activates automatically', 'done'=>false],
                    ] as $step)
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $step['done'] ? 'bg-emerald-50 border border-emerald-100' : 'bg-slate-50 border border-slate-100' }}">
                        <span class="text-xl flex-shrink-0">{{ $step['icon'] }}</span>
                        <span class="text-sm font-bold {{ $step['done'] ? 'text-emerald-700' : 'text-slate-600' }}">{{ $step['title'] }}</span>
                        @if($step['done'])
                        <span class="ms-auto w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── CTA Buttons ───────────────────────────────────────────── --}}
            <div class="px-8 pb-10 flex flex-col sm:flex-row gap-3">
                <a href="{{ Auth::check() && in_array(Auth::user()->role, ['expert','freelancer']) ? route('dashboard.expert') : route('dashboard') }}"
                   class="flex-1 text-center bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-black py-3.5 rounded-xl shadow-lg shadow-brand-primary/20 hover:shadow-xl hover:shadow-brand-primary/30 hover:-translate-y-0.5 transition-all text-sm">
                    {{ app()->getLocale() === 'ar' ? 'الذهاب إلى لوحة التحكم' : 'Go to Dashboard' }}
                </a>
                <a href="{{ route('services.browse') }}"
                   class="flex-1 text-center bg-slate-100 text-slate-700 font-bold py-3.5 rounded-xl hover:bg-slate-200 transition-all text-sm border border-slate-200">
                    {{ app()->getLocale() === 'ar' ? 'تصفح المزيد من الخدمات' : 'Browse More Services' }}
                </a>
            </div>

        </div>

        {{-- Stripe badge --}}
        <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-400 font-medium">
            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z"/></svg>
            {{ app()->getLocale() === 'ar' ? 'مدفوعات آمنة بواسطة' : 'Secured by' }}
            <span class="font-black text-slate-600">Stripe</span>
        </div>

    </div>
</div>
@endsection
