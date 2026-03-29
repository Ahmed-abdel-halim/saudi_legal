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
                {{ __('auth.RESET_PASSWORD_TITLE', [], $currentLang) ?? 'إعادة تعيين كلمة المرور' }}
            </h2>
            <p class="text-slate-600">
                {{ __('auth.RESET_PASSWORD_DESC', [], $currentLang) ?? 'أدخل كلمة المرور الجديدة أدناه' }}
            </p>
        </div>

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

        <!-- Reset Password Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">
                        {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}
                    </label>
                    <div class="relative">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            readonly
                            value="{{ $email ?? old('email') }}"
                            class="block w-full {{ $direction === 'rtl' ? 'pr-3 text-right' : 'pl-3' }} py-3 border border-slate-300 rounded-lg bg-gray-50 text-gray-500 focus:ring-0 outline-none transition"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">
                        {{ __('auth.PASSWORD_LABEL', [], $currentLang) ?? 'كلمة المرور الجديدة' }}
                    </label>
                    <div class="relative">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="block w-full {{ $direction === 'rtl' ? 'pr-3 text-right' : 'pl-3' }} py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        >
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-2">
                        {{ __('auth.PASSWORD_CONFIRM_LABEL', [], $currentLang) ?? 'تأكيد كلمة المرور' }}
                    </label>
                    <div class="relative">
                        <input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            required 
                            class="block w-full {{ $direction === 'rtl' ? 'pr-3 text-right' : 'pl-3' }} py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    {{ __('auth.BTN_RESET_PASSWORD', [], $currentLang) ?? 'تحديث كلمة المرور' }}
                </button>
            </form>
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
