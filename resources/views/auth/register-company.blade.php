{{-- resources/views/auth/register-company.blade.php - Company Registration Form --}}
@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<style>
    /* Registration Page Styles */
    .registration-container {
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(2rem, 4vw, 4rem) 1rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
    }

    .registration-card {
        width: 100%;
        max-width: 700px;
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: clamp(1.5rem, 3vw, 3rem);
        animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Progress Bar */
    .progress-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 0.5rem;
    }

    .progress-step {
        flex: 1;
        text-align: center;
        font-size: clamp(0.75rem, 1.2vw, 0.875rem);
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .progress-step.active {
        font-weight: 700;
        color: #0d9488;
        background: rgba(13, 148, 136, 0.1);
    }

    .progress-step.inactive {
        color: #9CA3AF;
    }

    .progress-bar-container {
        height: 0.5rem;
        background: #E5E7EB;
        border-radius: 9999px;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #0d9488 0%, #14b8a6 100%);
        border-radius: 9999px;
        transition: width 0.5s ease;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #E5E7EB;
        border-radius: 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
    }

    .form-input:focus {
        outline: none;
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }

    .form-input::placeholder {
        color: #9CA3AF;
    }

    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);
        color: white;
        font-weight: 600;
        padding: 0.875rem 1.5rem;
        border-radius: 0.75rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-secondary {
        background: #F3F4F6;
        color: #374151;
        font-weight: 600;
        padding: 0.875rem 1.5rem;
        border-radius: 0.75rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #E5E7EB;
    }

    /* Step Transitions */
    .step-content {
        animation: slideIn 0.4s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    [dir="rtl"] .step-content {
        animation: slideInRtl 0.4s ease-out;
    }

    @keyframes slideInRtl {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Checkbox */
    .checkbox-container {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        background: #F9FAFB;
        border-radius: 0.75rem;
        border: 2px solid #E5E7EB;
    }

    .checkbox-container input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.125rem;
        cursor: pointer;
        accent-color: #0d9488;
    }

    /* Error Messages */
    .error-message {
        background: #FEE2E2;
        color: #DC2626;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin-bottom: 1rem;
        border: 1px solid #FCA5A5;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .progress-steps {
            flex-direction: column;
            gap: 0.75rem;
        }

        .progress-step {
            width: 100%;
            text-align: left;
            padding: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
@auth
<script>
    window.location.href = '{{ route("dashboard") }}';
</script>
@endauth

<div class="registration-container">
    <div class="registration-card">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h2 class="text-3xl md:text-4xl font-black text-dark-navy mb-3">
                {{ __('auth.AUTH_CREATE_COMPANY', [], $currentLang) }}
            </h2>
            <p class="text-gray-600 text-base md:text-lg" id="form-subtitle">
                {{ __('auth.AUTH_LOGIN_TO_CONTINUE', [], $currentLang) }}
            </p>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
        <div class="error-message mb-6">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="progress-steps">
                <div class="progress-step active" id="step-title-1">
                    1. {{ __('auth.AUTH_STEP_ACCOUNT', [], $currentLang) }}
                </div>
                <div class="progress-step inactive" id="step-title-2">
                    2. {{ __('auth.AUTH_STEP_COMPANY', [], $currentLang) }}
                </div>
                <div class="progress-step inactive" id="step-title-3">
                    3. {{ __('auth.AUTH_STEP_AGREEMENT', [], $currentLang) }}
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar" style="width: 33.33%"></div>
            </div>
        </div>

        {{-- Form --}}
        <form id="multi-step-form" action="{{ route('register.company.handle') }}" method="POST" class="space-y-6">
            @csrf

            <input type="hidden" id="registration-type" name="registration-type" value="{{ $type ?? request('type', 'supplier') }}">

            {{-- Step 1: Account Information --}}
            <div id="step-1" class="step-content space-y-5">
                <div class="form-group">
                    <label for="full-name" class="form-label">
                        {{ __('auth.AUTH_FULL_NAME', [], $currentLang) }}
                    </label>
                    <input type="text"
                        id="full-name"
                        name="full-name"
                        required
                        class="form-input"
                        value="{{ old('full-name') }}"
                        placeholder="{{ __('auth.AUTH_FULL_NAME', [], $currentLang) }}">
                </div>

                <div class="form-group">
                    <label for="work-email" class="form-label">
                        {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}
                    </label>
                    <input type="email"
                        id="work-email"
                        name="work-email"
                        required
                        class="form-input"
                        value="{{ old('work-email') }}"
                        placeholder="example@company.com">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        {{ __('auth.AUTH_PASSWORD_LABEL', [], $currentLang) }}
                    </label>
                    <input type="password"
                        id="password"
                        name="password"
                        required
                        class="form-input"
                        placeholder="••••••••"
                        minlength="8">
                </div>

                <div class="form-group">
                    <label for="password-confirm" class="form-label">
                        {{ __('auth.AUTH_PASSWORD_CONFIRM', [], $currentLang) }}
                    </label>
                    <input type="password"
                        id="password-confirm"
                        name="password_confirmation"
                        required
                        class="form-input"
                        placeholder="••••••••"
                        minlength="8">
                </div>

                <button type="button"
                    onclick="nextStep(2)"
                    class="w-full btn-primary text-lg py-3">
                    {{ __('auth.BTN_NEXT', [], $currentLang) }} →
                </button>
            </div>

            {{-- Step 2: Company Information --}}
            <div id="step-2" class="step-content space-y-5 hidden">
                <div class="form-group">
                    <label for="company-name" class="form-label">
                        {{ __('auth.AUTH_COMPANY_NAME', [], $currentLang) }}
                    </label>
                    <input type="text"
                        id="company-name"
                        name="company-name"
                        required
                        class="form-input"
                        value="{{ old('company-name') }}"
                        placeholder="{{ __('auth.AUTH_COMPANY_NAME', [], $currentLang) }}">
                </div>

                <div class="form-group">
                    <label for="cr-number" class="form-label">
                        {{ __('auth.AUTH_CR_NUMBER', [], $currentLang) }}
                    </label>
                    <input type="text"
                        id="cr-number"
                        name="cr-number"
                        required
                        class="form-input"
                        placeholder="{{ __('auth.AUTH_CR_PLACEHOLDER', [], $currentLang) }}"
                        value="{{ old('cr-number') }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="industry" class="form-label">
                            {{ __('auth.AUTH_INDUSTRY', [], $currentLang) }}
                        </label>
                        <select id="industry"
                            name="industry"
                            class="form-input"
                            required>
                            <option value="">{{ __('auth.AUTH_INDUSTRY', [], $currentLang) }}</option>
                            <option value="تقنية المعلومات" @selected(old('industry')==='تقنية المعلومات' )>
                                {{ __('auth.INDUSTRY_IT', [], $currentLang) }}
                            </option>
                            <option value="تصميم واجهات (UI)" @selected(old('industry')==='تصميم واجهات (UI)' )>
                                {{ __('auth.INDUSTRY_UI', [], $currentLang) }}
                            </option>
                            <option value="تجربة العميل (CX)" @selected(old('industry')==='تجربة العميل (CX)' )>
                                {{ __('auth.INDUSTRY_CX', [], $currentLang) }}
                            </option>
                            <option value="استشارات مالية" @selected(old('industry')==='استشارات مالية' )>
                                {{ __('auth.INDUSTRY_FINANCE', [], $currentLang) }}
                            </option>
                            <option value="التسويق الرقمي" @selected(old('industry')==='التسويق الرقمي' )>
                                {{ __('auth.INDUSTRY_MARKETING', [], $currentLang) }}
                            </option>
                            <option value="other" @selected(old('industry')==='other' )>
                                {{ __('auth.INDUSTRY_OTHER', [], $currentLang) }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="company-size" class="form-label">
                            {{ __('auth.AUTH_COMPANY_SIZE', [], $currentLang) }}
                        </label>
                        <select id="company-size"
                            name="company-size"
                            class="form-input"
                            required>
                            <option value="">{{ __('auth.AUTH_COMPANY_SIZE', [], $currentLang) }}</option>
                            <option value="1-10" @selected(old('company-size')==='1-10' )>
                                {{ __('auth.SIZE_1_10', [], $currentLang) }}
                            </option>
                            <option value="11-50" @selected(old('company-size')==='11-50' )>
                                {{ __('auth.SIZE_11_50', [], $currentLang) }}
                            </option>
                            <option value="51-200" @selected(old('company-size')==='51-200' )>
                                {{ __('auth.SIZE_51_200', [], $currentLang) }}
                            </option>
                            <option value="201+" @selected(old('company-size')==='201+' )>
                                {{ __('auth.SIZE_201_PLUS', [], $currentLang) }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="button"
                        onclick="prevStep(1)"
                        class="flex-1 btn-secondary">
                        ← {{ __('auth.BTN_PREVIOUS', [], $currentLang) }}
                    </button>
                    <button type="button"
                        onclick="nextStep(3)"
                        class="flex-1 btn-primary">
                        {{ __('auth.BTN_NEXT', [], $currentLang) }} →
                    </button>
                </div>
            </div>

            {{-- Step 3: Agreement --}}
            <div id="step-3" class="step-content space-y-6 hidden">
                <div>
                    <h3 class="text-2xl font-bold text-dark-navy mb-3">
                        {{ __('auth.AUTH_LEGAL_AGREEMENT', [], $currentLang) }}
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ __('auth.AUTH_LEGAL_AGREEMENT_DESC', [], $currentLang) }}
                    </p>
                </div>

                <div class="checkbox-container">
                    <input type="checkbox"
                        id="terms"
                        name="terms"
                        required>
                    <label for="terms" class="text-sm text-gray-700 cursor-pointer">
                        {{ __('auth.AUTH_I_AGREE', [], $currentLang) }}
                        <a href="{{ route('legal.terms') ?? '#' }}"
                            target="_blank"
                            class="text-brand-teal hover:underline font-semibold">
                            {{ __('auth.AUTH_TERMS', [], $currentLang) }}
                        </a>
                        {{ __('auth.AND', [], $currentLang) }}
                        <a href="{{ route('legal.privacy') ?? '#' }}"
                            target="_blank"
                            class="text-brand-teal hover:underline font-semibold">
                            {{ __('auth.AUTH_PRIVACY', [], $currentLang) }}
                        </a>
                    </label>
                </div>

                <div class="flex gap-4">
                    <button type="button"
                        onclick="prevStep(2)"
                        class="flex-1 btn-secondary">
                        ← {{ __('auth.BTN_PREVIOUS', [], $currentLang) }}
                    </button>
                    <button type="submit"
                        class="flex-1 btn-primary text-lg">
                        {{ __('auth.BTN_SIGN_UP', [], $currentLang) }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Login Link --}}
        <p class="text-center text-sm text-gray-600 mt-8">
            {{ __('auth.AUTH_ALREADY_HAVE_ACCOUNT', [], $currentLang) }}
            <a href="{{ route('login') ?? '#' }}"
                class="font-semibold text-brand-teal hover:text-brand-teal/80 hover:underline">
                {{ __('auth.BTN_LOGIN', [], $currentLang) }}
            </a>
        </p>
    </div>
</div>

@push('scripts')
<script>
    let currentStep = 1;

    window.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const type = urlParams.get('type') || 'supplier';
        const registrationTypeInput = document.getElementById('registration-type');
        if (registrationTypeInput) {
            registrationTypeInput.value = type;
        }
    });

    function showStep(step) {
        // Hide all steps
        document.getElementById('step-1').classList.add('hidden');
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-3').classList.add('hidden');

        // Show current step
        document.getElementById('step-' + step).classList.remove('hidden');

        // Update progress
        updateProgress(step);
    }

    function nextStep(step) {
        // Validate current step before proceeding
        if (currentStep === 1) {
            const fullName = document.getElementById('full-name').value.trim();
            const email = document.getElementById('work-email').value.trim();
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password-confirm').value;

            if (!fullName || !email || !password || !passwordConfirm) {
                alert('{{ __("auth.ERROR_EMPTY_FIELDS", [], $currentLang) }}');
                return;
            }

            if (password.length < 8) {
                alert('{{ __("auth.ERROR_PASSWORD_MIN", [], $currentLang) }}');
                return;
            }

            if (password !== passwordConfirm) {
                alert('{{ __("auth.ERROR_PASSWORD_CONFIRMATION", [], $currentLang) }}');
                return;
            }
        }

        if (currentStep === 2) {
            const companyName = document.getElementById('company-name').value.trim();
            const crNumber = document.getElementById('cr-number').value.trim();
            const industry = document.getElementById('industry').value;
            const companySize = document.getElementById('company-size').value;

            if (!companyName || !crNumber || !industry || !companySize) {
                alert('{{ __("auth.ERROR_EMPTY_FIELDS", [], $currentLang) }}');
                return;
            }
        }

        currentStep = step;
        showStep(currentStep);
    }

    function prevStep(step) {
        currentStep = step;
        showStep(currentStep);
    }

    function updateProgress(step) {
        const progressBar = document.getElementById('progress-bar');
        const stepTitle1 = document.getElementById('step-title-1');
        const stepTitle2 = document.getElementById('step-title-2');
        const stepTitle3 = document.getElementById('step-title-3');

        // Reset all steps
        [stepTitle1, stepTitle2, stepTitle3].forEach(el => {
            el.classList.remove('active');
            el.classList.add('inactive');
        });

        // Update progress bar and active step
        if (step === 1) {
            progressBar.style.width = '33.33%';
            stepTitle1.classList.remove('inactive');
            stepTitle1.classList.add('active');
        } else if (step === 2) {
            progressBar.style.width = '66.66%';
            stepTitle2.classList.remove('inactive');
            stepTitle2.classList.add('active');
        } else if (step === 3) {
            progressBar.style.width = '100%';
            stepTitle3.classList.remove('inactive');
            stepTitle3.classList.add('active');
        }
    }
</script>
@endpush
@endsection