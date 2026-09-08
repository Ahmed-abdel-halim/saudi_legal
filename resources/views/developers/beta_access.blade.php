@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@section('content')
<div class="min-h-screen bg-[#0b1120] text-slate-100 py-16 px-4 sm:px-6 lg:px-8" dir="{{ $direction }}">
    <div class="max-w-4xl mx-auto pt-8">

        {{-- Top Badge & Navigation Back --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('developers.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand-green transition">
                <i class="fa-solid {{ $direction === 'rtl' ? 'fa-arrow-right' : 'fa-arrow-left' }}"></i>
                <span>{{ $currentLang === 'en' ? 'Back to API Documentation' : 'العودة لتوثيق الـ API' }}</span>
            </a>
            <span class="text-xs px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">
                ● Beta Access Program
            </span>
        </div>

        {{-- Success Notification with Instant Sandbox Key --}}
        @if(session('success'))
            <div class="mb-10 p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-emerald-950/60 to-slate-900 border border-emerald-500/40 shadow-2xl relative overflow-hidden">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-2xl shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-black text-white mb-2">{{ session('success') }}</h3>
                        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed mb-4">
                            تم تفعيل مفتاح تجريبي فوري (Sandbox Key) يمكنك استخدامه مباشرة لاختبار دوال الـ API داخل بيئة التطوير. سيقوم فريقنا بمراجعة حسابك لمنحك مفتاح الـ Production الكامل.
                        </p>

                        @if(session('sandbox_key'))
                            <div class="mt-4 p-4 rounded-xl bg-slate-950/90 border border-emerald-500/30">
                                <label class="text-[11px] font-bold text-emerald-400 block mb-1">مفتاح التطوير التجريبي المخصص لك (Sandbox Key):</label>
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="{{ session('sandbox_key') }}" id="sandbox-key-input"
                                           class="w-full bg-transparent text-xs font-mono text-slate-200 border-none focus:ring-0 select-all p-0">
                                    <button onclick="copySandboxKey()" class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 rounded-lg text-xs font-bold transition flex items-center gap-1 shrink-0">
                                        <i class="fa-regular fa-copy"></i>
                                        <span id="copy-btn-text">نسخ</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('developers.index') }}" class="px-5 py-2.5 bg-emerald-500 text-dark-navy font-bold rounded-xl text-xs hover:bg-emerald-400 transition">
                                <i class="fa-solid fa-terminal mr-1"></i> تجربة الأكواد في التوثيق
                            </a>
                            <a href="{{ route('legal_assistant.public') }}" class="px-5 py-2.5 bg-slate-800 text-slate-200 font-bold rounded-xl text-xs hover:bg-slate-700 transition">
                                <i class="fa-solid fa-robot mr-1"></i> تجربة المساعد القانوني
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function copySandboxKey() {
                    const input = document.getElementById('sandbox-key-input');
                    if (!input) return;
                    navigator.clipboard.writeText(input.value).then(() => {
                        document.getElementById('copy-btn-text').innerText = 'تم النسخ!';
                        setTimeout(() => { document.getElementById('copy-btn-text').innerText = 'نسخ'; }, 2000);
                    });
                }
            </script>
        @endif

        {{-- Header Intro --}}
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl sm:text-4xl font-black text-white mb-4">
                {{ $currentLang === 'en' ? 'Request Developer Beta Access' : 'طلب الانضمام للنسخة التجريبية للمطورين والشركات' }}
            </h1>
            <p class="text-slate-400 text-sm leading-relaxed">
                {{ $currentLang === 'en' 
                    ? 'Get early API access, higher rate limits, and direct technical consultation to embed Radiif Legal AI into your software.' 
                    : 'احصل على وصول مبكر لواجهة برمجة التطبيقات، مع معدلات استعلام موسعة ودعم فني مخصص لربط محرك البحث القانوني بنظامك.' }}
            </p>
        </div>

        {{-- Application Form Card --}}
        <div class="bg-gradient-to-b from-slate-900/90 to-dark-card/90 rounded-3xl p-6 sm:p-10 border border-slate-800/80 shadow-2xl backdrop-blur-xl">
            @if (isset($errors) && $errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-950/40 border border-rose-800/50 text-rose-300 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('developers.beta.submit') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Full Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            {{ $currentLang === 'en' ? 'Full Name / Developer Name' : 'الاسم الكامل للمطور أو المسؤول' }} <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="name" required value="{{ old('name', auth()->user()->name ?? '') }}"
                               class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition"
                               placeholder="مثال: م. فهد الشمري">
                    </div>

                    {{-- Work Email --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            {{ $currentLang === 'en' ? 'Work Email' : 'البريد الإلكتروني للعمل' }} <span class="text-rose-400">*</span>
                        </label>
                        <input type="email" name="email" required value="{{ old('email', auth()->user()->email ?? '') }}"
                               class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition"
                               placeholder="name@company.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Company / Firm Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            {{ $currentLang === 'en' ? 'Company or Law Firm Name' : 'اسم المنشأة أو مكتب المحاماة' }}
                        </label>
                        <input type="text" name="company" value="{{ old('company') }}"
                               class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition"
                               placeholder="مثال: شركة المحاماة والاستشارات التقنية">
                    </div>

                    {{-- Phone Number --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            {{ $currentLang === 'en' ? 'Phone Number / WhatsApp' : 'رقم الجوال / واتساب' }}
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition"
                               placeholder="+966 50 123 4567">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Organization Type --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            {{ $currentLang === 'en' ? 'Organization Type' : 'طبيعة الجهة أو النشاط' }} <span class="text-rose-400">*</span>
                        </label>
                        <select name="organization_type" required
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition">
                            <option value="legaltech" {{ old('organization_type') == 'legaltech' ? 'selected' : '' }}>شركة تقنية قانونية (LegalTech)</option>
                            <option value="law_firm" {{ old('organization_type') == 'law_firm' ? 'selected' : '' }}>مكتب محاماة أو استشارات قانونية</option>
                            <option value="enterprise" {{ old('organization_type') == 'enterprise' ? 'selected' : '' }}>شركة أو مؤسسة كبرى (Enterprise)</option>
                            <option value="developer" {{ old('organization_type') == 'developer' ? 'selected' : '' }}>مطور مستقل أو باحث ذكاء اصطناعي</option>
                            <option value="other" {{ old('organization_type') == 'other' ? 'selected' : '' }}>أخرى</option>
                        </select>
                    </div>

                    {{-- Expected Monthly Volume --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            {{ $currentLang === 'en' ? 'Expected Monthly Queries' : 'حجم الاستعلامات الشهري المتوقع' }} <span class="text-rose-400">*</span>
                        </label>
                        <select name="expected_volume" required
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition">
                            <option value="under_10k" {{ old('expected_volume') == 'under_10k' ? 'selected' : '' }}>أقل من 10,000 استعلام / شهر</option>
                            <option value="10k_to_100k" {{ old('expected_volume') == '10k_to_100k' ? 'selected' : '' }}>10,000 إلى 100,000 استعلام / شهر</option>
                            <option value="over_100k" {{ old('expected_volume') == 'over_100k' ? 'selected' : '' }}>أكثر من 100,000 استعلام / شهر (مؤسسي)</option>
                        </select>
                    </div>
                </div>

                {{-- Tech Stack --}}
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-2">
                        {{ $currentLang === 'en' ? 'Integration Technology / Tech Stack' : 'التقنيات المستخدمة في الربط (Tech Stack)' }}
                    </label>
                    <input type="text" name="tech_stack" value="{{ old('tech_stack') }}"
                           class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition"
                           placeholder="مثال: Python / FastAPI, Laravel / PHP, Node.js, Next.js, Salesforce">
                </div>

                {{-- Use Case Description --}}
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-2">
                        {{ $currentLang === 'en' ? 'Describe Your Planned Use Case' : 'وصف حالة الاستخدام وخطة الربط' }} <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="use_case" rows="4" required
                              class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-brand-green transition"
                              placeholder="مثال: نرغب في إضافة مساعد ذكي يبحث في سوابق المحاكم العمالية والتجارية داخل بوابة عملائنا لتقديم ردود موثقة فورية...">{{ old('use_case') }}</textarea>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button type="submit"
                            class="w-full py-4 bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black rounded-2xl shadow-green-glow hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer text-base">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>{{ $currentLang === 'en' ? 'Submit Application & Get Sandbox Key' : 'إرسال الطلب والحصول على المفتاح التجريبي' }}</span>
                    </button>
                    <p class="text-[11px] text-center text-slate-500 mt-3 font-medium">
                        🛡️ بياناتك ومشاريعك محمية وتخضع لسياسة سرية المعلومات الخاصة بمنصة رديف.
                    </p>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection
