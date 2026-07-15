@extends('layouts.app')

@section('content')
<section class="bg-dark-navy text-white py-24 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-green/10 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="container mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-black mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">@lang('pricing.PRICING_TITLE')</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto">@lang('pricing.PRICING_SUBTITLE')</p>
    </div>
</section>

<section class="py-20 bg-dark-navy">
    <div class="container mx-auto max-w-5xl px-4">
        <div class="flex flex-col lg:flex-row gap-8 justify-center items-stretch">
            
            <!-- Basic Plan -->
            <div class="flex-1 expert-card p-8 hover:-translate-y-1 transition duration-300 flex flex-col">
                <h3 class="text-2xl font-black text-white mb-2">@lang('pricing.PLAN_BASIC_TITLE')</h3>
                <p class="text-slate-400 text-sm mb-6 leading-relaxed">@lang('pricing.PLAN_BASIC_DESC')</p>
                
                <div class="flex items-baseline mb-6">
                    <span class="text-5xl font-black text-brand-green {{ app()->getLocale() == 'ar' ? 'order-last' : '' }}">@lang('pricing.PLAN_BASIC_PRICE')</span>
                    <span class="text-slate-500 text-sm ml-2">@lang('pricing.PLAN_BASIC_PERIOD')</span>
                </div>
                
                <a href="{{ route('register.company') }}" class="block w-full py-3.5 px-6 bg-slate-900/60 hover:bg-slate-900 text-white hover:text-brand-green border border-white/5 hover:border-brand-green text-center font-bold rounded-xl transition duration-300 mb-8">
                    @lang('pricing.PLAN_BASIC_BTN')
                </a>
                
                <ul class="space-y-4 text-slate-400 text-sm mt-auto">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_VERIFIED_EXPERTS')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_SECURE_PAYMENTS')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_CONTRACTS')
                    </li>
                </ul>
            </div>

            <!-- Enterprise Plan -->
            <div class="flex-1 expert-card p-8 hover:-translate-y-1 transition duration-300 relative overflow-hidden flex flex-col ring-2 ring-brand-green/30 shadow-green-glow">
                <div class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0 rounded-br-lg' : 'right-0 rounded-bl-lg' }} bg-brand-green text-dark-navy text-xs font-black px-3.5 py-1">VIP</div>
                <h3 class="text-2xl font-black text-white mb-2">@lang('pricing.PLAN_ENTERPRISE_TITLE')</h3>
                <p class="text-slate-400 text-sm mb-6 leading-relaxed">@lang('pricing.PLAN_ENTERPRISE_DESC')</p>
                
                <div class="flex items-baseline mb-6">
                    <span class="text-5xl font-black text-white {{ app()->getLocale() == 'ar' ? 'order-last' : '' }}">@lang('pricing.PLAN_ENTERPRISE_PRICE')</span>
                    <span class="text-slate-400 text-sm ml-2">@lang('pricing.PLAN_ENTERPRISE_PERIOD')</span>
                </div>
                
                <a href="{{ route('contact') }}" class="block w-full py-3.5 px-6 bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy text-center font-black rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 mb-8 shadow-md shadow-brand-green/20">
                    @lang('pricing.PLAN_ENTERPRISE_BTN')
                </a>
                
                <ul class="space-y-4 text-slate-300 text-sm mt-auto">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_DEDICATED_SUPPORT')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_API_ACCESS')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_CUSTOM_ONBOARDING')
                    </li>
                     <li class="flex items-center">
                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.NAV_SUPPLIERS')
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>
@endsection
