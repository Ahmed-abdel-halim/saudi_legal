@extends('layouts.app')

@section('content')
<div class="bg-white py-20 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-brand-teal to-dark-navy"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl md:text-6xl font-extrabold text-dark-navy mb-6">
            {{ __('about.ABOUT_TITLE') }}
        </h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            {{ __('about.ABOUT_SUBTITLE') }}
        </p>
    </div>
</div>

<div class="py-16 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="relative">
                <div class="absolute -inset-4 bg-brand-teal/20 rounded-xl transform rotate-3"></div>
                <img src="{{ asset('assets/images/team.jpg') }}" 
                     onerror="this.src='https://placehold.co/600x400/1E293B/5FD3D3?text=Team+Work'"
                     class="relative rounded-xl shadow-lg w-full object-cover h-96"
                     alt="Our Team">
            </div>

            <div>
                <h2 class="text-3xl font-bold text-dark-navy mb-6">
                    {{ __('about.OUR_VISION') }}
                </h2>
                <p class="text-gray-600 leading-relaxed mb-6 text-lg">
                    {{ __('about.VISION_DESC') }}
                </p>

                <h3 class="text-xl font-bold text-dark-navy mb-4">
                    {{ __('about.OUR_VALUES') }}
                </h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        <span class="mx-3 text-gray-700">
                            <strong>Corporate Verification:</strong> {{ __('about.VALUE_1') }}
                        </span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </span>
                        <span class="mx-3 text-gray-700">
                            <strong>Legal Compliance:</strong> {{ __('about.VALUE_2') }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="py-20 bg-white border-t border-gray-100">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold text-dark-navy mb-4">
            {{ __('about.LEGAL_STRUCTURE_TITLE') }}
        </h2>
        <p class="text-gray-500 max-w-2xl mx-auto mb-12">
            {{__('about.COMPANY_OWNERSHIP')}}
        </p>

        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">

                <div class="p-8 text-left bg-dark-navy text-white relative">
                    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>

                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <img src="{{ asset('assets/images/logo.png') }}"
                                 alt="Radiif Ltd Logo"
                                 class="w-16 h-16 rounded-full object-cover border-2 border-white/20">
                            <div class="ml-4">
                                <h3 class="text-xl font-bold">{{__('about.COMPANY_NAME')}}</h3>
                                <p class="text-brand-teal text-sm">{{__('about.PARENT_COMPANY')}}</p>
                            </div>
                        </div>

                        <div class="space-y-4 text-gray-300 text-sm">
                            <p>
                                <span class="block text-gray-500 text-xs uppercase tracking-wider mb-1">{{__('about.REGISTRATION')}}</span>
                                {{__('about.REGISTERED_LOCATION')}}
                            </p>
                            <p>
                                <span class="block text-gray-500 text-xs uppercase tracking-wider mb-1">{{__('about.COMPANY_NUMBER')}}</span>
                                <span>{{ __('about.COMPANY_NUMBER_STATUS') }}</span>
                            </p>
                            <p>
                                <span class="block text-gray-500 text-xs uppercase tracking-wider mb-1">{{__('about.HEADQUARTERS')}}</span>
                                {{__('about.HEADQUARTERS_ADDRESS')}}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-8 flex flex-col justify-center text-left bg-gray-50">
                    <blockquote class="text-gray-600 italic mb-8 text-lg">
                        {{__('about.FOUNDER_QUOTE')}}
                    </blockquote>

                    <div class="flex items-center mt-auto">
                        <img src="{{ asset('assets/images/founder.jpg') }}"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Mohamed+Abdulhamid&background=0D8ABC&color=fff&size=200';"
                             class="w-36 h-36 rounded-full object-cover border-4 border-white shadow-md ring-1 ring-gray-200"
                             alt="Mohamed Abdulhamid">

                        <div class="ml-6">
                            <p class="text-dark-navy font-bold text-xl">{{__('about.FOUNDER_NAME')}}</p>
                            <p class="text-gray-500 text-sm mb-3">{{__('about.FOUNDER_TITLE')}}</p>

                            <a href="https://www.linkedin.com/in/medo79/" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-semibold transition-colors bg-blue-50 px-4 py-2 rounded-full hover:bg-blue-100">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                LinkedIn Profile
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
