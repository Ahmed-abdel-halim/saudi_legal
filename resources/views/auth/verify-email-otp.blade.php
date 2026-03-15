@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 bg-brand-primary rounded-2xl flex items-center justify-center text-white font-bold text-3xl shadow-lg">
                R
            </div>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            {{ __('auth.VERIFY_EMAIL_TITLE', [], app()->getLocale()) ?? 'Verify Your Email' }}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            {{ __('auth.VERIFY_EMAIL_INSTRUCTION', [], app()->getLocale()) ?? "We've sent a 6-digit verification code to" }} <br>
            <span class="font-bold text-brand-primary">{{ $email ?? session('email') }}</span>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100">
            
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm text-center font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('verify-otp.submit') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="otp_code" class="block text-sm font-bold text-gray-700 mb-2">
                        {{ __('auth.ENTER_OTP', [], app()->getLocale()) ?? 'Enter 6-digit code' }}
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input id="otp_code" name="otp_code" type="text" maxlength="6" pattern="\d{6}" required
                            class="appearance-none block w-full px-4 py-3 sm:text-2xl text-center tracking-widest font-mono border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent transition-all"
                            placeholder="000000" autofocus>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 text-center">{{ __('auth.OTP_VALIDITY', [], app()->getLocale()) ?? 'Code expires in 10 minutes.' }}</p>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-brand-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary transition-all">
                        {{ __('auth.BTN_VERIFY_OTP', [], app()->getLocale()) ?? 'Verify Code' }}
                    </button>
                </div>
            </form>

            <div class="mt-8 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">
                        {{ __('auth.DIDNT_RECEIVE_CODE', [], app()->getLocale()) ?? "Didn't receive the code?" }}
                    </span>
                </div>
            </div>

            <div class="mt-6 text-center">
                <form action="{{ route('verify-otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                        {{ __('auth.BTN_RESEND_OTP', [], app()->getLocale()) ?? 'Resend Code' }}
                    </button>
                </form>
            </div>
            
            <div class="mt-6 text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                        {{ __('auth.CANCEL_AND_LOGOUT', [], app()->getLocale()) ?? 'Cancel and Return to Home' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
