@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pt-24 pb-20">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-slate-100">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('requests.CONTACT_COMPANY_TITLE') }}</h1>
                <p class="text-slate-500">
                    {{ __('requests.CONTACT_COMPANY_DESC', ['company' => $request->requester_name, 'project' => $request->title]) }}
                </p>
            </div>

            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-center">
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('requests.contact.send', $request->project_id) }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_NAME') }}</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_EMAIL') }}</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('requests.FORM_MESSAGE') }}</label>
                    <textarea name="message" rows="5" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
                        {{ __('requests.BTN_SEND_MESSAGE') }}
                    </button>
                    <a href="{{ route('requests.show', $request->project_id) }}" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition text-center">
                        {{ __('requests.BTN_CANCEL') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
