<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    @include('partials.google-analytics')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Insurance Payer | اختيار شركة التأمين</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Tajawal', 'sans-serif'],
                        tajawal: ['Tajawal', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #030712;
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0, transparent 50%),
                radial-gradient(at 50% 0%, rgba(239, 68, 68, 0.05) 0, transparent 50%);
            background-attachment: fixed;
        }

        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.07);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.15);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data="{ locale: localStorage.getItem('b2b_locale') || 'en' }"
    x-init="$watch('locale', val => localStorage.setItem('b2b_locale', val))"
    :dir="locale === 'ar' ? 'rtl' : 'ltr'" :class="locale === 'ar' ? 'font-tajawal' : 'font-sans'"
    class="text-slate-200 min-h-screen antialiased pb-12 transition-all duration-300">

    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center">
            
            <!-- Logo & Brand Title -->
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-extrabold text-2xl shadow-indigo-500/30 shadow-lg">
                    <i class="fa-solid fa-microchip-ai"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white leading-none">
                        <span x-show="locale === 'en'">AI Claims Orchestrator & Auditor</span>
                        <span x-show="locale === 'ar'">مطالبات التأمين</span>
                    </h1>
                    <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest block mt-1">
                        <span x-show="locale === 'en'">Hospital Provider Panel</span>
                        <span x-show="locale === 'ar'">بوابة مقدم الرعاية الطبية (المستشفى)</span>
                    </span>
                </div>
            </div>

            <!-- Language Switcher & Back to Dashboard -->
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 text-xs font-bold bg-slate-950/40 text-slate-400 border border-slate-900/50 hover:bg-slate-900 hover:text-white rounded-lg transition-all flex items-center gap-2">
                    <i class="fa-solid fa-house"></i>
                    <span>
                        <span x-show="locale === 'en'">Dashboard</span>
                        <span x-show="locale === 'ar'">لوحة التحكم</span>
                    </span>
                </a>

                <button type="button" @click="locale = (locale === 'en' ? 'ar' : 'en')"
                    class="px-4 py-2 text-xs font-bold bg-indigo-950/40 text-indigo-400 border border-indigo-900/50 hover:bg-indigo-900 hover:text-white rounded-lg transition-all flex items-center gap-2">
                    <i class="fa-solid fa-language text-sm"></i>
                    <span class="font-semibold" x-text="locale === 'en' ? 'العربية' : 'English'"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 text-center">
        
        <!-- Welcome Title -->
        <div class="mb-10 max-w-2xl mx-auto">
            <span class="px-3 py-1 bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-[10px] font-black uppercase tracking-widest rounded-full">
                <span x-show="locale === 'en'">Claims Routing Portal</span>
                <span x-show="locale === 'ar'">بوابة توجيه مطالبات التأمين</span>
            </span>
            <h2 class="text-3xl font-black text-white mt-4 tracking-tight">
                <span x-show="locale === 'en'">Select Target Insurance Company</span>
                <span x-show="locale === 'ar'">اختر شركة التأمين المستهدفة</span>
            </h2>
            <p class="text-slate-400 text-sm mt-3 leading-relaxed">
                <span x-show="locale === 'en'">Choose the insurance payer you want to submit medical claim payloads to for AI automated rules audit and settlement verification.</span>
                <span x-show="locale === 'ar'">اختر الجهة الدافعة للتأمين التي ترغب في إرسال مطالبات التأمين الطبية إليها لتدقيق القواعد الآلي والتحقق من التسويات.</span>
            </p>
        </div>

        <!-- Payers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto mt-6 text-start">
            @foreach($insuranceCompanies as $company)
                @php
                    // Set color themes based on company name
                    $colorClass = 'indigo';
                    $logoChar = 'INS';
                    
                    if (str_contains($company->name, 'Tawuniya')) {
                        $colorClass = 'emerald';
                        $logoChar = 'T';
                    } elseif (str_contains($company->name, 'Bupa')) {
                        $colorClass = 'sky';
                        $logoChar = 'B';
                    } elseif (str_contains($company->name, 'Rajhi')) {
                        $colorClass = 'indigo';
                        $logoChar = 'R';
                    } elseif (str_contains($company->name, 'Medgulf')) {
                        $colorClass = 'teal';
                        $logoChar = 'M';
                    }
                @endphp
                
                <a href="{{ route('workflow.portal', ['role' => 'hospital', 'payer_id' => $company->company_id]) }}" 
                   class="glass-card p-6 rounded-3xl flex flex-col justify-between h-72 relative overflow-hidden group">
                    
                    <!-- Decorative background glow -->
                    <div class="absolute -right-10 -bottom-10 w-24 h-24 bg-{{ $colorClass }}-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    
                    <div>
                        <!-- Header with Icon and Verified Badge -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-{{ $colorClass }}-500/15 border border-{{ $colorClass }}-500/35 text-{{ $colorClass }}-400 flex items-center justify-center font-black text-xl shadow-lg">
                                <span>{{ $logoChar }}</span>
                            </div>
                            
                            @if($company->is_verified_provider)
                                <span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[8px] font-bold rounded-full uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i>
                                    <span x-show="locale === 'en'">Verified</span>
                                    <span x-show="locale === 'ar'">موثق</span>
                                </span>
                            @endif
                        </div>

                        <!-- Company Title -->
                        <h3 class="text-lg font-bold text-white group-hover:text-{{ $colorClass }}-400 transition-colors">
                            @if(str_contains($company->name, 'Tawuniya'))
                                <span x-show="locale === 'en'">Tawuniya Insurance</span>
                                <span x-show="locale === 'ar'">التعاونية للتأمين</span>
                            @elseif(str_contains($company->name, 'Bupa'))
                                <span x-show="locale === 'en'">Bupa Arabia</span>
                                <span x-show="locale === 'ar'">بوبا العربية</span>
                            @elseif(str_contains($company->name, 'Rajhi'))
                                <span x-show="locale === 'en'">Al Rajhi Takaful</span>
                                <span x-show="locale === 'ar'">تكافل الراجحي</span>
                            @elseif(str_contains($company->name, 'Medgulf'))
                                <span x-show="locale === 'en'">Medgulf Insurance</span>
                                <span x-show="locale === 'ar'">ميدغلف للتأمين</span>
                            @else
                                <span>{{ $company->name }}</span>
                            @endif
                        </h3>
                        
                        <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                            @if(str_contains($company->name, 'Tawuniya'))
                                <span x-show="locale === 'en'">Leading insurer in Saudi Arabia, supporting automated Green-path clearances.</span>
                                <span x-show="locale === 'ar'">الشركة الرائدة للتأمين في المملكة العربية السعودية، تدعم خلوص المسار الأخضر التلقائي.</span>
                            @elseif(str_contains($company->name, 'Bupa'))
                                <span x-show="locale === 'en'">Specialized healthcare insurance provider with high-performance SLA routing.</span>
                                <span x-show="locale === 'ar'">مزود متخصص في التأمين الصحي مع توجيه اتفاقية مستوى الخدمة عالي الأداء.</span>
                            @elseif(str_contains($company->name, 'Rajhi'))
                                <span x-show="locale === 'en'">Islamic compliant cooperative insurance solutions with clinical review audit.</span>
                                <span x-show="locale === 'ar'">حلول تأمين تعاوني متوافقة مع الشريعة الإسلامية مع تدقيق المراجعة السريرية.</span>
                            @elseif(str_contains($company->name, 'Medgulf'))
                                <span x-show="locale === 'en'">Comprehensive medical network coverage for corporate and family segments.</span>
                                <span x-show="locale === 'ar'">تغطية شبكة طبية شاملة لقطاعات الشركات والعائلات.</span>
                            @else
                                <span x-show="locale === 'en'">Verified national insurance provider for B2B automated claims routing.</span>
                                <span x-show="locale === 'ar'">مزود تأمين وطني معتمد لتوجيه مطالبات التأمين الآلية للشركات.</span>
                            @endif
                        </p>
                    </div>

                    <!-- Footer Action / Select -->
                    <div class="mt-4 pt-4 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400 group-hover:text-white transition-colors">
                        <span class="font-semibold uppercase tracking-widest text-[9px] text-slate-500 group-hover:text-{{ $colorClass }}-400">
                            <span x-show="locale === 'en'">Size: {{ ucfirst($company->size ?? 'Large') }}</span>
                            <span x-show="locale === 'ar'">الحجم: {{ $company->size == 'medium' ? 'متوسط' : 'كبير' }}</span>
                        </span>
                        
                        <div class="flex items-center gap-1 text-{{ $colorClass }}-400 font-bold hover:underline">
                            <span x-show="locale === 'en'">Select</span>
                            <span x-show="locale === 'ar'">اختيار</span>
                            <i class="fa-solid fa-chevron-right text-[10px] rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </main>

</body>

</html>
