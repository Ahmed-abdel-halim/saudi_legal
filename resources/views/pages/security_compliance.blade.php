@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-slate-900 text-white pt-20 pb-16">
    {{-- Glow background --}}
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-teal/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-brand-cyan/20 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 max-w-7xl relative z-10 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand-teal/30 bg-brand-teal/10 text-brand-teal text-xs font-semibold mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            Secure & Compliant
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-none mb-6">
            {{ __('pages.SEC_TITLE') }}
        </h1>
        <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
            {{ __('pages.SEC_SUBTITLE') }}
        </p>
    </div>
</div>

<div class="bg-slate-50 py-20">
    <div class="container mx-auto px-6 max-w-7xl">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark-navy mb-4">{{ __('pages.SEC_TRUST_INDICATORS') }}</h2>
            <div class="w-16 h-1 bg-brand-teal mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            {{-- PDPL Compliance Card --}}
            <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-brand-teal flex items-center justify-center mb-6 group-hover:bg-brand-teal group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.SEC_PDPL_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    {{ __('pages.SEC_PDPL_DESC') }}
                </p>
                <div class="mt-auto text-xs font-bold text-brand-teal uppercase tracking-wider">Local SA Data Residency</div>
            </div>

            {{-- B2B NDAs Card --}}
            <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-brand-primary flex items-center justify-center mb-6 group-hover:bg-brand-primary group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.SEC_NDA_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    {{ __('pages.SEC_NDA_DESC') }}
                </p>
                <div class="mt-auto text-xs font-bold text-brand-primary uppercase tracking-wider">Corporate Accountability</div>
            </div>

            {{-- Data Encryption Card --}}
            <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-brand-secondary flex items-center justify-center mb-6 group-hover:bg-brand-secondary group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.SEC_ENCRYPTION_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    {{ __('pages.SEC_ENCRYPTION_DESC') }}
                </p>
                <div class="mt-auto text-xs font-bold text-brand-secondary uppercase tracking-wider">AES-256 & TLS 1.3</div>
            </div>

            {{-- Audit Logs Card --}}
            <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-brand-cyan flex items-center justify-center mb-6 group-hover:bg-brand-cyan group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.SEC_AUDIT_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    {{ __('pages.SEC_AUDIT_DESC') }}
                </p>
                <div class="mt-auto text-xs font-bold text-brand-cyan uppercase tracking-wider">Audit Trail (RBAC)</div>
            </div>

            {{-- Verified Professional Staff Card --}}
            <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.SEC_VERIFIED_STAFF_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    {{ __('pages.SEC_VERIFIED_STAFF_DESC') }}
                </p>
                <div class="mt-auto text-xs font-bold text-emerald-600 uppercase tracking-wider">Saudi GOSI Verified</div>
            </div>

        </div>

        {{-- Audit section --}}
        <div class="mt-20 bg-slate-900 text-white rounded-3xl p-8 lg:p-12 relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-brand-primary/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                <div>
                    <h3 class="text-2xl lg:text-3xl font-bold mb-4">Looking for our custom security setup?</h3>
                    <p class="text-gray-300 text-sm lg:text-base max-w-2xl">
                        Request our comprehensive B2B compliance document mapping Radiif processes to National Cybersecurity Authority (NCA) frameworks and enterprise-specific data policies.
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('contact') }}" class="inline-block bg-brand-teal text-white hover:bg-opacity-90 transition-all font-bold px-8 py-4 rounded-full shadow-lg">
                        {{ __('pages.CONTACT_SALES') }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
