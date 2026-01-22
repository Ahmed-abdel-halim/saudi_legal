@extends('layouts.app')

@section('content')
<section class="bg-gray-900 text-white py-24 relative overflow-hidden">
    <div class="container mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">@lang('pricing.PRICING_TITLE')</h1>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto">@lang('pricing.PRICING_SUBTITLE')</p>
    </div>
    <!-- Decorative bg -->
    <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-gradient-to-r from-blue-900 to-purple-900"></div>
</section>

<section class="py-20 bg-gray-50">
    <div class="w-full px-4">
        <div class="flex flex-col lg:flex-row gap-8 justify-center items-stretch mx-auto">
            
            <!-- Basic Plan -->
            <div class="flex-1 bg-white rounded-2xl shadow-lg border border-gray-200 p-8 transform hover:-translate-y-1 transition duration-300">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">@lang('pricing.PLAN_BASIC_TITLE')</h3>
                <p class="text-gray-500 mb-6">@lang('pricing.PLAN_BASIC_DESC')</p>
                
                <div class="flex items-baseline mb-6">
                    <span class="text-5xl font-extrabold text-blue-600 {{ app()->getLocale() == 'ar' ? 'order-last' : '' }}">@lang('pricing.PLAN_BASIC_PRICE')</span>
                    <span class="text-gray-500 ml-2">@lang('pricing.PLAN_BASIC_PERIOD')</span>
                </div>
                
                <a href="{{ route('register.company') }}" class="block w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white text-center font-bold rounded-lg transition mb-8">
                    @lang('pricing.PLAN_BASIC_BTN')
                </a>
                
                <ul class="space-y-4 text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_VERIFIED_EXPERTS')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_SECURE_PAYMENTS')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_CONTRACTS')
                    </li>
                </ul>
            </div>

            <!-- Enterprise Plan -->
            <div class="flex-1 bg-gray-900 text-white rounded-2xl shadow-xl border border-gray-800 p-8 transform hover:-translate-y-1 transition duration-300 relative overflow-hidden">
                <div class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0 rounded-br-lg' : 'right-0 rounded-bl-lg' }} bg-blue-500 text-xs font-bold px-3 py-1">VIP</div>
                <h3 class="text-2xl font-bold mb-2">@lang('pricing.PLAN_ENTERPRISE_TITLE')</h3>
                <p class="text-gray-400 mb-6">@lang('pricing.PLAN_ENTERPRISE_DESC')</p>
                
                <div class="flex items-baseline mb-6">
                    <span class="text-5xl font-extrabold text-white {{ app()->getLocale() == 'ar' ? 'order-last' : '' }}">@lang('pricing.PLAN_ENTERPRISE_PRICE')</span>
                    <span class="text-gray-400 ml-2">@lang('pricing.PLAN_ENTERPRISE_PERIOD')</span>
                </div>
                
                <a href="{{ route('contact') }}" class="block w-full py-3 px-6 bg-white hover:bg-gray-100 text-gray-900 text-center font-bold rounded-lg transition mb-8">
                    @lang('pricing.PLAN_ENTERPRISE_BTN')
                </a>
                
                <ul class="space-y-4 text-gray-300">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_DEDICATED_SUPPORT')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_API_ACCESS')
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.FEATURE_CUSTOM_ONBOARDING')
                    </li>
                     <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @lang('pricing.NAV_SUPPLIERS')
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>
@endsection
