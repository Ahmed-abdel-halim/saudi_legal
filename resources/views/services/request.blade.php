@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pt-24 pb-20">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-slate-100">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('services.REQUEST_EXPERT_TITLE') }}</h1>
                <p class="text-slate-500">
                    {{ __('services.REQUEST_EXPERT_DESC') }}
                </p>
            </div>

            <!-- Service Info Card -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-8">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $service->title }}</h3>
                        <p class="text-sm text-slate-600 mb-2">{{ __('services.PROVIDED_BY') }}: {{ $service->company_name }}</p>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">{{ __('services.RATE') }}:</span>
                            <span class="text-lg font-bold text-indigo-600">${{ $service->hourly_rate }}<span class="text-sm font-normal text-slate-500">/hr</span></span>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-center">
                {{ session('success') }}
            </div>
            @endif

            <!-- Request Form -->
            <form action="{{ route('services.request.send', $service->service_id) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_NAME') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_EMAIL') }} <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_COMPANY') }}</label>
                        <input type="text" name="company" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_PHONE') }}</label>
                        <input type="tel" name="phone" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_PROJECT_TYPE') }} <span class="text-red-500">*</span></label>
                    <select name="project_type" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <option value="">{{ __('services.SELECT_PROJECT_TYPE') }}</option>
                        <option value="full-time">{{ __('services.PROJECT_FULL_TIME') }}</option>
                        <option value="part-time">{{ __('services.PROJECT_PART_TIME') }}</option>
                        <option value="contract">{{ __('services.PROJECT_CONTRACT') }}</option>
                        <option value="one-time">{{ __('services.PROJECT_ONE_TIME') }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_DURATION') }}</label>
                        <input type="text" name="duration" placeholder="{{ __('services.DURATION_PLACEHOLDER') }}" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_BUDGET') }}</label>
                        <input type="text" name="budget" placeholder="{{ __('services.BUDGET_PLACEHOLDER') }}" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('services.FORM_PROJECT_DETAILS') }} <span class="text-red-500">*</span></label>
                    <textarea name="project_details" rows="5" required placeholder="{{ __('services.PROJECT_DETAILS_PLACEHOLDER') }}" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition shadow-lg">
                        {{ __('services.BTN_SUBMIT_REQUEST') }}
                    </button>
                    <a href="{{ route('services.show', $service->service_id) }}" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-4 rounded-xl transition text-center">
                        {{ __('services.BTN_CANCEL') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
