@extends('layouts.app')

@section('content')
<div class="bg-dark-navy text-white py-16 border-b border-white/5 relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-black mb-4 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">{{ __('contact.NAV_CONTACT') }}</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">{{ __('contact.CONTACT_SUBTITLE') }}</p>
    </div>
</div>

<div class="py-20 bg-dark-navy relative">
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 max-w-5xl relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 bg-slate-900/40 rounded-3xl overflow-hidden border border-white/5 shadow-2xl backdrop-blur-md">
            
            <div class="bg-slate-900/60 p-10 flex flex-col justify-between border-r border-white/5 rtl:border-l rtl:border-r-0">
                <div>
                    <h3 class="text-2xl font-black text-white mb-8">{{ __('contact.CONTACT_INFO_TITLE') }}</h3>
                    <div class="space-y-6 text-slate-300">
                        <p class="flex items-center">
                            <span class="w-8 h-8 bg-brand-green/10 text-brand-green rounded-full flex items-center justify-center rtl:ml-4 ltr:mr-4 shrink-0 shadow-sm shadow-brand-green/5">✉</span>
                            <a href="mailto:info@radiif.com" class="hover:text-brand-green transition-colors text-sm md:text-base">info@radiif.com</a>
                        </p>
                        <p class="flex items-center">
                            <span class="w-8 h-8 bg-brand-green/10 text-brand-green rounded-full flex items-center justify-center rtl:ml-4 ltr:mr-4 shrink-0 shadow-sm shadow-brand-green/5">📞</span>
                            <a href="tel:00966540506796" class="text-brand-green font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-brand-green/40 rounded-sm text-sm md:text-base">00966540506796</a>
                        </p>
                        <div class="flex items-start">
                            <span class="w-8 h-8 bg-brand-green/10 text-brand-green rounded-full flex items-center justify-center rtl:ml-4 ltr:mr-4 shrink-0 mt-1 shadow-sm shadow-brand-green/5">📍</span>
                            <div class="text-sm leading-relaxed">
                                <p class="font-bold text-white mb-1">Riyadh Office:</p>
                                <p class="mb-4 text-slate-400">{{ __('contact.CONTACT_ADDRESS') }}</p>
                                
                                <p class="font-bold text-white mb-1">London Office (HQ):</p>
                                <p class="text-slate-400">{{ __('contact.CONTACT_ADDRESS_2') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-10 pt-6 border-t border-white/5">
                    <h4 class="font-bold text-white mb-4">{{ __('contact.FOLLOW_US') }}</h4>
                    <div class="flex space-x-4 rtl:space-x-reverse">
                        <a href="#" class="text-slate-400 hover:text-brand-green transition-colors" aria-label="Twitter">
                             <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>
                        </a>
                        <a href="#" class="text-slate-400 hover:text-brand-green transition-colors" aria-label="LinkedIn">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-10 bg-slate-950/40 relative z-10 flex flex-col justify-center">
                @if (session('success'))
                    <div class="bg-brand-green/10 text-brand-green border border-brand-green/20 p-4 rounded-xl mb-6 text-sm font-semibold">{{ session('success') }}</div>
                @endif
                
                {{-- Placeholder route for now --}}
                <form action="{{ route('contact.send') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('contact.AUTH_FULL_NAME') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full px-4 py-3 bg-slate-900/60 border border-white/5 rounded-lg focus:ring-2 focus:ring-brand-green/30 focus:border-brand-green text-white transition-all @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('contact.AUTH_EMAIL_LABEL') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-3 bg-slate-900/60 border border-white/5 rounded-lg focus:ring-2 focus:ring-brand-green/30 focus:border-brand-green text-white transition-all @error('email') border-red-500 @enderror" required>
                        @error('email')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('contact.CONTACT_SUBJECT') }}</label>
                        <select name="subject" id="subject" class="w-full px-4 py-3 bg-slate-900/60 border border-white/5 rounded-lg focus:ring-2 focus:ring-brand-green/30 focus:border-brand-green text-white transition-all">
                            <option value="General Inquiry" class="bg-slate-900">{{ __('contact.CONTACT_SUBJECT_GENERAL') }}</option>
                            <option value="Technical Support" class="bg-slate-900">{{ __('contact.CONTACT_SUBJECT_TECH') }}</option>
                            <option value="Sales/Partnership" class="bg-slate-900">{{ __('contact.CONTACT_SUBJECT_SALES') }}</option>
                            <option value="Billing Issue" class="bg-slate-900">{{ __('contact.CONTACT_SUBJECT_BILLING') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('contact.CONTACT_MESSAGE') }}</label>
                        <textarea name="message" id="message" rows="4" class="w-full px-4 py-3 bg-slate-900/60 border border-white/5 rounded-lg focus:ring-2 focus:ring-brand-green/30 focus:border-brand-green text-white transition-all @error('message') border-red-500 @enderror" required>{{ old('message') }}</textarea>
                         @error('message')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black py-3.5 px-6 rounded-xl hover:scale-[1.01] active:scale-[0.99] transition-all shadow-md shadow-brand-green/10">
                        {{ __('contact.BTN_SEND') }}
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
