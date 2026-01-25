@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pt-24 pb-20">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-slate-100">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('requests.SUBMIT_PROPOSAL_TITLE') }}</h1>
                <p class="text-slate-500">
                    {{ __('requests.SUBMIT_PROPOSAL_DESC') }}
                </p>
            </div>

            <!-- Request Info Card -->
            <div class="bg-purple-50 border border-purple-100 rounded-xl p-6 mb-8">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $request->title }}</h3>
                        <p class="text-sm text-slate-600 mb-2">{{ __('requests.POSTED_BY') }}: {{ $request->requester_name }}</p>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">{{ __('requests.MAX_RATE') }}:</span>
                            <span class="text-lg font-bold text-purple-600">${{ $request->max_hourly_rate }}<span class="text-sm font-normal text-slate-500">/hr</span></span>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-center">
                {{ session('success') }}
            </div>
            @endif

            <!-- Proposal Form -->
            <form action="{{ route('requests.proposal.send', $request->project_id) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_NAME') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_EMAIL') }} <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_COMPANY') }}</label>
                        <input type="text" name="company" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_PHONE') }}</label>
                        <input type="tel" name="phone" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_PROPOSED_RATE') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="proposed_rate" step="0.01" required placeholder="{{ __('requests.RATE_PLACEHOLDER') }}" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_ESTIMATED_HOURS') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="estimated_hours" required placeholder="{{ __('requests.HOURS_PLACEHOLDER') }}" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_COVER_LETTER') }} <span class="text-red-500">*</span></label>
                    <textarea name="cover_letter" rows="6" required placeholder="{{ __('requests.COVER_LETTER_PLACEHOLDER') }}" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_PORTFOLIO_LINK') }}</label>
                    <input type="url" name="portfolio_link" placeholder="https://" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl transition shadow-lg">
                        {{ __('requests.BTN_SUBMIT_PROPOSAL') }}
                    </button>
                    <a href="{{ route('requests.show', $request->project_id) }}" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-4 rounded-xl transition text-center">
                        {{ __('requests.BTN_CANCEL') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
