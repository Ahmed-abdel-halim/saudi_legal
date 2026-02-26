@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@section('content')
{{-- Hero Header --}}
<div class="bg-dark-navy text-white py-16" dir="{{ $direction }}">
    <div class="container mx-auto px-6">
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ $currentLang === 'ar' ? 'الرئيسية' : 'Home' }}</a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <a href="{{ route('legal.terms') }}" class="hover:text-white transition-colors">{{ $currentLang === 'ar' ? 'قانوني' : 'Legal' }}</a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-white">{{ $currentLang === 'ar' ? 'اتفاقية عدم الإفصاح' : 'NDA' }}</span>
        </div>

        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0 mt-1">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <h1 class="text-4xl font-extrabold mb-2">
                    {{ $currentLang === 'ar' ? 'اتفاقية عدم الإفصاح (NDA)' : 'Non-Disclosure Agreement (NDA)' }}
                </h1>
                <p class="text-gray-400 text-sm">
                    {{ $currentLang === 'ar' ? 'آخر تحديث' : 'Last updated' }}: {{ date("Y-m-d") }}
                    &nbsp;·&nbsp;
                    {{ $currentLang === 'ar' ? 'سارية المفعول على جميع العقود المبرمة عبر منصة رديف' : 'Applies to all contracts via Radiif platform' }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Content --}}
<div class="py-16 bg-slate-50" dir="{{ $direction }}">
    <div class="container mx-auto px-6 max-w-4xl">

        {{-- Notice Banner --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-10 flex gap-4">
            <svg class="flex-shrink-0 mt-0.5" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <p class="text-indigo-800 text-sm leading-relaxed">
                {{ $currentLang === 'ar'
                    ? 'هذه الاتفاقية ملزمة قانونيًا لجميع أطراف التعاقد عبر منصة رديف. يُعدّ الاستخدام المستمر للمنصة قبولًا صريحًا لكافة بنود هذه الاتفاقية.'
                    : 'This agreement is legally binding for all contracting parties through the Radiif platform. Continued use of the platform constitutes explicit acceptance of all terms of this agreement.'
                }}
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 space-y-10 leading-relaxed text-gray-700">

            {{-- 1. التعريفات --}}
            <section>
                <h2 class="text-2xl font-bold text-dark-navy mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black flex-shrink-0">1</span>
                    {{ $currentLang === 'ar' ? 'التعريفات والمصطلحات' : 'Definitions & Terms' }}
                </h2>
                <p class="mb-4">{{ $currentLang === 'ar' ? 'في إطار هذه الاتفاقية، تحمل المصطلحات التالية المعاني المبيّنة قرين كلٍّ منها:' : 'Within this agreement, the following terms carry the meanings indicated next to each:' }}</p>
                <ul class="space-y-3">
                    <li class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mt-2 flex-shrink-0"></span>
                        <span><strong>{{ $currentLang === 'ar' ? 'المنصة:' : 'Platform:' }}</strong> {{ $currentLang === 'ar' ? 'منصة «رديف» الإلكترونية بجميع خدماتها وأدواتها.' : 'The "Radiif" platform with all its services and tools.' }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mt-2 flex-shrink-0"></span>
                        <span><strong>{{ $currentLang === 'ar' ? 'الطرف المُفصِح:' : 'Disclosing Party:' }}</strong> {{ $currentLang === 'ar' ? 'الشركة أو المؤسسة أو الفرد الذي يُطلع الطرف الآخر على المعلومات السرية.' : 'The company, institution, or individual who discloses confidential information to the other party.' }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mt-2 flex-shrink-0"></span>
                        <span><strong>{{ $currentLang === 'ar' ? 'الطرف المُتلقِّي:' : 'Receiving Party:' }}</strong> {{ $currentLang === 'ar' ? 'الشركة أو المؤسسة أو الخبير الذي يتلقّى المعلومات السرية.' : 'The company, institution, or expert who receives confidential information.' }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mt-2 flex-shrink-0"></span>
                        <span><strong>{{ $currentLang === 'ar' ? 'المعلومات السرية:' : 'Confidential Information:' }}</strong> {{ $currentLang === 'ar' ? 'تشمل بيانات الأعمال، الخوارزميات، مجموعات البيانات، المشاريع، الاستراتيجيات، بيانات العملاء، والمعلومات التقنية الخاصة.' : 'Includes business data, algorithms, datasets, projects, strategies, client data, and proprietary technical information.' }}</span>
                    </li>
                </ul>
            </section>

            {{-- 2. التزامات السرية --}}
            <section>
                <h2 class="text-2xl font-bold text-dark-navy mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black flex-shrink-0">2</span>
                    {{ $currentLang === 'ar' ? 'التزامات السرية' : 'Confidentiality Obligations' }}
                </h2>
                <p class="mb-4">{{ $currentLang === 'ar' ? 'يتعهد الطرف المُتلقِّي بما يلي:' : 'The receiving party commits to the following:' }}</p>
                <ul class="space-y-3">
                    @php $obligations = $currentLang === 'ar' ? [
                        'عدم الإفصاح عن أي معلومات سرية لأي طرف ثالث دون موافقة خطية مسبقة من الطرف المُفصِح.',
                        'استخدام المعلومات السرية حصرًا للأغراض المتفق عليها في عقد التعاون.',
                        'اتخاذ كافة الإجراءات الاحترازية اللازمة لحماية سرية المعلومات بمستوى لا يقل عن الحماية المعتادة لمعلوماته الخاصة.',
                        'إبلاغ الطرف المُفصِح فورًا في حال اكتشاف أي خرق أو تسريب محتمل.',
                        'عدم استخدام المعلومات السرية لأغراض تنافسية أو لصالح منافسي الطرف المُفصِح.',
                    ] : [
                        'Not disclose any confidential information to any third party without prior written consent from the disclosing party.',
                        'Use confidential information exclusively for the purposes agreed upon in the cooperation contract.',
                        'Take all necessary precautionary measures to protect the confidentiality of information at a level no less than the usual protection of its own information.',
                        'Immediately notify the disclosing party upon discovering any breach or potential leak.',
                        'Not use confidential information for competitive purposes or for the benefit of the disclosing party\'s competitors.',
                    ] @endphp
                    @foreach($obligations as $item)
                    <li class="flex gap-3">
                        <svg class="flex-shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </section>

            {{-- 3. الاستثناءات --}}
            <section>
                <h2 class="text-2xl font-bold text-dark-navy mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black flex-shrink-0">3</span>
                    {{ $currentLang === 'ar' ? 'الاستثناءات من السرية' : 'Exceptions to Confidentiality' }}
                </h2>
                <p class="mb-3">{{ $currentLang === 'ar' ? 'لا تنطبق التزامات السرية على المعلومات التي:' : 'Confidentiality obligations do not apply to information that:' }}</p>
                <ul class="space-y-2">
                    @php $exceptions = $currentLang === 'ar' ? [
                        'كانت معروفة للعموم قبل الإفصاح.',
                        'أصبحت متاحة للعموم دون إخلال من الطرف المُتلقِّي.',
                        'حصل عليها الطرف المُتلقِّي بشكل مستقل دون الاستناد إلى المعلومات السرية.',
                        'يُلزَم الطرف المُتلقِّي بالإفصاح عنها بموجب أمر قضائي أو حكومي، شريطة إخطار الطرف المُفصِح فورًا.',
                    ] : [
                        'Was known to the public before disclosure.',
                        'Became publicly available without breach by the receiving party.',
                        'Was independently obtained by the receiving party without relying on confidential information.',
                        'The receiving party is required to disclose by judicial or governmental order, provided the disclosing party is notified immediately.',
                    ] @endphp
                    @foreach($exceptions as $item)
                    <li class="flex gap-3 text-gray-600">
                        <span class="w-2 h-2 rounded-full bg-gray-400 mt-2 flex-shrink-0"></span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </section>

            {{-- 4. مدة الاتفاقية --}}
            <section>
                <h2 class="text-2xl font-bold text-dark-navy mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black flex-shrink-0">4</span>
                    {{ $currentLang === 'ar' ? 'مدة الاتفاقية' : 'Agreement Duration' }}
                </h2>
                <p>{{ $currentLang === 'ar'
                    ? 'تسري هذه الاتفاقية طوال فترة التعاون بين الأطراف، وتستمر لمدة خمس (5) سنوات بعد انتهاء العلاقة التعاقدية. تظل الالتزامات المتعلقة بالمعلومات التي تُصنَّف كأسرار تجارية سارية إلى أجل غير مسمى.'
                    : 'This agreement is valid throughout the period of cooperation between the parties, and continues for five (5) years after the contractual relationship ends. Obligations related to information classified as trade secrets remain in effect indefinitely.'
                }}</p>
            </section>

            {{-- 5. العقوبات --}}
            <section>
                <h2 class="text-2xl font-bold text-dark-navy mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-sm font-black flex-shrink-0">5</span>
                    {{ $currentLang === 'ar' ? 'المسؤولية والعقوبات' : 'Liability & Penalties' }}
                </h2>
                <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                    <p class="text-red-800">{{ $currentLang === 'ar'
                        ? 'يُحق للطرف المُفصِح في حال الإخلال بأي من بنود السرية المطالبةُ بالتعويض عن جميع الأضرار المباشرة وغير المباشرة الناتجة عن هذا الإخلال، بما في ذلك الخسائر التجارية وخسارة الميزة التنافسية. كما يحق للمنصة إيقاف حساب الطرف المخالف فورًا وإلغاء عقوده القائمة.'
                        : 'In the event of a breach of any confidentiality provisions, the disclosing party is entitled to claim compensation for all direct and indirect damages resulting from this breach, including commercial losses and loss of competitive advantage. The platform also has the right to immediately suspend the violating party\'s account and cancel its existing contracts.'
                    }}</p>
                </div>
            </section>

            {{-- 6. القانون الحاكم --}}
            <section>
                <h2 class="text-2xl font-bold text-dark-navy mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black flex-shrink-0">6</span>
                    {{ $currentLang === 'ar' ? 'القانون الحاكم وتسوية النزاعات' : 'Governing Law & Dispute Resolution' }}
                </h2>
                <p>{{ $currentLang === 'ar'
                    ? 'تخضع هذه الاتفاقية لأحكام نظام التجارة الإلكترونية ونظام حماية البيانات الشخصية في المملكة العربية السعودية. تُحال النزاعات أولًا إلى الوساطة، وفي حال تعذّر حلّها تُرفع إلى المحاكم التجارية المختصة في المملكة العربية السعودية.'
                    : 'This agreement is governed by the provisions of the E-Commerce Law and the Personal Data Protection Law in Saudi Arabia. Disputes are first referred to mediation, and if unresolvable, are submitted to the competent commercial courts in Saudi Arabia.'
                }}</p>
            </section>

        </div>

        {{-- Related Legal Pages --}}
        <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4" dir="{{ $direction }}">
            <a href="{{ route('legal.terms') }}" class="block bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-md transition-all group">
                <div class="flex items-center gap-3 mb-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span class="font-bold text-dark-navy text-sm group-hover:text-indigo-600 transition-colors">{{ $currentLang === 'ar' ? 'شروط الخدمة' : 'Terms of Service' }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ $currentLang === 'ar' ? 'الشروط الحاكمة لاستخدام المنصة' : 'Platform usage governing terms' }}</p>
            </a>
            <a href="{{ route('legal.privacy') }}" class="block bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-md transition-all group">
                <div class="flex items-center gap-3 mb-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span class="font-bold text-dark-navy text-sm group-hover:text-indigo-600 transition-colors">{{ $currentLang === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ $currentLang === 'ar' ? 'كيفية معالجة بياناتك' : 'How your data is handled' }}</p>
            </a>
            <a href="{{ route('legal.msa') }}" class="block bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-md transition-all group">
                <div class="flex items-center gap-3 mb-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    <span class="font-bold text-dark-navy text-sm group-hover:text-indigo-600 transition-colors">{{ $currentLang === 'ar' ? 'اتفاقية مستوى الخدمة' : 'Service Level Agreement' }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ $currentLang === 'ar' ? 'ضمانات الجودة والأداء' : 'Quality & performance guarantees' }}</p>
            </a>
        </div>

    </div>
</div>
@endsection
