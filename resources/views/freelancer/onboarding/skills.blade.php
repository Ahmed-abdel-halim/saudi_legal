@extends('layouts.app')

@extends('layouts.app')

@section('content')
{{-- Load Tagify CSS/JS --}}
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

<style>
    /* Custom Tagify Styling to match Brand */
    .tagify {
        --tags-border-color: #E2E8F0;
        --tags-hover-border-color: #1B7A7E;
        --tags-focus-border-color: #1B7A7E;
        --tag-bg: #F0FDFA;
        --tag-hover: #CCFBF1;
        --tag-text-color: #0F766E;
        --tag-text-color--edit: #111827;
        --tag-pad: 0.3rem 0.5rem;
        --tag-inset-shadow-size: 1.1em;
        --tag-invalid-color: #D39494;
        --tag-invalid-bg: rgba(211, 148, 148, 0.5);
        --tag-remove-bg: rgba(211, 148, 148, 0.3);
        --tag-remove-btn-color: black;
        --tag-remove-btn-bg: none;
        --tag-remove-btn-bg--hover: #c77777;
        
        border-radius: 0.75rem;
        padding: 0.5rem;
        transition: all 0.2s;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .tagify:focus-within {
        border-color: #1B7A7E;
        box-shadow: 0 0 0 3px rgba(27, 122, 126, 0.1);
    }

    .tagify__tag {
        margin-top: 5px;
        background-color: var(--tag-bg);
        border: 1px solid #99F6E4;
        border-radius: 9999px; /* Pill shape */
    }

    .tagify__tag > div {
        border-radius: 9999px;
    }

    /* Animation */
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-[url('https://radiif.com/images/pattern.png')] bg-repeat bg-opacity-5">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-xl fade-in-up">
        
        <!-- Brand Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/icon.png') }}" class="mx-auto h-20 w-20 rounded-full shadow-lg shadow-teal-500/20" alt="Radiif">
        </div>

        <div class="bg-white py-10 px-6 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-12 border border-white relative overflow-hidden">
            
            <!-- Top Gradient Line -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-teal via-teal-400 to-brand-magenta"></div>

            <div class="text-center mb-10">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    أظهر مهاراتك للعالم
                </h2>
                <p class="mt-3 text-lg text-gray-500">
                    أضف المهارات التي تميزك لزيادة فرصك في الحصول على مشاريع تناسب خبرتك.
                </p>
            </div>

            <form action="{{ route('freelancer.onboarding.skills.store') }}" method="POST" id="skills-form">
                @csrf
                
                {{-- Skills input container --}}
                <div class="mb-8">
                    <label for="skill-input" class="block text-sm font-bold text-gray-700 mb-2">المهارات والخبرات</label>
                    
                    <div class="relative">
                        <input name="skills_json" 
                               id="skill-input" 
                               class="w-full text-base" 
                               placeholder="اكتب المهارة واضغط Enter (مثال: PHP, Photoshop Design)..."
                               value="">
                    </div>
                    <p class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        يمكنك إضافة حتى 15 مهارة
                    </p>
                </div>

                {{-- Suggested Skills --}}
                <div class="mb-8 bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500 mb-3 font-semibold flex items-center gap-2">
                        <span class="text-brand-magenta">✨</span> مهارات شائعة قد تتقنها:
                    </p>
                    <div class="flex flex-wrap gap-2" id="suggestions-list">
                        @foreach($skills->take(12) as $skill)
                        <button type="button" 
                                class="suggested-skill-btn group relative inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-sm font-medium rounded-full text-gray-600 bg-white hover:border-brand-teal hover:text-brand-teal hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-teal"
                                data-skill-name="{{ $skill->name }}">
                            <span class="mr-1 group-hover:block hidden transition-all duration-200 text-brand-teal">+</span>
                            {{ $skill->name_ar ?? $skill->name }}
                        </button>
                        @endforeach
                    </div>
                </div>

                @error('skills_json')
                    <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                            </div>
                        </div>
                    </div>
                @enderror

                <div class="mt-8">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-gradient-to-r from-brand-teal to-teal-600 hover:from-teal-600 hover:to-brand-teal focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-teal transform transition hover:-translate-y-0.5 hover:shadow-xl">
                        متابعة إلى لوحة التحكم ←
                    </button>
                </div>
            </form>
        </div>
        
        <p class="text-center text-gray-400 text-sm mt-6">
            © {{ date('Y') }} {{ __('auth.PLATFORM_NAME', [], app()->getLocale()) }}. جميع الحقوق محفوظة.
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.querySelector('input[name=skills_json]');
        
        // Initialize Tagify
        var tagify = new Tagify(input, {
            whitelist: [
                @foreach($skills as $skill)
                    "{{ $skill->name }}",
                @endforeach
            ],
            maxTags: 15,
            dropdown: {
                maxItems: 20,           // <- mixumum allowed rendered suggestions
                classname: "tags-look", // <- custom classname for this dropdown, so it could be targeted
                enabled: 0,             // <- show suggestions on focus
                closeOnSelect: false    // <- do not hide the suggestions dropdown once an item has been selected
            },
            originalInputValueFormat: valuesArr => JSON.stringify(valuesArr)
        });

        // Handle Suggested Skills Click
        document.querySelectorAll('.suggested-skill-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                var skillName = this.dataset.skillName;
                tagify.addTags([skillName]);
                
                // Optional: visual feedback or remove button from suggestions
                this.classList.add('opacity-50', 'cursor-not-allowed');
                this.disabled = true;
            });
        });

        // Re-enable suggestion button if tag is removed
        tagify.on('remove', function(e){
            var removedTag = e.detail.data.value;
            var btn = document.querySelector(`.suggested-skill-btn[data-skill-name="${removedTag}"]`);
            if(btn) {
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.disabled = false;
            }
        });
    });
</script>
@endsection

