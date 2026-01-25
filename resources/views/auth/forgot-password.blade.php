@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo/Header -->
        <div class="text-center">
            <h2 class="text-4xl font-extrabold text-slate-800 mb-2">
                {{ __('auth.FORGOT_PASSWORD_TITLE', [], $currentLang) }}
            </h2>
            <p class="text-slate-600">
                {{ __('auth.FORGOT_PASSWORD_DESC', [], $currentLang) }}
            </p>
        </div>

        <!-- Success Message -->
        @if(session('status'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-center">
            {{ session('status') }}
        </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Forgot Password Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">
                        {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 {{ $direction === 'rtl' ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            value="{{ old('email') }}"
                            class="block w-full {{ $direction === 'rtl' ? 'pr-10 text-right' : 'pl-10' }} py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                            placeholder="{{ __('auth.AUTH_EMAIL_PLACEHOLDER', [], $currentLang) }}"
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    {{ __('auth.BTN_SEND_RESET_LINK', [], $currentLang) }}
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold transition">
                    ← {{ __('auth.BACK_TO_LOGIN', [], $currentLang) }}
                </a>
            </div>
        </div>

        <!-- Additional Help -->
        <div class="text-center">
            <p class="text-sm text-slate-600">
                {{ __('auth.NEED_HELP', [], $currentLang) }}
                <a href="{{ route('contact') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                    {{ __('auth.CONTACT_SUPPORT', [], $currentLang) }}
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
