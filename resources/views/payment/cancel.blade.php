@extends('layouts.app')

@section('title', __('Payment Cancelled'))

@push('styles')
<style>
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%       { transform: translateX(-6px) rotate(-2deg); }
        40%       { transform: translateX(6px) rotate(2deg); }
        60%       { transform: translateX(-4px); }
        80%       { transform: translateX(4px); }
    }
    @keyframes float-slow {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-8px); }
    }
    .shake { animation: shake 0.7s ease 0.3s both; }
    .float-slow { animation: float-slow 5s ease-in-out infinite; }
</style>
@endpush

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-100/40 to-rose-50/20 py-16 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Flash message (e.g. redirected here from a paid order) --}}
        @if(session('info'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold px-5 py-4 rounded-2xl shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('info') }}
        </div>
        @endif

        {{-- ── Hero Card ───────────────────────────────────────────────── --}}
        <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">

            {{-- Gradient top strip --}}
            <div class="h-2 w-full bg-gradient-to-r from-slate-400 via-slate-500 to-slate-600"></div>

            {{-- Background decorative --}}
            <div class="absolute top-0 right-0 w-56 h-56 bg-slate-100/60 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

            <div class="relative px-8 pt-12 pb-10 text-center">

                {{-- Icon --}}
                <div class="relative inline-flex items-center justify-center mb-8">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-slate-500 to-slate-700 shadow-xl flex items-center justify-center float-slow shake">
                        <svg class="w-11 h-11 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>

                {{-- Heading --}}
                <h1 class="text-3xl md:text-4xl font-black text-dark-navy tracking-tight leading-tight mb-3">
                    {{ $isAr ? 'تم إلغاء الدفع' : 'Payment Cancelled' }}
                </h1>
                <p class="text-slate-500 text-base font-medium leading-relaxed max-w-md mx-auto">
                    {{ $isAr
                        ? 'لم يتم خصم أي مبلغ من حسابك. طلبك لا يزال معلقاً — يمكنك المحاولة مرة أخرى في أي وقت.'
                        : 'No charge was made to your account. Your order is still pending — you can retry the payment anytime.' }}
                </p>
            </div>

            {{-- ── Order Details ─────────────────────────────────────────── --}}
            @if($purchase)
            <div class="mx-8 mb-8 rounded-2xl border border-slate-100 bg-slate-50/70 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                    <h2 class="text-xs font-black text-slate-500 uppercase tracking-widest">
                        {{ $isAr ? 'تفاصيل الطلب' : 'Order Details' }}
                    </h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">{{ $isAr ? 'رقم الطلب' : 'Order Reference' }}</span>
                        <span class="text-sm font-black text-dark-navy font-mono">#{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">{{ $isAr ? 'المبلغ المستحق' : 'Amount Due' }}</span>
                        <span class="text-base font-black text-dark-navy">{{ number_format($purchase->total_price, 2) }} SAR</span>
                    </div>
                    @if($purchase->service)
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">{{ $isAr ? 'الخدمة' : 'Service' }}</span>
                        <span class="text-sm font-bold text-slate-700 max-w-[200px] text-end">{{ $purchase->service->title }}</span>
                    </div>
                    @endif
                    @if($purchase->expert)
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">{{ $isAr ? 'الخبير' : 'Expert' }}</span>
                        <span class="text-sm font-bold text-slate-700">{{ $purchase->expert->name }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <span class="text-sm text-slate-500 font-medium">{{ $isAr ? 'الحالة' : 'Status' }}</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-black bg-amber-100 text-amber-700 px-3 py-1 rounded-full border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $isAr ? 'معلق — لم يُدفع' : 'Pending — Unpaid' }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Why This Might Have Happened ─────────────────────────── --}}
            <div class="mx-8 mb-8 p-5 rounded-2xl bg-amber-50 border border-amber-100">
                <div class="flex gap-3">
                    <span class="text-amber-500 mt-0.5 flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-amber-800 mb-1">
                            {{ $isAr ? 'لماذا تم الإلغاء؟' : 'Why was it cancelled?' }}
                        </p>
                        <ul class="text-sm text-amber-700 space-y-1 list-disc list-inside">
                            <li>{{ $isAr ? 'ضغطت على "رجوع" في صفحة الدفع' : 'You clicked "Back" on the payment page' }}</li>
                            <li>{{ $isAr ? 'انتهت مهلة جلسة الدفع' : 'The payment session timed out' }}</li>
                            <li>{{ $isAr ? 'رُفضت البطاقة أو حدث خطأ' : 'Card was declined or an error occurred' }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ── CTA Buttons ───────────────────────────────────────────── --}}
            <div class="px-8 pb-6 flex flex-col sm:flex-row gap-3">
                @if($purchase && $purchase->payment_status !== 'paid')
                <a href="{{ route('payment.checkout', $purchase->id) }}"
                   class="flex-1 text-center bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-black py-3.5 rounded-xl shadow-lg shadow-brand-primary/20 hover:shadow-xl hover:shadow-brand-primary/30 hover:-translate-y-0.5 transition-all text-sm">
                    {{ $isAr ? 'محاولة الدفع مرة أخرى' : 'Try Payment Again' }}
                </a>
                @endif
                <a href="{{ route('services.browse') }}"
                   class="flex-1 text-center bg-slate-100 text-slate-700 font-bold py-3.5 rounded-xl hover:bg-slate-200 transition-all text-sm border border-slate-200">
                    {{ $isAr ? 'تصفح الخدمات' : 'Browse Services' }}
                </a>
            </div>
            @if($purchase && $purchase->payment_status !== 'paid')
            <p class="text-center text-xs text-slate-400 pb-8 px-8">
                {{ $isAr
                    ? '⚠️ ملاحظة: كل محاولة دفع تفتح جلسة Stripe جديدة صالحة لمدة 30 دقيقة.'
                    : '⚠️ Note: Each retry opens a fresh Stripe session valid for 30 minutes.' }}
            </p>
            @endif

        </div>

        {{-- Help text --}}
        <div class="mt-6 text-center text-xs text-slate-400 font-medium">
            {{ $isAr ? 'هل تحتاج مساعدة؟' : 'Need help?' }}
            <a href="{{ route('home') }}" class="font-bold text-brand-primary hover:underline ms-1">
                {{ $isAr ? 'تواصل مع الدعم' : 'Contact Support' }}
            </a>
        </div>

    </div>
</div>
@endsection
