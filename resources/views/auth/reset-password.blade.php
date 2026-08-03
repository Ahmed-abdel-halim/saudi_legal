@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" dir="{{ $direction }}">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo/Header -->
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-slate-800 mb-2">
                {{ __('auth.RESET_PASSWORD_TITLE', [], $currentLang) ?? 'إعادة تعيين كلمة المرور' }}
            </h2>
            <p class="text-slate-600 text-sm">
                {{ __('auth.RESET_PASSWORD_DESC', [], $currentLang) ?? 'أدخل كلمة المرور الجديدة أدناه' }}
            </p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm text-sm">
            <ul class="list-disc list-inside space-y-1">
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

                <!-- Email Field (Readonly) -->
                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">
                        {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) ?? 'البريد الإلكتروني' }}
                    </label>
                    <div class="relative">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            readonly
                            value="{{ $email ?? old('email') }}"
                            class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 font-medium text-sm focus:outline-none transition cursor-not-allowed"
                        >
                    </div>
                </div>

                <!-- Password Field -->
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
                            placeholder="••••••••"
                            class="block w-full {{ $direction === 'rtl' ? 'pl-11 pr-4' : 'pr-11 pl-4' }} py-3 border border-slate-300 rounded-xl text-slate-900 bg-slate-50 focus:bg-white text-base font-medium placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        >
                        <button 
                            type="button"
                            class="absolute {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none focus:text-indigo-600 transition-colors p-1"
                            onclick="togglePassword('password', this)"
                            aria-label="{{ __('auth.SA_TOGGLE_PASSWORD', [], $currentLang) ?? 'إظهار/إخفاء كلمة المرور' }}"
                        >
                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-5 h-5 eye-slash-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password Field -->
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
                            placeholder="••••••••"
                            class="block w-full {{ $direction === 'rtl' ? 'pl-11 pr-4' : 'pr-11 pl-4' }} py-3 border border-slate-300 rounded-xl text-slate-900 bg-slate-50 focus:bg-white text-base font-medium placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        >
                        <button 
                            type="button"
                            class="absolute {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none focus:text-indigo-600 transition-colors p-1"
                            onclick="togglePassword('password_confirmation', this)"
                            aria-label="{{ __('auth.SA_TOGGLE_PASSWORD', [], $currentLang) ?? 'إظهار/إخفاء كلمة المرور' }}"
                        >
                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-5 h-5 eye-slash-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    {{ __('auth.BTN_RESET_PASSWORD', [], $currentLang) ?? 'تحديث كلمة المرور' }}
                </button>
            </form>
        </div>

        <!-- Additional Help -->
        <div class="text-center">
            <p class="text-sm text-slate-600">
                {{ __('auth.NEED_HELP', [], $currentLang) ?? 'تحتاج مساعدة؟' }}
                <a href="{{ route('contact') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                    {{ __('auth.CONTACT_SUPPORT', [], $currentLang) ?? 'تواصل مع الدعم' }}
                </a>
            </p>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const eyeIcon = button.querySelector('.eye-icon');
        const eyeSlashIcon = button.querySelector('.eye-slash-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
        }
    }
</script>
@endsection

