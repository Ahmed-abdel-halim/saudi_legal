@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-20 pt-24">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-7xl">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center hover:text-indigo-600 transition font-medium">
                        <svg class="w-4 h-4 mr-2 rtl:ml-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        {{ __('dashboard.home') ?? 'Home' }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('services.browse') }}" class="ml-1 text-sm font-medium hover:text-indigo-600 md:ml-2 rtl:mr-2 transition">{{ __('services.SERVICES_TITLE') ?? 'Services' }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2 rtl:mr-2 truncate max-w-[200px] md:max-w-md">{{ $service->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Service Header -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 overflow-hidden relative">
                    <!-- Decorative Background Gradient -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
                    <div class="absolute top-0 right-40 w-64 h-64 bg-purple-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>

                    <div class="relative z-10">
                        <div class="flex flex-wrap gap-3 mb-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                                {{ $service->industry ?? 'General' }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-700 gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span>{{ number_format($service->avg_rating ?? 5.0, 1) }}</span>
                                <span class="text-amber-600/60 font-medium">({{ $service->reviews_count ?? 0 }} reviews)</span>
                            </span>
                        </div>
                        
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-6 leading-tight tracking-tight">
                            {{ $service->title }}
                        </h1>
                        
                        <div class="flex items-center gap-4 mt-8 pt-8 border-t border-gray-100">
                            <div class="relative">
                                <img src="{{ $service->expert_image ?? 'https://ui-avatars.com/api/?name=Expert&background=random' }}" 
                                     alt="{{ $service->expert_name }}"
                                     class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-md"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Expert&background=ccc';">
                                <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-bold uppercase tracking-wider mb-0.5">{{ __('dashboard.expert_label') }}</p>
                                <h3 class="font-bold text-gray-900 text-lg">{{ $service->expert_name }}</h3>
                                <p class="text-sm text-gray-500 font-medium">{{ $service->expert_title ?? 'Service Expert' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ __('dashboard.about_service') }}
                    </h2>
                    <div class="prose prose-slate max-w-none text-gray-600 leading-relaxed">
                        <p class="whitespace-pre-line">{{ $service->description }}</p>
                        
                        @if(!empty($service->expert_bio))
                            <div class="mt-8 pt-6 border-t border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900 mb-3">{{ __('dashboard.about_expert') }}</h3>
                                <p class="whitespace-pre-line">{{ $service->expert_bio }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Skills Card -->
                @if(isset($service->skills_array) && count($service->skills_array) > 0)
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        {{ __('dashboard.skills_expertise') }}
                    </h2>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach($service->skills_array as $skill)
                            <span class="px-4 py-2 bg-gray-50 hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 rounded-lg font-semibold text-sm border border-gray-200 hover:border-indigo-200 transition duration-300 cursor-default">
                                {{ trim($skill) }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Pricing & Action Card -->
                <div class="bg-white rounded-2xl p-6 shadow-xl shadow-indigo-100/50 border border-indigo-50 sticky top-24">
                    <div class="mb-6 pb-6 border-b border-gray-50">
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-wider mb-1">{{ __('dashboard.rate_label') }}</p>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">${{ number_format($service->hourly_rate, 2) }}</span>
                            <span class="text-lg text-gray-500 font-medium">{{ __('dashboard.per_hour') }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('services.request', $service->service_id) }}" 
                           class="flex justify-center items-center w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-xl font-bold text-lg shadow-lg shadow-indigo-200 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-indigo-300">
                            {{ __('dashboard.btn_request_expert') }}
                            <svg class="w-5 h-5 ml-2 rtl:mr-2 rtl:ml-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                        
                        <a href="{{ route('services.contact', $service->service_id) }}"
                           class="flex justify-center items-center w-full bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-200 hover:border-gray-300 px-6 py-3.5 rounded-xl font-bold text-base transition-all duration-300">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2 rtl:mr-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            {{ __('dashboard.btn_contact_company') }}
                        </a>
                    </div>
                
                    <!-- Company Info Mini-Section -->
                    @if($service->company_name)
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-4 group cursor-pointer">
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 group-hover:border-indigo-100 transition">
                                <img src="{{ $service->company_logo ?? 'https://ui-avatars.com/api/?name=Co&background=random' }}" 
                                     class="w-10 h-10 object-contain"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Co&background=efefef';">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">{{ __('dashboard.provided_by') }}</p>
                                <p class="font-bold text-gray-900 text-sm truncate group-hover:text-indigo-600 transition">{{ $service->company_name }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Trust Badges (Static for now to build trust) -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <div class="bg-green-100 text-green-600 p-1.5 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="font-medium">Verified Professional</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <div class="bg-blue-100 text-blue-600 p-1.5 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <span class="font-medium">Secure Payment</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <div class="bg-purple-100 text-purple-600 p-1.5 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            </div>
                            <span class="font-medium">Full Satisfaction Guarantee</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
