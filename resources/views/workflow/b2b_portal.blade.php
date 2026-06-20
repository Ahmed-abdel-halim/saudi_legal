<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> AI Claims Orchestrator & Auditor | منسق ومدقق مطالبات التأمين</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Tajawal:wght@400;500;700;900&display=swap"
        rel="stylesheet">
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
            border-color: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .glow-green {
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.15);
        }

        .glow-yellow {
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.15);
        }

        .glow-red {
            box-shadow: 0 0 25px rgba(239, 68, 68, 0.15);
        }

        .animated-stripes {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.05) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.05) 50%, rgba(255, 255, 255, 0.05) 75%, transparent 75%, transparent);
            background-size: 40px 40px;
            animation: move-stripes 2s linear infinite;
        }

        @keyframes move-stripes {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 40px 0;
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data="{ 
        locale: localStorage.getItem('b2b_locale') || 'en', 
        activeTab: 'all', 
        selectedTask: null,
        loadPreset(type) {
            if (type === 'green') {
                document.getElementById('patient_name').value = 'Aisha Al-Subaie';
                document.getElementById('patient_gender').value = 'Female';
                document.getElementById('patient_age').value = '28';
                document.getElementById('patient_national_id').value = '1098765432';
                document.getElementById('patient_phone').value = '+966551212121';
                document.getElementById('patient_email').value = 'aisha@gmail.com';
                document.getElementById('cpt_code').value = '99213';
                document.getElementById('claimed_amount').value = '150.00';
                document.getElementById('icd_10_code').value = 'R51';
                document.getElementById('clinical_notes').value = this.locale === 'ar' ? 'استشارة عيادات خارجية قياسية لصداع التوتر.' : 'Standard outpatient consultation for tension headaches.';
                document.getElementById('simulated_semantic_score').value = '0.94';
                document.getElementById('simulated_llm_score').value = '0.96';
                document.getElementById('is_duplicate_flag').checked = false;
            } else if (type === 'yellow') {
                document.getElementById('patient_name').value = 'Bandar Al-Otaibi';
                document.getElementById('patient_gender').value = 'Male';
                document.getElementById('patient_age').value = '52';
                document.getElementById('patient_national_id').value = '1087654321';
                document.getElementById('patient_phone').value = '+966504567890';
                document.getElementById('patient_email').value = 'bandar@outlook.com';
                document.getElementById('cpt_code').value = '70450';
                document.getElementById('claimed_amount').value = '1450.00';
                document.getElementById('icd_10_code').value = 'G44';
                document.getElementById('clinical_notes').value = this.locale === 'ar' ? 'طلب تصوير معقد خلال فترة زمنية قصيرة، يتطلب تدقيق تجاوز الطبيب السريري.' : 'Complex imaging request within short window, requiring clinician override audit.';
                document.getElementById('simulated_semantic_score').value = '0.65';
                document.getElementById('simulated_llm_score').value = '0.78';
                document.getElementById('is_duplicate_flag').checked = false;
            } else if (type === 'red') {
                document.getElementById('patient_name').value = 'Fahad Al-Harbi';
                document.getElementById('patient_gender').value = 'Male';
                document.getElementById('patient_age').value = '31';
                document.getElementById('patient_national_id').value = '1065432109';
                document.getElementById('patient_phone').value = '+966547654321';
                document.getElementById('patient_email').value = 'fahad@yahoo.com';
                document.getElementById('cpt_code').value = '59400';
                document.getElementById('claimed_amount').value = '5000.00';
                document.getElementById('icd_10_code').value = 'O60';
                document.getElementById('clinical_notes').value = this.locale === 'ar' ? 'ترميز مطالبة مريض ذكر برمز ولادة أمراض النساء والتوليد.' : 'Male patient billed with obstetric pregnancy delivery code.';
                document.getElementById('simulated_semantic_score').value = '0.40';
                document.getElementById('simulated_llm_score').value = '0.45';
                document.getElementById('is_duplicate_flag').checked = false;
            }
        }
    }"
    x-init="$watch('locale', val => localStorage.setItem('b2b_locale', val))"
    :dir="locale === 'ar' ? 'rtl' : 'ltr'" :class="locale === 'ar' ? 'font-tajawal' : 'font-sans'"
    class="text-slate-200 min-h-screen antialiased pb-12 transition-all duration-300">

    <!-- Top Glow Header -->
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center">

            <!-- Logo & Brand Title -->
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-extrabold text-2xl shadow-indigo-500/30 shadow-lg">
                    <i class="fa-solid fa-microchip-ai"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white leading-none">
                        <span x-show="locale === 'en'">AI Claims Orchestrator & Auditor</span>
                        <span x-show="locale === 'ar'">مطالبات التأمين</span>
                    </h1>
                    <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest block mt-1">
                        <span x-show="locale === 'en'">Polymorphic AI Claims Orchestrator</span>
                        <span x-show="locale === 'ar'">منسق مطالبات الذكاء الاصطناعي متعدد الأشكال</span>
                    </span>
                </div>
            </div>

            <!-- Global Perspective Selector -->
            <div class="flex bg-slate-950/80 p-1.5 rounded-xl border border-slate-800 gap-1.5">
                <a href="?role=hospital"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeRole === 'hospital' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                    <i class="fa-solid fa-hospital-user text-xs"></i>
                    <span>
                        <span x-show="locale === 'en'">Hospital</span>
                        <span x-show="locale === 'ar'">المستشفى</span>
                    </span>
                </a>
                <a href="?role=payer"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeRole === 'payer' ? 'bg-teal-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>
                        <span x-show="locale === 'en'">Insurance Payer</span>
                        <span x-show="locale === 'ar'">الدافع التأميني</span>
                    </span>
                </a>
                <a href="?role=doctor"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeRole === 'doctor' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                    <i class="fa-solid fa-user-doctor text-xs"></i>
                    <span>
                        <span x-show="locale === 'en'">HITL Doctor</span>
                        <span x-show="locale === 'ar'">طبيب التدقيق</span>
                    </span>
                </a>
            </div>

            <!-- Language Switcher & Reset Workspace -->
            <div class="flex items-center gap-3">
                <!-- Language Toggle Button -->
                <button type="button" @click="locale = (locale === 'en' ? 'ar' : 'en')"
                    class="px-4 py-2 text-xs font-bold bg-indigo-950/40 text-indigo-400 border border-indigo-900/50 hover:bg-indigo-900 hover:text-white rounded-lg transition-all flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-language text-sm"></i>
                    <span class="font-semibold" x-text="locale === 'en' ? 'العربية' : 'English'"></span>
                </button>

                <form action="{{ route('workflow.reset') }}" method="POST"
                    onsubmit="return confirm(locale === 'en' ? 'Reset all demo claims?' : 'إعادة تعيين جميع مطالبات العرض التوضيحي؟');">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-xs font-bold bg-rose-950/40 text-rose-400 border border-rose-900/50 hover:bg-rose-900 hover:text-white rounded-lg transition-all flex items-center gap-2">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <span>
                            <span x-show="locale === 'en'">Reset Workspace</span>
                            <span x-show="locale === 'ar'">إعادة تعيين المساحة</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Top Alert Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div
                class="bg-emerald-950/50 border border-emerald-800 text-emerald-300 p-4 rounded-2xl flex items-center gap-3 shadow-lg glow-green animate-pulse">
                <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div
                class="bg-rose-950/50 border border-rose-800 text-rose-300 p-4 rounded-2xl flex items-center gap-3 shadow-lg glow-red">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-xl"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        <!-- Scaffolding Container Summary Dashboard Stats -->
        <section class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <div class="glass-card p-6 rounded-3xl relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-10 text-slate-500 text-7xl p-4 pointer-events-none">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <span x-show="locale === 'en'">Global Clearing Settlement</span>
                    <span x-show="locale === 'ar'">تسوية المقاصة العالمية</span>
                </h3>
                <div class="text-3xl font-extrabold text-white mt-2">
                    {{ number_format($stats['clearing_pool'], 2) }}
                    <span class="text-sm text-slate-400 font-semibold">
                        <span x-show="locale === 'en'">SAR</span>
                        <span x-show="locale === 'ar'">ر.س</span>
                    </span>
                </div>
                <p class="text-[10px] text-emerald-400 font-semibold mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i>
                    <span x-show="locale === 'en'">Credited Settlement Pool (Auto-Approved)</span>
                    <span x-show="locale === 'ar'">مجمع التسويات المعتمد (موافقة تلقائية)</span>
                </p>
            </div>

            <div class="glass-card p-6 rounded-3xl border-s-4 border-emerald-500 relative overflow-hidden glow-green">
                <div class="absolute right-0 bottom-0 opacity-10 text-emerald-500 text-7xl p-4 pointer-events-none">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-widest">
                    <span x-show="locale === 'en'">🟢 Auto-Adjudications</span>
                    <span x-show="locale === 'ar'">🟢 أحكام تلقائية</span>
                </h3>
                <div class="text-3xl font-extrabold text-white mt-2">{{ $stats['green'] }}</div>
                <p class="text-[10px] text-slate-400 mt-1">
                    <span x-show="locale === 'en'">Confidence score &ge; 0.90</span>
                    <span x-show="locale === 'ar'">مستوى الثقة &ge; 0.90</span>
                </p>
            </div>

            <div class="glass-card p-6 rounded-3xl border-s-4 border-amber-500 relative overflow-hidden glow-yellow">
                <div class="absolute right-0 bottom-0 opacity-10 text-amber-500 text-7xl p-4 pointer-events-none">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-widest">
                    <span x-show="locale === 'en'">🟡 Clinical Audits (HITL)</span>
                    <span x-show="locale === 'ar'">🟡 تدقيق سريري (بشري)</span>
                </h3>
                <div class="text-3xl font-extrabold text-white mt-2">{{ $stats['yellow'] }}</div>
                <p class="text-[10px] text-slate-400 mt-1">
                    <span x-show="locale === 'en'">0.60 &le; Confidence &lt; 0.90</span>
                    <span x-show="locale === 'ar'">0.60 &le; الثقة &lt; 0.90</span>
                </p>
            </div>

            <div class="glass-card p-6 rounded-3xl border-s-4 border-rose-500 relative overflow-hidden glow-red">
                <div class="absolute right-0 bottom-0 opacity-10 text-rose-500 text-7xl p-4 pointer-events-none">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-xs font-bold text-rose-400 uppercase tracking-widest">
                    <span x-show="locale === 'en'">🔴 Fraud Isolated (SIU)</span>
                    <span x-show="locale === 'ar'">🔴 عزل الاحتيال (SIU)</span>
                </h3>
                <div class="text-3xl font-extrabold text-white mt-2">{{ $stats['red'] }}</div>
                <p class="text-[10px] text-slate-400 mt-1">
                    <span x-show="locale === 'en'">Confidence &lt; 0.60</span>
                    <span x-show="locale === 'ar'">الثقة &lt; 0.60</span>
                </p>
            </div>

        </section>

        <!-- Dynamic Workspace Panel based on activeRole -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Left Side: Role Specific Controls (Lighter Card) -->
            <div class="lg:col-span-4 space-y-6">

                @if($activeRole === 'hospital')
                    <!-- Hospital Submit Panel -->
                    <div class="glass-card p-6 rounded-3xl space-y-4">
                        <div class="flex items-center gap-2 text-indigo-400 font-extrabold text-lg">
                            <i class="fa-solid fa-file-arrow-up"></i>
                            <h2>
                                <span x-show="locale === 'en'">Submit Claim Payload</span>
                                <span x-show="locale === 'ar'">تقديم بيانات المطالبة</span>
                            </h2>
                        </div>
                        <p class="text-xs text-slate-400">
                            <span x-show="locale === 'en'">Healthcare facilities (Submitters) post HL7 FHIR compliant JSON
                                datasets requesting payer credit settlement.</span>
                            <span x-show="locale === 'ar'">تقوم المنشآت الصحية (المقدمون) بنشر مجموعات بيانات JSON متوافقة
                                مع HL7 FHIR لطلب تسوية رصيد الجهة الدافعة.</span>
                        </p>

                        <!-- Presets Selector -->
                        <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-2">
                            <span class="block text-[10px] font-extrabold uppercase text-slate-400">
                                <span x-show="locale === 'en'">Simulate Quick Presets:</span>
                                <span x-show="locale === 'ar'">محاكاة سريعة مسبقة الضبط:</span>
                            </span>
                            <div class="flex gap-1.5">
                                <button type="button" @click="loadPreset('green')"
                                    class="flex-1 py-1.5 text-[10px] bg-emerald-950/60 border border-emerald-900 text-emerald-400 rounded-lg hover:bg-emerald-900 hover:text-white transition-all font-bold">
                                    <span x-show="locale === 'en'">🟢 Auto-Approve</span>
                                    <span x-show="locale === 'ar'">🟢 موافقة تلقائية</span>
                                </button>
                                <button type="button" @click="loadPreset('yellow')"
                                    class="flex-1 py-1.5 text-[10px] bg-amber-950/60 border border-amber-900 text-amber-400 rounded-lg hover:bg-amber-900 hover:text-white transition-all font-bold font-bold">
                                    <span x-show="locale === 'en'">🟡 HITL Audit</span>
                                    <span x-show="locale === 'ar'">🟡 تدقيق بشري</span>
                                </button>
                                <button type="button" @click="loadPreset('red')"
                                    class="flex-1 py-1.5 text-[10px] bg-rose-950/60 border border-rose-900 text-rose-400 rounded-lg hover:bg-rose-900 hover:text-white transition-all font-bold font-bold font-bold">
                                    <span x-show="locale === 'en'">🔴 Fraud Trigger</span>
                                    <span x-show="locale === 'ar'">🔴 كشف احتيال</span>
                                </button>
                            </div>
                        </div>

                        <!-- Claim submission form -->
                        <form action="{{ route('workflow.upload_claim') }}" method="POST" class="space-y-3">
                            @csrf

                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                    <span x-show="locale === 'en'">Patient Name (PHI)</span>
                                    <span x-show="locale === 'ar'">اسم المريض (معلومات صحية محمية)</span>
                                </label>
                                <input type="text" id="patient_name" name="patient_name" required
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">Gender</span>
                                        <span x-show="locale === 'ar'">الجنس</span>
                                    </label>
                                    <select id="patient_gender" name="patient_gender" required
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                        <option value="Female">
                                            <span x-show="locale === 'en'">Female</span>
                                            <span x-show="locale === 'ar'">أنثى</span>
                                        </option>
                                        <option value="Male">
                                            <span x-show="locale === 'en'">Male</span>
                                            <span x-show="locale === 'ar'">ذكر</span>
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">Age</span>
                                        <span x-show="locale === 'ar'">العمر</span>
                                    </label>
                                    <input type="number" id="patient_age" name="patient_age" required
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">National ID (PHI)</span>
                                        <span x-show="locale === 'ar'">رقم الهوية (معلومات محمية)</span>
                                    </label>
                                    <input type="text" id="patient_national_id" name="patient_national_id" required
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">Phone (PHI)</span>
                                        <span x-show="locale === 'ar'">رقم الهاتف (معلومات محمية)</span>
                                    </label>
                                    <input type="text" id="patient_phone" name="patient_phone" required
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                    <span x-show="locale === 'en'">Email Address (PHI)</span>
                                    <span x-show="locale === 'ar'">البريد الإلكتروني (معلومات محمية)</span>
                                </label>
                                <input type="email" id="patient_email" name="patient_email" required
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">CPT Procedure</span>
                                        <span x-show="locale === 'ar'">إجراء CPT</span>
                                    </label>
                                    <select id="cpt_code" name="cpt_code" required
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                        <option value="99213">99213 - Outpatient Visit</option>
                                        <option value="70450">70450 - CT Scan Head</option>
                                        <option value="93000">93000 - ECG trace</option>
                                        <option value="59400">59400 - Vaginal Delivery</option>
                                        <option value="59510">59510 - Cesarean Delivery</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">Claim Cost (SAR)</span>
                                        <span x-show="locale === 'ar'">تكلفة المطالبة (ر.س)</span>
                                    </label>
                                    <input type="number" id="claimed_amount" name="claimed_amount" required step="0.01"
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                        <span x-show="locale === 'en'">ICD-10 Diagnosis</span>
                                        <span x-show="locale === 'ar'">تشخيص ICD-10</span>
                                    </label>
                                    <select id="icd_10_code" name="icd_10_code" required
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                        <option value="R51">R51 - Headache / Migraine</option>
                                        <option value="I10">I10 - Essential Hypertension</option>
                                        <option value="O30">O30 - Multiple Gestation</option>
                                        <option value="O60">O60 - Preterm Labor</option>
                                    </select>
                                </div>
                                <div class="flex items-center pt-5 ps-2">
                                    <input type="checkbox" id="is_duplicate_flag" name="is_duplicate_flag" value="1"
                                        class="w-4 h-4 text-indigo-600 bg-slate-950 border-slate-800 rounded focus:ring-indigo-500">
                                    <label for="is_duplicate_flag" class="ms-2 text-xs font-bold text-slate-400">
                                        <span x-show="locale === 'en'">Force Duplicate Flag</span>
                                        <span x-show="locale === 'ar'">فرض علامة التكرار</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                    <span x-show="locale === 'en'">Clinical Documentation</span>
                                    <span x-show="locale === 'ar'">التوثيق السريري</span>
                                </label>
                                <textarea id="clinical_notes" name="clinical_notes" rows="2"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                            </div>

                            <div class="bg-slate-950 p-3 rounded-2xl border border-slate-900 space-y-2">
                                <span class="block text-[9px] font-bold text-indigo-400">
                                    <span x-show="locale === 'en'">POLYMORPHIC CONFIDENCE INJECTION (For Testing):</span>
                                    <span x-show="locale === 'ar'">حقن الثقة متعدد الأشكال (للاختبار):</span>
                                </span>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[8px] text-slate-400">
                                            <span x-show="locale === 'en'">Semantic Similar (R)</span>
                                            <span x-show="locale === 'ar'">التشابه الدلالي (R)</span>
                                        </label>
                                        <input type="number" id="simulated_semantic_score" name="simulated_semantic_score"
                                            step="0.01" min="0" max="1" value="0.88"
                                            class="w-full bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-xs text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[8px] text-slate-400">
                                            <span x-show="locale === 'en'">LLM Certainty (L)</span>
                                            <span x-show="locale === 'ar'">يقين النموذج اللغوي (L)</span>
                                        </label>
                                        <input type="number" id="simulated_llm_score" name="simulated_llm_score" step="0.01"
                                            min="0" max="1" value="0.93"
                                            class="w-full bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-xs text-white">
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/20 active:scale-95">
                                <i class="fa-solid fa-paper-plane me-2"></i>
                                <span x-show="locale === 'en'">Submit to AI Orchestrator</span>
                                <span x-show="locale === 'ar'">إرسال إلى منسق الذكاء الاصطناعي</span>
                            </button>
                        </form>
                    </div>
                @endif

                @if($activeRole === 'payer')
                    <!-- Payer Policy Rules Configurations -->
                    <div class="glass-card p-6 rounded-3xl space-y-4">
                        <div class="flex items-center gap-2 text-teal-400 font-extrabold text-lg">
                            <i class="fa-solid fa-sliders"></i>
                            <h2>
                                <span x-show="locale === 'en'">Adjust Policy Constraints</span>
                                <span x-show="locale === 'ar'">تعديل قيود السياسة</span>
                            </h2>
                        </div>
                        <p class="text-xs text-slate-400">
                            <span x-show="locale === 'en'">Tawuniya Insurance (Payers) define policy caps that block
                                deterministic rule checks (B = 0) if claims exceed bounds.</span>
                            <span x-show="locale === 'ar'">تحدد شركة التعاونية (الدافعون) حدود السياسة التي تحظر فحوصات
                                القواعد الحتمية (B = 0) إذا تجاوزت المطالبات الحدود.</span>
                        </p>

                        <form action="{{ route('workflow.payer_policy') }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                    <span x-show="locale === 'en'">CPT 99213 Cap (Outpatient)</span>
                                    <span x-show="locale === 'ar'">حد CPT 99213 (زيارة خارجية)</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="number" name="cpt_cap_99213" value="{{ $ruleCaps['99213'] }}" required
                                        class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none">
                                    <span
                                        class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-400 flex items-center">
                                        <span x-show="locale === 'en'">SAR</span>
                                        <span x-show="locale === 'ar'">ر.س</span>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                    <span x-show="locale === 'en'">CPT 70450 Cap (CT Scan Head)</span>
                                    <span x-show="locale === 'ar'">حد CPT 70450 (أشعة مقطعية للرأس)</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="number" name="cpt_cap_70450" value="{{ $ruleCaps['70450'] }}" required
                                        class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none">
                                    <span
                                        class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-400 flex items-center">
                                        <span x-show="locale === 'en'">SAR</span>
                                        <span x-show="locale === 'ar'">ر.س</span>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                    <span x-show="locale === 'en'">CPT 93000 Cap (ECG)</span>
                                    <span x-show="locale === 'ar'">حد CPT 93000 (تخطيط القلب)</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="number" name="cpt_cap_93000" value="{{ $ruleCaps['93000'] }}" required
                                        class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none">
                                    <span
                                        class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-400 flex items-center">
                                        <span x-show="locale === 'en'">SAR</span>
                                        <span x-show="locale === 'ar'">ر.س</span>
                                    </span>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg active:scale-95">
                                <i class="fa-solid fa-save me-2"></i>
                                <span x-show="locale === 'en'">Update Policy Limits</span>
                                <span x-show="locale === 'ar'">تحديث حدود السياسة</span>
                            </button>
                        </form>
                    </div>
                @endif

                @if($activeRole === 'doctor')
                    <!-- Doctor Micro-Wallet Profile Card -->
                    <div class="glass-card p-6 rounded-3xl relative overflow-hidden glow-yellow">
                        <div class="absolute right-0 bottom-0 opacity-10 text-amber-500 text-7xl p-4 pointer-events-none">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-lg">
                                {{ substr($doctor->name ?? 'D', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">
                                    <span x-show="locale === 'en'">{{ $doctor->name ?? 'Dr. Sarah (HITL Auditor)' }}</span>
                                    <span x-show="locale === 'ar'">د. سارة (مدقق إكلينيكي بشري)</span>
                                </h3>
                                <span
                                    class="text-[9px] text-amber-400 font-extrabold uppercase tracking-widest block mt-0.5">
                                    <span x-show="locale === 'en'">Crowdsourced Medical Auditor</span>
                                    <span x-show="locale === 'ar'">مدقق طبي خارجي مشارك</span>
                                </span>
                            </div>
                        </div>

                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <span x-show="locale === 'en'">Independent Micro-Wallet</span>
                            <span x-show="locale === 'ar'">المحفظة الرقمية المستقلة</span>
                        </span>
                        <!-- Wallet Reward Balance Indicator -->
                        <div class="text-3xl font-extrabold text-white mt-1 flex items-baseline gap-2">
                            <span>{{ number_format($doctor->wallet_balance ?? 0.00, 2) }}</span>
                            <span class="text-xs text-slate-400">
                                <span x-show="locale === 'en'">SAR</span>
                                <span x-show="locale === 'ar'">ر.س</span>
                            </span>
                        </div>

                        <div class="mt-4 p-3 bg-slate-950 rounded-2xl border border-slate-900 text-[10px] text-slate-400">
                            <i class="fa-solid fa-circle-info text-amber-500 me-1.5"></i>
                            <span x-show="locale === 'en'">Doctors audit yellow-flagged claims (PHI-scrubbed) and earn 75.00
                                SAR per completed transaction.</span>
                            <span x-show="locale === 'ar'">يقوم الأطباء بتدقيق المطالبات المصنفة باللون الأصفر (مع تنقية
                                معلومات الهوية) وكسب 75.00 ر.س لكل معاملة مكتملة.</span>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Side: Polymorphic Data List & Details (8 Columns) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Shared Scaffolding List View Header -->
                <div class="glass-card rounded-3xl p-6 space-y-6">

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-indigo-600/20 text-indigo-400 flex items-center justify-center">
                                <i class="fa-solid fa-table-list"></i>
                            </div>
                            <h2 class="text-lg font-bold text-white">
                                <span x-show="locale === 'en'">Ingested Transactions Database</span>
                                <span x-show="locale === 'ar'">قاعدة بيانات المعاملات المستلمة</span>
                            </h2>
                        </div>

                        <!-- Sub-tab filtering inside list container -->
                        <div class="flex bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs">
                            <button @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-slate-800 text-white font-bold' : 'text-slate-400'"
                                class="px-3 py-1.5 rounded-lg transition-all">
                                <span x-show="locale === 'en'">All Tasks</span>
                                <span x-show="locale === 'ar'">جميع المهام</span>
                            </button>
                            @if($activeRole === 'doctor')
                                <button @click="activeTab = 'queue'"
                                    :class="activeTab === 'queue' ? 'bg-amber-600 text-white font-bold' : 'text-slate-400'"
                                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1">
                                    <span>
                                        <span x-show="locale === 'en'">Doctor Audits</span>
                                        <span x-show="locale === 'ar'">تدقيقات الأطباء</span>
                                    </span>
                                    <span
                                        class="bg-slate-950/40 text-[9px] px-1 rounded-md">{{ $doctorQueue->count() }}</span>
                                </button>
                            @endif
                            @if($activeRole === 'payer')
                                <button @click="activeTab = 'siu'"
                                    :class="activeTab === 'siu' ? 'bg-rose-950 border border-rose-800 text-rose-300 font-bold' : 'text-slate-400'"
                                    class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1">
                                    <span>
                                        <span x-show="locale === 'en'">SIU Fraud Esc</span>
                                        <span x-show="locale === 'ar'">تصعيد احتيال SIU</span>
                                    </span>
                                    <span
                                        class="bg-rose-500/20 text-rose-300 text-[9px] px-1 rounded-md">{{ $siuClaims->count() }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Dynamic Representation Grids swap based on active tab/claims list -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-start border-collapse text-slate-300">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 text-xs font-semibold">
                                    <th class="pb-3 ps-2 text-start">
                                        <span x-show="locale === 'en'">Task ID / Type</span>
                                        <span x-show="locale === 'ar'">معرف / نوع المهمة</span>
                                    </th>
                                    <th class="pb-3 text-start">
                                        <span x-show="locale === 'en'">Submitter / Payer</span>
                                        <span x-show="locale === 'ar'">الجهة المقدمة / الدافع</span>
                                    </th>
                                    <th class="pb-3 text-center font-bold">
                                        <span x-show="locale === 'en'">Confidence</span>
                                        <span x-show="locale === 'ar'">مستوى الثقة</span>
                                    </th>
                                    <th class="pb-3 text-center">
                                        <span x-show="locale === 'en'">Status</span>
                                        <span x-show="locale === 'ar'">الحالة</span>
                                    </th>
                                    <th class="pb-3 text-end">
                                        <span x-show="locale === 'en'">Cost (SAR)</span>
                                        <span x-show="locale === 'ar'">التكلفة (ر.س)</span>
                                    </th>
                                    <th class="pb-3 text-center">
                                        <span x-show="locale === 'en'">Action</span>
                                        <span x-show="locale === 'ar'">الإجراء</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-sm">

                                <!-- No tasks state -->
                                @if($allTasks->isEmpty())
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500">
                                            <i class="fa-solid fa-inbox text-4xl block mb-2"></i>
                                            <span x-show="locale === 'en'">No claim tasks submitted. Use the Submitter panel
                                                to post.</span>
                                            <span x-show="locale === 'ar'">لا توجد مهام مطالبات مرسلة. استخدم لوحة التقديم
                                                للنشر.</span>
                                        </td>
                                    </tr>
                                @endif

                                <!-- All Tasks Loop -->
                                @foreach($allTasks as $t)
                                    <tr x-show="activeTab === 'all' || (activeTab === 'queue' && {{ $t->status_code }} === 2) || (activeTab === 'siu' && {{ $t->status_code }} === 3)"
                                        class="hover:bg-slate-900/40 transition-colors group cursor-pointer"
                                        @click="selectedTask = {{ json_encode($t) }}">
                                        <td class="py-3.5 ps-2">
                                            <div
                                                class="font-semibold text-white group-hover:text-indigo-400 transition-colors">
                                                #{{ substr($t->task_id, 0, 8) }}
                                            </div>
                                            <span
                                                class="text-[9px] text-slate-400 uppercase bg-slate-950 px-1.5 py-0.5 rounded border border-slate-800">
                                                {{ $t->task_type }}
                                            </span>
                                        </td>
                                        <td class="py-3.5">
                                            <div class="text-xs text-slate-300">
                                                <span x-show="locale === 'en'">Submitter: KFSH</span>
                                                <span x-show="locale === 'ar'">المقدم: مستشفى الملك فيصل</span>
                                            </div>
                                            <div class="text-[10px] text-slate-500">
                                                <span x-show="locale === 'en'">Payer: Tawuniya</span>
                                                <span x-show="locale === 'ar'">الدافع: التعاونية</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 text-center">
                                            <div
                                                class="font-semibold text-xs {{ $t->confidence_score >= 0.90 ? 'text-emerald-400' : ($t->confidence_score >= 0.60 ? 'text-amber-400' : 'text-rose-400') }}">
                                                {{ number_format($t->confidence_score * 100, 0) }}%
                                            </div>
                                            <div class="w-12 bg-slate-800 h-1.5 rounded-full mx-auto overflow-hidden mt-1">
                                                <div class="h-full {{ $t->confidence_score >= 0.90 ? 'bg-emerald-500' : ($t->confidence_score >= 0.60 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                    style="width: {{ $t->confidence_score * 100 }}%"></div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 text-center">
                                            @if($t->status_code === 1)
                                                <span
                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-900 px-2 py-0.5 rounded-full glow-green">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                    <span>
                                                        <span x-show="locale === 'en'">Approved</span>
                                                        <span x-show="locale === 'ar'">معتمد</span>
                                                    </span>
                                                </span>
                                            @elseif($t->status_code === 2)
                                                <span
                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-400 bg-amber-950/60 border border-amber-900 px-2 py-0.5 rounded-full glow-yellow">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                                    <span>
                                                        <span x-show="locale === 'en'">In Audit</span>
                                                        <span x-show="locale === 'ar'">قيد التدقيق</span>
                                                    </span>
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-400 bg-rose-950/60 border border-rose-900 px-2 py-0.5 rounded-full glow-red">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                    <span>
                                                        <span x-show="locale === 'en'">Fraud SIU</span>
                                                        <span x-show="locale === 'ar'">احتيال SIU</span>
                                                    </span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 text-end font-semibold text-white">
                                            {{ number_format($t->payload['claimed_amount'] ?? $t->original_payload['claimed_amount'] ?? 0, 2) }}
                                        </td>
                                        <td class="py-3.5 text-center">
                                            <button
                                                class="px-2.5 py-1 text-[10px] font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md transition-all border border-slate-750"
                                                @click.stop="selectedTask = {{ json_encode($t) }}">
                                                <span x-show="locale === 'en'">Details</span>
                                                <span x-show="locale === 'ar'">التفاصيل</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Shared Container Workspace details preview panel (when task selected) -->
                <div x-show="selectedTask" x-cloak class="glass-card rounded-3xl p-6 relative overflow-hidden"
                    :class="selectedTask.status_code === 1 ? 'border-t-4 border-emerald-500 glow-green' : (selectedTask.status_code === 2 ? 'border-t-4 border-amber-500 glow-yellow' : 'border-t-4 border-rose-500 glow-red')">
                    <!-- Close button -->
                    <button @click="selectedTask = null"
                        class="absolute right-4 top-4 text-slate-500 hover:text-white text-lg transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <div class="space-y-6">

                        <!-- Header with dynamic signaling -->
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div class="text-start">
                                <span class="text-[9px] uppercase font-bold tracking-widest"
                                    :class="selectedTask.status_code === 1 ? 'text-emerald-400' : (selectedTask.status_code === 2 ? 'text-amber-400' : 'text-rose-400')">
                                    <span x-show="selectedTask.status_code === 1">
                                        <span x-show="locale === 'en'">🟢 Greenfield Path (Approved)</span>
                                        <span x-show="locale === 'ar'">🟢 المسار الأخضر (مقبول تلقائياً)</span>
                                    </span>
                                    <span x-show="selectedTask.status_code === 2">
                                        <span x-show="locale === 'en'">🟡 Yellow path (In-Review)</span>
                                        <span x-show="locale === 'ar'">🟡 المسار الأصفر (قيد التدقيق والتحقق
                                            البشري)</span>
                                    </span>
                                    <span x-show="selectedTask.status_code === 3">
                                        <span x-show="locale === 'en'">🔴 Red Path (SIU Isolated)</span>
                                        <span x-show="locale === 'ar'">🔴 المسار الأحمر (معزول للتحقيق في
                                            الاحتيال)</span>
                                    </span>
                                </span>
                                <h2 class="text-xl font-bold text-white mt-1">
                                    <span x-show="locale === 'en'">Claim Task #</span>
                                    <span x-show="locale === 'ar'">مهمة المطالبة #</span>
                                    <span x-text="selectedTask.task_id.substring(0,18)"></span>
                                </h2>
                            </div>
                            <div class="text-end">
                                <span class="block text-[10px] text-slate-400 uppercase">
                                    <span x-show="locale === 'en'">AI Score Confidence</span>
                                    <span x-show="locale === 'ar'">ثقة تقييم الذكاء الاصطناعي</span>
                                </span>
                                <span class="text-xl font-extrabold text-white"
                                    x-text="Math.round(selectedTask.confidence_score * 100) + '%'"></span>
                            </div>
                        </div>

                        <!-- Grid Representation of Polymorphic Payload -->
                        <div>
                            <h3
                                class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1.5 text-start">
                                <i class="fa-solid fa-database text-indigo-400"></i>
                                <span>
                                    <span x-show="locale === 'en'">Dynamic Payload Details (Polymorphic Grid)</span>
                                    <span x-show="locale === 'ar'">تفاصيل الحمولة الديناميكية (شبكة متعددة
                                        الأشكال)</span>
                                </span>
                            </h3>

                            <!-- Render claim variables grid -->
                            <div
                                class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-950 p-4 rounded-2xl border border-slate-800 relative text-start">

                                <!-- Anonymization Watermark overlay -->
                                <template x-if="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor'">
                                    <div
                                        class="absolute inset-0 border border-amber-500/30 rounded-2xl pointer-events-none animated-stripes flex items-center justify-center opacity-30">
                                    </div>
                                </template>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">Patient Name</span>
                                        <span x-show="locale === 'ar'">اسم المريض</span>
                                    </span>
                                    <!-- Rendered with PHI lock if anonymized -->
                                    <span class="font-bold text-sm"
                                        :class="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor' ? 'text-amber-400' : 'text-white'">
                                        <i x-show="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor'"
                                            class="fa-solid fa-user-lock text-[10px] me-1"></i>
                                        <span x-text="selectedTask.payload.patient_name || '[REDACTED]'"></span>
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">National ID</span>
                                        <span x-show="locale === 'ar'">رقم الهوية</span>
                                    </span>
                                    <span class="font-bold text-sm"
                                        :class="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor' ? 'text-amber-400' : 'text-white'">
                                        <i x-show="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor'"
                                            class="fa-solid fa-id-card-clip text-[10px] me-1"></i>
                                        <span x-text="selectedTask.payload.patient_national_id || '[REDACTED]'"></span>
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">Contact / Phone</span>
                                        <span x-show="locale === 'ar'">رقم الاتصال / الهاتف</span>
                                    </span>
                                    <span class="font-bold text-sm"
                                        :class="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor' ? 'text-amber-400' : 'text-white'">
                                        <i x-show="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor'"
                                            class="fa-solid fa-lock text-[10px] me-1"></i>
                                        <span x-text="selectedTask.payload.patient_phone || '[REDACTED]'"></span>
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">Gender / Age</span>
                                        <span x-show="locale === 'ar'">الجنس / العمر</span>
                                    </span>
                                    <span class="font-bold text-white text-sm">
                                        <span
                                            x-text="selectedTask.payload.patient_gender === 'Female' ? (locale === 'ar' ? 'أنثى' : 'Female') : (locale === 'ar' ? 'ذكر' : 'Male')"></span>
                                        / <span x-text="selectedTask.payload.patient_age"></span>
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">CPT Code</span>
                                        <span x-show="locale === 'ar'">رمز إجراء CPT</span>
                                    </span>
                                    <span class="font-bold text-indigo-400 text-sm font-mono"
                                        x-text="selectedTask.payload.cpt_code"></span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">ICD-10 Diagnosis</span>
                                        <span x-show="locale === 'ar'">تشخيص ICD-10</span>
                                    </span>
                                    <span class="font-bold text-indigo-400 text-sm font-mono"
                                        x-text="selectedTask.payload.icd_10_code"></span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">Submitting Cost</span>
                                        <span x-show="locale === 'ar'">تكلفة تقديم الطلب</span>
                                    </span>
                                    <span class="font-bold text-emerald-400 text-sm"
                                        x-text="selectedTask.payload.claimed_amount + (locale === 'ar' ? ' ر.س' : ' SAR')"></span>
                                </div>

                                <div>
                                    <span class="block text-[10px] text-slate-500 font-semibold uppercase">
                                        <span x-show="locale === 'en'">Task Type</span>
                                        <span x-show="locale === 'ar'">نوع المهمة</span>
                                    </span>
                                    <span class="font-bold text-slate-300 text-xs font-mono"
                                        x-text="selectedTask.task_type"></span>
                                </div>

                            </div>
                        </div>

                        <!-- Payer SIU Panel (Visible for RED Path claims to Payers) -->
                        <template x-if="selectedTask.status_code === 3 && '{{ $activeRole }}' === 'payer'">
                            <div
                                class="bg-rose-950/40 p-5 rounded-2xl border border-rose-800 space-y-3 glow-red animate-pulse text-start">
                                <div class="flex items-center gap-2 text-rose-400 font-extrabold text-sm">
                                    <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg"></i>
                                    <h4>
                                        <span x-show="locale === 'en'">Payer SIU Investigation Report (Un-Scrubbed
                                            File)</span>
                                        <span x-show="locale === 'ar'">تقرير تحقيقات وحدة التحقيقات الخاصة (SIU) للدافع
                                            (ملف غير منقى)</span>
                                    </h4>
                                </div>
                                <p class="text-xs text-slate-300">
                                    <span x-show="locale === 'en'">Confidence limits fell below 0.60. Detailed data
                                        isolated from the crowdsourced network and flagged for the Internal Special
                                        Investigations Unit (SIU) admin review.</span>
                                    <span x-show="locale === 'ar'">انخفاض مستوى الثقة لأقل من 0.60. تم عزل البيانات
                                        الكاملة لملف المريض وتوجيهها للفحص والتدقيق من قبل وحدة التحقيقات الخاصة
                                        (SIU).</span>
                                </p>

                                <div
                                    class="bg-slate-950 p-4 rounded-xl border border-slate-900 text-xs font-semibold space-y-1">
                                    <div class="text-slate-400">
                                        <span x-show="locale === 'en'">UN-SCRUBBED PATIENT PROFILE:</span>
                                        <span x-show="locale === 'ar'">ملف تعريف المريض الكامل غير المنقى:</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                        <div>
                                            <span class="text-slate-500">
                                                <span x-show="locale === 'en'">FullName:</span>
                                                <span x-show="locale === 'ar'">الاسم الكامل:</span>
                                            </span>
                                            <span class="text-white"
                                                x-text="selectedTask.original_payload.patient_name"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500">
                                                <span x-show="locale === 'en'">National ID:</span>
                                                <span x-show="locale === 'ar'">رقم الهوية:</span>
                                            </span>
                                            <span class="text-white"
                                                x-text="selectedTask.original_payload.patient_national_id"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500">
                                                <span x-show="locale === 'en'">Phone Contact:</span>
                                                <span x-show="locale === 'ar'">رقم الهاتف:</span>
                                            </span>
                                            <span class="text-white"
                                                x-text="selectedTask.original_payload.patient_phone"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500">
                                                <span x-show="locale === 'en'">Email Address:</span>
                                                <span x-show="locale === 'ar'">البريد الإلكتروني:</span>
                                            </span>
                                            <span class="text-white"
                                                x-text="selectedTask.original_payload.patient_email"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Rule engine logs -->
                        <div class="space-y-2 text-start">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                <span x-show="locale === 'en'">Rule engine audit trail log</span>
                                <span x-show="locale === 'ar'">سجل تتبع تدقيق محرك القواعد</span>
                            </h4>
                            <div
                                class="bg-slate-950 rounded-2xl p-4 border border-slate-900 space-y-2.5 max-h-48 overflow-y-auto">
                                <template x-for="log in selectedTask.audit_trail.rule_engine_logs" :key="log.rule">
                                    <div
                                        class="flex items-start justify-between text-xs py-1 border-b border-slate-900 last:border-0 gap-3">
                                        <div>
                                            <span class="font-mono text-indigo-400 font-bold block"
                                                x-text="log.rule"></span>
                                            <span class="text-slate-400" x-text="locale === 'ar' ? 
                                                (log.rule === 'GenderCompatibility' ? 
                                                    (log.status === 'PASSED' ? 'الجنس متوافق مع رموز إجراءات التوليد وأمراض النساء.' : 'فشل التوافق: الرموز محجوزة للإناث والمريض ذكر.') : 
                                                 log.rule === 'PriceCapEnforcement' ? 
                                                    (log.status === 'PASSED' ? 'تكلفة الإجراء تقع ضمن الحد الأقصى المعين للسياسة.' : 'تجاوز التكلفة: المطالبة تتعدى سقف السياسة المعين.') :
                                                 log.rule === 'TemporalDuplicateCheck' ? 
                                                    (log.status === 'PASSED' ? 'لم يتم رصد مطالبات مكررة مطابقة لهذا المريض خلال 24 ساعة الماضية.' : 'فشل مكرر: توجد مطالبة مماثلة للمريض في آخر 24 ساعة.') : log.message
                                                ) : log.message"></span>
                                        </div>
                                        <div>
                                            <span class="px-2 py-0.5 rounded font-extrabold text-[9px]"
                                                :class="log.status === 'PASSED' ? 'bg-emerald-950/60 text-emerald-400 border border-emerald-900' : 'bg-rose-950/60 text-rose-400 border border-rose-900'"
                                                x-text="log.status === 'PASSED' ? (locale === 'ar' ? 'ناجح' : 'PASSED') : (locale === 'ar' ? 'فشل' : 'FAILED')"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Doctor Resolve Workbench form inside detail panel if doctor perspective and YELLOW status -->
                        <template x-if="selectedTask.status_code === 2 && '{{ $activeRole }}' === 'doctor'">
                            <div class="bg-slate-950/60 p-5 rounded-3xl border border-slate-800 space-y-4 text-start">
                                <div class="flex items-center gap-2 text-amber-400 font-bold text-sm">
                                    <i class="fa-solid fa-stethoscope"></i>
                                    <h4>
                                        <span x-show="locale === 'en'">Resolve Verification Audit & Credit
                                            micro-wallet</span>
                                        <span x-show="locale === 'ar'">حسم تدقيق التحقق وشحن المحفظة المصغرة</span>
                                    </h4>
                                </div>

                                <form action="{{ route('workflow.doctor_resolve') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="task_id" :value="selectedTask.task_id">

                                    <div class="flex flex-col sm:flex-row gap-4">
                                        <label
                                            class="flex-1 bg-slate-900 border border-slate-800 p-4 rounded-2xl flex items-center justify-between cursor-pointer hover:border-emerald-500 transition-all">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" name="action" value="Approve" required
                                                    class="w-4 h-4 text-emerald-500 bg-slate-950 border-slate-800">
                                                <span class="font-bold text-emerald-400 text-sm">
                                                    <span x-show="locale === 'en'">Adjudicate / Approve</span>
                                                    <span x-show="locale === 'ar'">حكم بالموافقة والتمرير</span>
                                                </span>
                                            </div>
                                            <span class="text-[9px] text-slate-500 font-bold uppercase">
                                                <span x-show="locale === 'en'">Greenlight settlement</span>
                                                <span x-show="locale === 'ar'">تصفية التسوية</span>
                                            </span>
                                        </label>

                                        <label
                                            class="flex-1 bg-slate-900 border border-slate-800 p-4 rounded-2xl flex items-center justify-between cursor-pointer hover:border-rose-500 transition-all">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" name="action" value="Deny" required
                                                    class="w-4 h-4 text-rose-500 bg-slate-950 border-slate-800">
                                                <span class="font-bold text-rose-400 text-sm">
                                                    <span x-show="locale === 'en'">Escalate / Deny</span>
                                                    <span x-show="locale === 'ar'">تصعيد ورفض</span>
                                                </span>
                                            </div>
                                            <span class="text-[9px] text-slate-500 font-bold uppercase">
                                                <span x-show="locale === 'en'">Flag for SIU fraud</span>
                                                <span x-show="locale === 'ar'">إرسال للاحتيال</span>
                                            </span>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">
                                            <span x-show="locale === 'en'">Clinical Decision Justification</span>
                                            <span x-show="locale === 'ar'">تبرير القرار الإكلينيكي</span>
                                        </label>
                                        <textarea name="comment" rows="2" required
                                            placeholder="Write clinical justification audit notes..."
                                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                                    </div>

                                    <button type="submit"
                                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>
                                            <span x-show="locale === 'en'">Commit Audit Decision & Earn 75.00 SAR</span>
                                            <span x-show="locale === 'ar'">اعتماد قرار التدقيق وكسب 75.00 ر.س</span>
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </template>

                        <!-- Display Doctor's decision if resolved -->
                        <template x-if="selectedTask.doctor_response">
                            <div
                                class="bg-slate-950 p-4 rounded-2xl border border-slate-900 text-xs space-y-2 text-start">
                                <div class="flex justify-between text-slate-400">
                                    <span class="font-bold text-amber-500 uppercase tracking-widest text-[9px]">
                                        <span x-show="locale === 'en'">HITL Auditor Audit Complete</span>
                                        <span x-show="locale === 'ar'">اكتمل تدقيق المراجع السريري البشري</span>
                                    </span>
                                    <span x-text="'Completed: ' + selectedTask.doctor_completed_at"></span>
                                </div>
                                <div class="text-white text-start">
                                    <span class="text-slate-500">
                                        <span x-show="locale === 'en'">Auditor decision:</span>
                                        <span x-show="locale === 'ar'">قرار المدقق السريري:</span>
                                    </span>
                                    <span
                                        class="font-bold px-2 py-0.5 rounded bg-slate-900 border border-slate-800 ms-1.5"
                                        :class="selectedTask.doctor_response === 'Approve' ? 'text-emerald-400' : 'text-rose-400'"
                                        x-text="selectedTask.doctor_response === 'Approve' ? (locale === 'ar' ? 'موافقة وتمرير' : 'Approve') : (locale === 'ar' ? 'رفض وتصعيد' : 'Deny')"></span>
                                </div>
                                <div class="text-white text-start">
                                    <span class="text-slate-500">
                                        <span x-show="locale === 'en'">Justification:</span>
                                        <span x-show="locale === 'ar'">مبررات القرار:</span>
                                    </span>
                                    <span class="italic text-slate-300 ms-1.5"
                                        x-text="selectedTask.doctor_comment"></span>
                                </div>
                                <div class="text-white text-start">
                                    <span class="text-slate-500">
                                        <span x-show="locale === 'en'">Micro-reward earned:</span>
                                        <span x-show="locale === 'ar'">المكافأة المصغرة المستحقة:</span>
                                    </span>
                                    <span class="font-bold text-yellow-400 ms-1.5"
                                        x-text="selectedTask.reward_amount + (locale === 'ar' ? ' ر.س' : ' SAR')"></span>
                                </div>
                            </div>
                        </template>

                    </div>

                </div>

            </div>

        </section>

    </main>

</body>

</html>