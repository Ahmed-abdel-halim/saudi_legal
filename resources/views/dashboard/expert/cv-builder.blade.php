<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('expert_cv.title') }} | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-green-200 shadow-lg">R</div>
                <div>
                    <h1 class="font-bold text-lg leading-none text-slate-800">Radiif</h1>
                    <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT DASHBOARD</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard.expert') }}" class="text-slate-600 hover:text-green-600 transition text-sm font-medium">
                    <i class="fa-solid fa-arrow-left ml-2"></i> {{ __('expert_cv.back_to_dashboard') }}
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-5xl">
        
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('expert_cv.title') }}</h1>
                <p class="text-slate-600">{{ __('expert_cv.subtitle') }}</p>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg font-medium hover:bg-slate-50 transition">
                    <i class="fa-solid fa-eye ml-2"></i> {{ __('expert_cv.preview_cv') }}
                </button>
                <button class="px-4 py-2 bg-slate-700 text-white rounded-lg font-medium hover:bg-slate-800 transition">
                    <i class="fa-solid fa-download ml-2"></i> {{ __('expert_cv.download_cv') }}
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fa-solid fa-circle-check ml-2"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.expert.cv-builder') }}" enctype="multipart/form-data">
            @csrf

            <!-- Personal Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user text-green-600"></i> {{ __('expert_cv.personal_info') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.full_name') }}</label>
                        <input type="text" name="full_name" value="{{ $user->full_name ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.job_title') }}</label>
                        <input type="text" name="job_title" value="{{ $user->job_title ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.email') }}</label>
                        <input type="email" name="email" value="{{ $user->email ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.phone') }}</label>
                        <input type="tel" name="phone" value="{{ $user->phone ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.bio') }}</label>
                        <textarea name="bio" rows="4" placeholder="{{ __('expert_cv.bio_placeholder') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ $user->bio ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-lightbulb text-green-600"></i> {{ __('expert_cv.skills') }}
                    </h3>
                    <button type="button" class="text-green-600 hover:text-green-700 font-medium text-sm">
                        <i class="fa-solid fa-plus ml-1"></i> {{ __('expert_cv.add_skill') }}
                    </button>
                </div>
                
                <div id="skills-container" class="space-y-3">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <input type="text" name="skills[]" placeholder="{{ __('expert_cv.skill_name') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                        </div>
                        <div class="w-48">
                            <select name="skill_levels[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                @foreach(['beginner', 'intermediate', 'advanced', 'expert'] as $level)
                                    <option value="{{ $level }}">{{ __('expert_cv.levels.' . $level) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Experience -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-green-600"></i> {{ __('expert_cv.experience') }}
                    </h3>
                    <button type="button" class="text-green-600 hover:text-green-700 font-medium text-sm">
                        <i class="fa-solid fa-plus ml-1"></i> {{ __('expert_cv.add_experience') }}
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.company_name') }}</label>
                                <input type="text" name="experience_company[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.position') }}</label>
                                <input type="text" name="experience_position[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.start_date') }}</label>
                                <input type="month" name="experience_start[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.end_date') }}</label>
                                <input type="month" name="experience_end[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.description') }}</label>
                                <textarea name="experience_desc[]" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-graduation-cap text-green-600"></i> {{ __('expert_cv.education') }}
                    </h3>
                    <button type="button" class="text-green-600 hover:text-green-700 font-medium text-sm">
                        <i class="fa-solid fa-plus ml-1"></i> {{ __('expert_cv.add_education') }}
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.institution') }}</label>
                                <input type="text" name="education_institution[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.degree') }}</label>
                                <input type="text" name="education_degree[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.field_of_study') }}</label>
                                <input type="text" name="education_field[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_cv.graduation_year') }}</label>
                                <input type="number" name="education_year[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 justify-end">
                <button type="button" class="px-6 py-3 border border-slate-300 text-slate-600 rounded-xl font-medium hover:bg-slate-50 transition">
                    {{ __('expert_cv.cancel') }}
                </button>
                <button type="submit" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition">
                    <i class="fa-solid fa-save ml-2"></i> {{ __('expert_cv.save_cv') }}
                </button>
            </div>
        </form>

    </div>

</body>
</html>
