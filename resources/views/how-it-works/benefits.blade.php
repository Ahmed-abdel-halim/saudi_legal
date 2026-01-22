@extends('layouts.app')

@section('content')
<section class="bg-white py-20">
    <div class="container mx-auto px-4 text-center mb-16">
        <span class="text-blue-600 font-semibold tracking-wide uppercase text-sm">@lang('benefits.NAV_BENEFITS')</span>
        <h1 class="text-4xl font-bold text-gray-900 mt-2 mb-4">@lang('benefits.BENEFITS_TITLE')</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">@lang('benefits.BENEFITS_SUBTITLE')</p>
    </div>

    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Supplier Benefits -->
            <div class="bg-blue-50 rounded-2xl p-8 border border-blue-100">
                <div class="w-14 h-14 bg-blue-600 text-white rounded-lg flex items-center justify-center text-2xl mb-6">
                    <i class="fas fa-building"></i>
                    🏢
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">@lang('benefits.BENEFITS_SUPPLIER_HEAD')</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_SUPPLIER_1')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_SUPPLIER_2')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_SUPPLIER_3')</span>
                    </li>
                </ul>
            </div>

            <!-- Requester Benefits -->
            <div class="bg-green-50 rounded-2xl p-8 border border-green-100">
                <div class="w-14 h-14 bg-green-600 text-white rounded-lg flex items-center justify-center text-2xl mb-6">
                    🤝
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">@lang('benefits.BENEFITS_REQUESTER_HEAD')</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_REQUESTER_1')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_REQUESTER_2')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_REQUESTER_3')</span>
                    </li>
                </ul>
            </div>

             <!-- Expert Benefits -->
             <div class="bg-purple-50 rounded-2xl p-8 border border-purple-100">
                <div class="w-14 h-14 bg-purple-600 text-white rounded-lg flex items-center justify-center text-2xl mb-6">
                   👤
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">@lang('benefits.BENEFITS_EXPERT_HEAD')</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_EXPERT_1')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_EXPERT_2')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-gray-700">@lang('benefits.BENEFITS_EXPERT_3')</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>
@endsection
