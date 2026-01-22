@extends('layouts.app')

@section('content')
<div class="bg-dark-navy text-white py-16">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold mb-4">{{ __('contact.NAV_CONTACT') }}</h1>
        <p class="text-gray-300">{{ __('contact.CONTACT_SUBTITLE') }}</p>
    </div>
</div>

<div class="py-20 bg-slate-50">
    <div class="container mx-auto px-6 max-w-5xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white rounded-xl shadow-lg overflow-hidden">
            
            <div class="bg-slate-100 p-10">
                <h3 class="text-2xl font-bold text-dark-navy mb-6">{{ __('contact.CONTACT_INFO_TITLE') }}</h3>
                <div class="space-y-6 text-gray-600">
                    <p class="flex items-center">
                        <span class="w-8 h-8 bg-brand-teal/10 text-brand-teal rounded-full flex items-center justify-center rtl:ml-4 ltr:mr-4">✉</span>
                        <a href="mailto:info@radiif.com" class="hover:text-brand-teal transition-colors">info@radiif.com</a>
                    </p>
                    <p class="flex items-center">
                        <span class="w-8 h-8 bg-brand-teal/10 text-brand-teal rounded-full flex items-center justify-center rtl:ml-4 ltr:mr-4">📞</span>
                        <a href="tel:00966540506796" class="text-brand-teal font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-brand-teal/40 rounded-sm">00966540506796</a>
                    </p>
                    <div class="flex items-start">
                        <span class="w-8 h-8 bg-brand-teal/10 text-brand-teal rounded-full flex items-center justify-center rtl:ml-4 ltr:mr-4 mt-1">📍</span>
                        <div>
                            <p class="font-semibold mb-1">Riyadh Office:</p>
                            <p class="mb-4">{{ __('contact.CONTACT_ADDRESS') }}</p>
                            
                            <p class="font-semibold mb-1">London Office (HQ):</p>
                            <p>{{ __('contact.CONTACT_ADDRESS_2') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-10">
                    <h4 class="font-bold text-dark-navy mb-4">{{ __('contact.FOLLOW_US') }}</h4>
                    <div class="flex space-x-4 rtl:space-x-reverse">
                        <a href="#" class="text-gray-400 hover:text-brand-teal transition-colors" aria-label="Twitter">
                             <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-brand-teal transition-colors" aria-label="LinkedIn">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-10">
                @if (session('success'))
                    <div class="bg-green-100 text-green-700 p-4 rounded mb-6">{{ session('success') }}</div>
                @endif
                
                {{-- Placeholder route for now --}}
                <form action="{{ route('contact.send') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.AUTH_FULL_NAME') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-brand-teal transition-all @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.AUTH_EMAIL_LABEL') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-brand-teal transition-all @error('email') border-red-500 @enderror" required>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.CONTACT_SUBJECT') }}</label>
                        <select name="subject" id="subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-brand-teal transition-all">
                            <option value="General Inquiry">{{ __('contact.CONTACT_SUBJECT_GENERAL') }}</option>
                            <option value="Technical Support">{{ __('contact.CONTACT_SUBJECT_TECH') }}</option>
                            <option value="Sales/Partnership">{{ __('contact.CONTACT_SUBJECT_SALES') }}</option>
                            <option value="Billing Issue">{{ __('contact.CONTACT_SUBJECT_BILLING') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.CONTACT_MESSAGE') }}</label>
                        <textarea name="message" id="message" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-brand-teal transition-all @error('message') border-red-500 @enderror" required>{{ old('message') }}</textarea>
                         @error('message')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-brand-magenta text-white py-3 px-6 rounded-lg font-semibold hover:bg-opacity-90 transition-all shadow-md hover:shadow-lg">
                        {{ __('contact.BTN_SEND') }}
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
