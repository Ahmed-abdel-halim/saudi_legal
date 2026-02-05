@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-800 min-h-screen pb-20">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.projects') }}" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ app()->getLocale() == 'ar' ? '9 5l7 7-7 7' : '15 19l-7-7 7-7' }}"></path></svg>
                </a>
                <span class="font-bold text-xl text-slate-800">{{ __('dashboard.btn_new_project') }}</span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            
            <form action="{{ route('dashboard.projects.store') }}" method="POST">
                @csrf
                
                <!-- Project Title -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                         {{ app()->getLocale() == 'ar' ? 'عنوان المشروع' : 'Project Title' }}
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: تطوير متجر إلكتروني' : 'Ex: E-commerce Development' }}">
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Scope Description -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'وصف المشروع' : 'Project Description' }}
                    </label>
                    <textarea name="scope_description" rows="5" required
                              class="w-full border border-slate-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                              placeholder="{{ app()->getLocale() == 'ar' ? 'صف متطلبات المشروع بالتفصيل...' : 'Describe the project details...' }}">{{ old('scope_description') }}</textarea>
                    @error('scope_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                             {{ app()->getLocale() == 'ar' ? 'المدة المقدرة (ساعات)' : 'Estimated Duration (Hours)' }}
                        </label>
                        <input type="number" name="requested_duration_hours" value="{{ old('requested_duration_hours') }}" required min="1"
                               class="w-full border border-slate-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               placeholder="Ex: 50">
                        @error('requested_duration_hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Max Hourly Rate -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                             {{ app()->getLocale() == 'ar' ? 'أقصى سعر للساعة (عملة)' : 'Max Hourly Rate (Currency)' }}
                        </label>
                        <input type="number" name="max_hourly_rate" value="{{ old('max_hourly_rate') }}" required min="0" step="0.01"
                               class="w-full border border-slate-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               placeholder="Ex: 25.00">
                        @error('max_hourly_rate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Skills -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        {{ app()->getLocale() == 'ar' ? 'المهارات المطلوبة (مفصولة بفاصلة)' : 'Required Skills (comma separated)' }}
                    </label>
                    <input type="text" name="skills" value="{{ old('skills') }}"
                           class="w-full border border-slate-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: Laravel, Vue.js, MySQL' : 'Ex: Laravel, Vue.js, MySQL' }}">
                    <p class="text-xs text-slate-400 mt-1">
                        {{ app()->getLocale() == 'ar' ? 'افصل بين المهارات بفاصلة' : 'Separate skills with commas' }}
                    </p>
                    @error('skills') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Submit -->
                <div class="flex justify-end border-t pt-6">
                    <a href="{{ route('dashboard.projects') }}" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-50 rounded-lg transition mr-2 ml-2">
                        {{ __('dashboard.btn_cancel') ?? 'Cancel' }}
                    </a>
                    <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:bg-indigo-700 hover:shadow-xl transition transform hover:-translate-y-1">
                        {{ __('dashboard.btn_save_project') ?? (app()->getLocale() == 'ar' ? 'نشر المشروع' : 'Post Project') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
