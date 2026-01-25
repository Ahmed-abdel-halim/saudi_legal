@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20 pt-24">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-6xl">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center hover:text-indigo-600 transition">
                        <svg class="w-4 h-4 mr-2 rtl:ml-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        {{ __('requests.BACK_TO_HOME', [], $currentLang) }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('requests.browse') }}" class="ml-1 text-sm font-medium hover:text-indigo-600 md:ml-2 rtl:mr-2 transition">{{ __('requests.REQUESTS_TITLE', [], $currentLang) }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2 rtl:mr-2">{{ $request->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Request Header Card -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">{{ __('requests.PROJECT_REQUEST', [], $currentLang) }}</span>
                        <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                            {{ $request->requested_duration_hours }} {{ __('requests.REQUEST_HOURS_PLURAL', [], $currentLang) }}
                        </span>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-6 leading-tight">{{ $request->title }}</h1>
                    
                    <div class="flex items-center gap-4 border-t border-slate-100 pt-6">
                        <img src="{{ $request->requester_logo ?? 'https://ui-avatars.com/api/?name=Company&background=random' }}" class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-md">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase mb-1">{{ __('requests.POSTED_BY', [], $currentLang) }}</p>
                            <h3 class="font-bold text-slate-800 text-lg">{{ $request->requester_name }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800 mb-4">{{ __('requests.PROJECT_DESCRIPTION', [], $currentLang) }}</h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                        <p>{{ $request->scope_description }}</p>
                    </div>
                </div>

                <!-- Required Skills -->
                @if(count($request->skills_array) > 0)
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">{{ __('requests.REQUIRED_SKILLS', [], $currentLang) }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($request->skills_array as $skill)
                            <span class="px-4 py-2 bg-purple-50 text-purple-700 rounded-xl font-bold text-sm border border-purple-200">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Budget Card -->
                <div class="bg-white rounded-3xl p-6 shadow-lg border border-purple-100 sticky top-24">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <p class="text-sm text-slate-400 font-bold uppercase">{{ __('requests.MAX_RATE', [], $currentLang) }}</p>
                            <p class="text-3xl font-black text-purple-600">${{ $request->max_hourly_rate }}<span class="text-lg text-slate-400 font-normal">/hr</span></p>
                        </div>
                    </div>

                    <a href="{{ route('requests.proposal', $request->project_id) }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-purple-200 transition transform hover:-translate-y-1 mb-4">
                        {{ __('requests.BTN_SUBMIT_PROPOSAL', [], $currentLang) }}
                    </a>
                    <a href="{{ route('requests.contact', $request->project_id) }}" class="block w-full text-center bg-white border-2 border-slate-200 hover:border-purple-600 hover:text-purple-600 text-slate-700 py-3 rounded-xl font-bold transition">
                        {{ __('requests.BTN_CONTACT_COMPANY', [], $currentLang) }}
                    </a>

                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-3">
                            <img src="{{ $request->requester_logo ?? 'https://ui-avatars.com/api/?name=Company&background=random' }}" class="w-10 h-10 rounded-lg object-contain bg-slate-50 p-1 border border-slate-200">
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">{{ __('requests.POSTED_BY', [], $currentLang) }}</p>
                                <p class="font-bold text-slate-800 text-sm">{{ $request->requester_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
