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
                        {{ __('dashboard.back_to_dashboard') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('services.browse') }}" class="ml-1 text-sm font-medium hover:text-indigo-600 md:ml-2 rtl:mr-2 transition">Services</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2 rtl:mr-2">{{ $service->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Service Header Card -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">{{ $service->industry }}</span>
                            <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                {{ number_format($service->avg_rating ?? 0, 1) }} ({{ $service->reviews_count ?? 0 }})
                            </span>
                        </div>
                        
                        <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-6 leading-tight">{{ $service->title }}</h1>
                        
                        <div class="flex items-center gap-4 border-t border-slate-100 pt-6">
                            <img src="{{ $service->expert_image }}" class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-md">
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase mb-1">{{ __('dashboard.expert_label') }}</p>
                                <h3 class="font-bold text-slate-800 text-lg">{{ $service->expert_name }}</h3>
                                <p class="text-sm text-slate-500">{{ $service->expert_title ?? 'Specialist' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800 mb-4">{{ __('dashboard.about_service') }}</h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                        <p>{{ $service->description }}</p>
                        
                        @if(!empty($service->expert_bio))
                            <h3 class="text-lg font-bold text-slate-800 mt-6 mb-3">{{ __('dashboard.about_expert') }}</h3>
                            <p>{{ $service->expert_bio }}</p>
                        @endif
                    </div>
                </div>

                <!-- Skills -->
                @if(count($service->skills_array) > 0)
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">{{ __('dashboard.skills_expertise') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($service->skills_array as $skill)
                            <span class="px-4 py-2 bg-slate-50 text-slate-700 rounded-xl font-bold text-sm border border-slate-200">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Booking Card -->
                <div class="bg-white rounded-3xl p-6 shadow-lg border border-indigo-100 sticky top-24">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <p class="text-sm text-slate-400 font-bold uppercase">{{ __('dashboard.rate_label') }}</p>
                            <p class="text-3xl font-black text-indigo-600">${{ $service->hourly_rate }}<span class="text-lg text-slate-400 font-normal">{{ __('dashboard.per_hour') }}</span></p>
                        </div>
                    </div>

                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1 mb-4">
                        {{ __('dashboard.btn_request_expert') }}
                    </button>
                    <button class="w-full bg-white border-2 border-slate-200 hover:border-indigo-600 hover:text-indigo-600 text-slate-700 py-3 rounded-xl font-bold transition">
                        {{ __('dashboard.btn_contact_company') }}
                    </button>

                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-3">
                            <img src="{{ $service->company_logo }}" class="w-10 h-10 rounded-lg object-contain bg-slate-50 p-1 border border-slate-200">
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">{{ __('dashboard.provided_by') }}</p>
                                <p class="font-bold text-slate-800 text-sm">{{ $service->company_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
