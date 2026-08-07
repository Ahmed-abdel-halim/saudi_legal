@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@section('content')
<div class="min-h-screen pt-24 pb-16 flex items-center justify-center px-4 sm:px-6 lg:px-8" dir="{{ $direction }}">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo/Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary text-white text-2xl font-bold shadow-glow mb-2">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight">
                {{ __('auth.FORGOT_PASSWORD_TITLE', [], $currentLang) }}
            </h2>
            <p class="text-gray-400 text-sm max-w-sm mx-auto leading-relaxed">
                {{ __('auth.FORGOT_PASSWORD_DESC', [], $currentLang) }}
            </p>
        </div>

        <!-- Success Message -->
        @if(session('status'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3.5 rounded-xl text-center text-xs font-bold shadow-green-glow">
            <i class="fa-solid fa-circle-check text-emerald-400 me-1.5"></i> {{ session('status') }}
        </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3.5 rounded-xl text-xs font-bold">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Forgot Password Card -->
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-2xl p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand-primary/10 rounded-full blur-2xl pointer-events-none"></div>

            <form action="{{ route('password.email') }}" method="POST" class="space-y-6 relative z-10">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-300 mb-2">
                        {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 {{ $direction === 'rtl' ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center pointer-events-none text-gray-500">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </div>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            value="{{ old('email') }}"
                            class="block w-full {{ $direction === 'rtl' ? 'pr-10 text-right' : 'pl-10' }} py-3 border border-dark-border rounded-xl text-white bg-dark-navy placeholder-gray-500 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition text-xs font-medium"
                            placeholder="{{ __('auth.AUTH_EMAIL_PLACEHOLDER', [], $currentLang) }}"
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-brand-primary to-brand-secondary hover:from-brand-primary/90 hover:to-brand-secondary/90 text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 shadow-glow hover:scale-[1.02] text-xs flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>{{ __('auth.BTN_SEND_RESET_LINK', [], $currentLang) }}</span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6 pt-4 border-t border-dark-border/60 text-center">
                <a href="{{ route('login') }}" class="text-xs text-brand-primary hover:text-brand-secondary font-bold transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-right rtl:rotate-180 text-[10px]"></i> {{ __('auth.BACK_TO_LOGIN', [], $currentLang) }}
                </a>
            </div>
        </div>

        <!-- Additional Help -->
        <div class="text-center">
            <p class="text-xs text-gray-400">
                {{ __('auth.NEED_HELP', [], $currentLang) }}
                <a href="{{ route('contact') }}" class="text-emerald-400 hover:underline font-bold ms-1">
                    {{ __('auth.CONTACT_SUPPORT', [], $currentLang) }}
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
