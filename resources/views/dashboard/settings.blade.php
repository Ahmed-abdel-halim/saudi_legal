@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-800 min-h-screen pb-20">
    <div class="container mx-auto px-6 py-10 max-w-4xl">
        
        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('dashboard') }}" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path></svg>
            </a>
            <h1 class="font-bold text-2xl text-slate-800">{{ __('dashboard.title') }}</h1>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 bg-green-50 text-green-700 border border-green-200">
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dashboard.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- 1. Identity -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="font-bold text-lg text-slate-800 mb-6 pb-2 border-b border-slate-100">{{ __('dashboard.section_identity') }}</h3>
                
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Logo Upload -->
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <div class="relative group cursor-pointer w-40 h-40">
                             @php
                                $logoSrc = !empty($company->company_logo) 
                                            ? asset($company->company_logo) 
                                            : "https://ui-avatars.com/api/?name=".urlencode($company->name)."&background=random";
                             @endphp
                            <img id="logoPreview" src="{{ $logoSrc }}" class="w-full h-full rounded-2xl border-2 border-dashed border-slate-300 object-contain p-2 group-hover:border-indigo-500 transition bg-white">
                            
                            <label for="logoInput" class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white rounded-2xl opacity-0 group-hover:opacity-100 transition duration-300 cursor-pointer">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-xs font-bold">{{ __('dashboard.change_logo') }}</span>
                            </label>
                            <input type="file" name="logo" id="logoInput" class="hidden" onchange="previewImage(this)">
                        </div>
                        <p class="text-xs text-slate-400 mt-2 text-center">PNG, JPG (Max 2MB)</p>
                    </div>

                    <!-- Info Inputs -->
                    <div class="w-full md:w-2/3 space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.company_name') }}</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $company->name) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition font-bold text-lg text-slate-800" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.cr_number') }}</label>
                            <div class="relative">
                                <input type="text" name="cr_number" value="{{ old('cr_number', $company->cr_number) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition bg-slate-50 font-mono tracking-wider">
                                @if($company->is_verified_provider)
                                    <div class="absolute top-3 left-3 flex items-center gap-1 text-green-600 text-xs font-bold bg-green-100 px-2 py-1 rounded">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        {{ __('dashboard.verified') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Details and Roles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Details -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <h3 class="font-bold text-lg text-slate-800 mb-6 pb-2 border-b border-slate-100">{{ __('dashboard.section_details') }}</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.industry') }}</label>
                            <select name="industry" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none bg-white">
                                @foreach(__('dashboard.industries') as $key => $label)
                                    <option value="{{ $key }}" {{ (old('industry', $company->industry) == $key) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.company_size') }}</label>
                            <select name="size" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none bg-white">
                                @foreach(__('dashboard.sizes') as $key => $label)
                                    <option value="{{ $key }}" {{ (old('size', $company->size) == $key) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Roles -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <h3 class="font-bold text-lg text-slate-800 mb-6 pb-2 border-b border-slate-100">{{ __('dashboard.section_roles') }}</h3>
                    
                    <div class="space-y-6">
                        <!-- Requester Switch -->
                        <div class="flex items-center justify-between">
                            <div class="w-3/4">
                                <h4 class="font-bold text-slate-800">{{ __('dashboard.role_requester') }}</h4>
                                <p class="text-xs text-slate-500 mt-1">{{ __('dashboard.role_requester_desc') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_requester" class="sr-only peer" {{ old('is_requester', $company->is_requester) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Supplier Switch -->
                        <div class="flex items-center justify-between">
                            <div class="w-3/4">
                                <h4 class="font-bold text-slate-800">{{ __('dashboard.role_supplier') }}</h4>
                                <p class="text-xs text-slate-500 mt-1">{{ __('dashboard.role_supplier_desc') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_supplier" class="sr-only peer" {{ old('is_supplier', $company->is_supplier) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Save Button -->
            <div class="pt-6 border-t border-slate-200 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('dashboard.save_changes') }}
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logoPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
