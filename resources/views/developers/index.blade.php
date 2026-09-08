@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<style>
    .code-block {
        font-family: 'Fira Code', 'JetBrains Mono', 'Courier New', monospace;
        direction: ltr;
        text-align: left;
    }
    .gradient-border-card {
        background: linear-gradient(145deg, rgba(17, 24, 39, 0.95) 0%, rgba(11, 17, 32, 0.98) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        position: relative;
    }
    .gradient-border-card:hover {
        border-color: rgba(13, 148, 136, 0.4);
        box-shadow: 0 10px 35px -10px rgba(13, 148, 136, 0.25);
    }
    .tab-btn.active {
        background: rgba(13, 148, 136, 0.15);
        color: #2dd4bf;
        border-color: #0d9488;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#0b1120] text-slate-100 py-16 px-4 sm:px-6 lg:px-8" dir="{{ $direction }}">
    <div class="max-w-7xl mx-auto">

        {{-- Hero Section --}}
        <div class="text-center max-w-4xl mx-auto mb-16 pt-8">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-green/10 border border-brand-green/30 text-brand-green text-xs font-bold mb-6">
                <i class="fa-solid fa-code"></i>
                <span>{{ $currentLang === 'en' ? 'Radiif Developer Portal & API' : 'بوابة المطورين والربط البرمجي | منصة رديف' }}</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </div>
            
            <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight mb-6">
                {{ $currentLang === 'en' ? 'Empower Your Systems with Saudi Legal AI' : 'اربط أنظمتك بالذكاء الاصطناعي القانوني السعودي' }}
            </h1>
            
            <p class="text-slate-400 text-base sm:text-lg leading-relaxed mb-8 max-w-3xl mx-auto">
                {{ $currentLang === 'en' 
                    ? 'Seamlessly integrate Saudi Arabia\'s most comprehensive legal AI search engine and document reasoning models into your enterprise CRM, ERP, and LegalTech applications.' 
                    : 'واجهة برمجة تطبيقات (RESTful API) متطورة تمكنك من دمج محرك البحث الدلالي في السوابق القضائية والأنظمة السعودية ونماذج الاستشارات القانونية المؤتمتة داخل أنظمة شركتك أو تطبيقك.' }}
            </p>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('developers.beta') }}" class="px-8 py-3.5 bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black rounded-xl shadow-lg shadow-brand-green/25 hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-key"></i>
                    <span>{{ $currentLang === 'en' ? 'Request Beta API Key' : 'طلب مفتاح API تجريبي' }}</span>
                </a>
                <a href="#endpoints" class="px-6 py-3.5 bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 font-bold rounded-xl border border-slate-700/60 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-book-bookmark text-brand-green"></i>
                    <span>{{ $currentLang === 'en' ? 'Explore Endpoints' : 'تصفح نقاط النهاية (Endpoints)' }}</span>
                </a>
            </div>
        </div>

        {{-- Quick Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <div class="gradient-border-card rounded-2xl p-6">
                <div class="w-12 h-12 rounded-xl bg-brand-green/10 border border-brand-green/20 flex items-center justify-center text-brand-green text-xl mb-4">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">{{ $currentLang === 'en' ? 'Saudi Legal Compliance' : 'موثوقية وتطابق نظامي كامل' }}</h3>
                <p class="text-slate-400 text-xs leading-relaxed">
                    {{ $currentLang === 'en' ? 'Responses are strictly anchored in official statutes and verified judicial precedents.' : 'جميع الإجابات والاستدلالات مستندة حصراً إلى نصوص الأنظمة السعودية الرسمية وأحكام الاستئناف والمحاكم التجارية.' }}
                </p>
            </div>

            <div class="gradient-border-card rounded-2xl p-6">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-xl mb-4">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">{{ $currentLang === 'en' ? 'Ultra-low Latency' : 'سرعة فائقة واسترجاع هجين' }}</h3>
                <p class="text-slate-400 text-xs leading-relaxed">
                    {{ $currentLang === 'en' ? 'Sub-second hybrid vector search via Qdrant & Azure AI across 50,000+ legal citations.' : 'استرجاع متوازي وهجين بأقل من 800ms عبر تقنيات تضمين المتجهات لأكثر من 50 ألف مادة وحكم قضائي.' }}
                </p>
            </div>

            <div class="gradient-border-card rounded-2xl p-6">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-xl mb-4">
                    <i class="fa-solid fa-terminal"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">{{ $currentLang === 'en' ? 'Standard REST & JSON' : 'تكامل سهل عبر REST و JSON' }}</h3>
                <p class="text-slate-400 text-xs leading-relaxed">
                    {{ $currentLang === 'en' ? 'Ready-made SDK patterns for Python, PHP, Node.js, and OpenAPI 3.0 specification.' : 'هيكلية استجابة موحدة مع مصفوفة المراجع (Citations Schema) ومؤشرات الدقة (Confidence Scores).' }}
                </p>
            </div>
        </div>

        {{-- Authentication Section --}}
        <div class="gradient-border-card rounded-2xl p-8 mb-16" id="auth">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-slate-800">
                <div>
                    <h2 class="text-xl font-black text-white flex items-center gap-2">
                        <i class="fa-solid fa-lock text-brand-green"></i>
                        <span>{{ $currentLang === 'en' ? 'Authentication & Headers' : 'المصادقة وترويسات الطلب (Headers)' }}</span>
                    </h2>
                    <p class="text-slate-400 text-xs mt-1">
                        {{ $currentLang === 'en' ? 'Pass your API Key in the Authorization header as a Bearer token.' : 'يتم إرسال مفتاح الـ API الخاص بك في ترويسة Authorization بصيغة Bearer Token.' }}
                    </p>
                </div>
                <span class="text-xs px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono font-bold">
                    HTTPS Bearer Token
                </span>
            </div>

            <div class="mt-6 code-block bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs text-slate-300">
                <span class="text-slate-500"># Required HTTP Headers</span><br>
                <span class="text-indigo-400">Authorization</span>: Bearer <span class="text-emerald-400">radif_live_your_api_key_here</span><br>
                <span class="text-indigo-400">Content-Type</span>: application/json<br>
                <span class="text-indigo-400">Accept</span>: application/json
            </div>
        </div>

        {{-- Interactive Endpoints Documentation --}}
        <div id="endpoints" class="space-y-12">
            <div class="text-center md:text-start">
                <h2 class="text-2xl sm:text-3xl font-black text-white mb-2">{{ $currentLang === 'en' ? 'Core Endpoints' : 'نقاط النهاية الرئيسية (API Endpoints)' }}</h2>
                <p class="text-slate-400 text-sm">{{ $currentLang === 'en' ? 'Interact with our legal AI reasoning and citation search engines.' : 'استكشف الوظائف البرمجية المتاحة للتكامل الفوري.' }}</p>
            </div>

            {{-- Endpoint 1: POST /api/v1/legal/ask --}}
            <div class="gradient-border-card rounded-2xl p-6 lg:p-8" x-data="{ activeTab: 'curl' }">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-emerald-500 text-dark-navy font-black text-xs rounded-lg uppercase">POST</span>
                        <span class="font-mono text-sm sm:text-base font-bold text-slate-200">https://api.radiif.com/v1/legal/ask</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">المستشار القانوني الذكي وتوليد الإجابات الموثقة</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                    {{-- Parameters & Description --}}
                    <div class="lg:col-span-5 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $currentLang === 'en' ? 'Request Body Schema' : 'محددات الطلب (Request Parameters)' }}</h4>
                        
                        <div class="space-y-3 text-xs">
                            <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800">
                                <div class="flex items-center justify-between font-mono mb-1">
                                    <span class="text-emerald-400 font-bold">question</span>
                                    <span class="text-rose-400">string (required)</span>
                                </div>
                                <p class="text-slate-400">نص الاستفسار أو المسألة القانونية المراد فحصها.</p>
                            </div>

                            <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800">
                                <div class="flex items-center justify-between font-mono mb-1">
                                    <span class="text-emerald-400 font-bold">mode</span>
                                    <span class="text-slate-400">string (optional)</span>
                                </div>
                                <p class="text-slate-400">وضع الإجابة: <code class="text-teal-300">simplified</code> (مبسطة لغير المحامين والأفراد) أو <code class="text-teal-300">professional</code> (احترافية للمحامين).</p>
                            </div>

                            <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800">
                                <div class="flex items-center justify-between font-mono mb-1">
                                    <span class="text-emerald-400 font-bold">conversation_uuid</span>
                                    <span class="text-slate-400">uuid (optional)</span>
                                </div>
                                <p class="text-slate-400">معرف المحادثة للحفاظ على سياق الأسئلة التتابعية.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Code Samples & Response Tabs --}}
                    <div class="lg:col-span-7 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button @click="activeTab = 'curl'" :class="{ 'active': activeTab === 'curl' }" class="tab-btn px-3 py-1 text-xs font-mono rounded-lg border border-slate-700 bg-slate-800/50 text-slate-300 transition">cURL</button>
                                <button @click="activeTab = 'python'" :class="{ 'active': activeTab === 'python' }" class="tab-btn px-3 py-1 text-xs font-mono rounded-lg border border-slate-700 bg-slate-800/50 text-slate-300 transition">Python</button>
                                <button @click="activeTab = 'php'" :class="{ 'active': activeTab === 'php' }" class="tab-btn px-3 py-1 text-xs font-mono rounded-lg border border-slate-700 bg-slate-800/50 text-slate-300 transition">PHP</button>
                                <button @click="activeTab = 'node'" :class="{ 'active': activeTab === 'node' }" class="tab-btn px-3 py-1 text-xs font-mono rounded-lg border border-slate-700 bg-slate-800/50 text-slate-300 transition">Node.js</button>
                            </div>
                            <span class="text-[11px] text-slate-500 font-mono">200 OK Response</span>
                        </div>

                        {{-- Code viewer --}}
                        <div class="code-block bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs overflow-x-auto max-h-[300px] custom-scrollbar">
                            <template x-if="activeTab === 'curl'">
                                <pre class="text-slate-300">curl -X POST https://api.radiif.com/v1/legal/ask \
  -H "Authorization: Bearer radif_live_sample_key" \
  -H "Content-Type: application/json" \
  -d '{
    "question": "ما هي شروط استحقاق مكافأة نهاية الخدمة عند الاستقالة؟",
    "mode": "simplified"
  }'</pre>
                            </template>

                            <template x-if="activeTab === 'python'">
                                <pre class="text-slate-300">import requests

url = "https://api.radiif.com/v1/legal/ask"
headers = {
    "Authorization": "Bearer radif_live_sample_key",
    "Content-Type": "application/json"
}
payload = {
    "question": "ما هي شروط استحقاق مكافأة نهاية الخدمة عند الاستقالة؟",
    "mode": "simplified"
}

response = requests.post(url, json=payload, headers=headers)
data = response.json()
print(data["answer"])</pre>
                            </template>

                            <template x-if="activeTab === 'php'">
                                <pre class="text-slate-300">&lt;?php
use Illuminate\Support\Facades\Http;

$response = Http::withToken('radif_live_sample_key')
    ->post('https://api.radiif.com/v1/legal/ask', [
        'question' => 'ما هي شروط استحقاق مكافأة نهاية الخدمة عند الاستقالة؟',
        'mode' => 'simplified',
    ]);

$result = $response->json();
echo $result['answer'];</pre>
                            </template>

                            <template x-if="activeTab === 'node'">
                                <pre class="text-slate-300">const response = await fetch("https://api.radiif.com/v1/legal/ask", {
  method: "POST",
  headers: {
    "Authorization": "Bearer radif_live_sample_key",
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    question: "ما هي شروط استحقاق مكافأة نهاية الخدمة عند الاستقالة؟",
    mode: "simplified"
  })
});
const data = await response.json();
console.log(data);</pre>
                            </template>
                        </div>

                        {{-- Mock Response Preview --}}
                        <div>
                            <div class="text-[11px] font-mono text-slate-400 mb-1 flex items-center justify-between">
                                <span>Response JSON Payload</span>
                                <span class="text-emerald-400">● 200 OK (520ms)</span>
                            </div>
                            <div class="code-block bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs text-emerald-300/90 overflow-x-auto max-h-[220px]">
<pre>{
  "success": true,
  "mode": "simplified",
  "answer": "تستحق مكافأة نهاية الخدمة عند الاستقالة بحسب مدة خدمتك وفقاً للمادة 85 من نظام العمل...",
  "citations": {
    "confidence_score": 96,
    "items": [
      {
        "type": "article",
        "title": "المادة 85 - نظام العمل السعودي",
        "article_number": "85",
        "system": "نظام العمل"
      }
    ]
  },
  "conversation_uuid": "c4b12a9e-873b-419b-b0b9-5f1712a4b882"
}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Endpoint 2: POST /api/v1/legal/search --}}
            <div class="gradient-border-card rounded-2xl p-6 lg:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-cyan-500 text-dark-navy font-black text-xs rounded-lg uppercase">POST</span>
                        <span class="font-mono text-sm sm:text-base font-bold text-slate-200">https://api.radiif.com/v1/legal/search</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">البحث الدلالي المتجهي والهجين في السوابق والمواد</span>
                </div>

                <div class="mt-6 code-block bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs text-slate-300">
<pre>curl -X POST https://api.radiif.com/v1/legal/search \
  -H "Authorization: Bearer radif_live_sample_key" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "فسخ العقد لعدم سداد الدفعات في عقود التوريد",
    "filter_type": "judgment",
    "limit": 5
  }'</pre>
                </div>
            </div>
        </div>

        {{-- CTA Bottom Banner --}}
        <div class="mt-20 p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-emerald-950/40 via-dark-card to-indigo-950/30 border border-brand-green/30 text-center relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-brand-green/10 rounded-full blur-3xl"></div>
            <div class="relative z-10 max-w-2xl mx-auto">
                <h3 class="text-2xl sm:text-3xl font-black text-white mb-4">
                    {{ $currentLang === 'en' ? 'Ready to build with Radiif API?' : 'جاهز للبدء في دمج رديف داخل تطبيقاتك؟' }}
                </h3>
                <p class="text-slate-400 text-sm mb-8">
                    {{ $currentLang === 'en' 
                        ? 'Submit a beta access request to receive your development sandbox keys and dedicated integration support.' 
                        : 'قدّم طلب انضمام للنسخة التجريبية للمطورين للحصول على مفتاح API تجريبي ودعم فني مخصص للتكامل.' }}
                </p>
                <a href="{{ route('developers.beta') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black rounded-2xl shadow-green-glow hover:scale-105 transition-all duration-200">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>{{ $currentLang === 'en' ? 'Apply for Beta Access' : 'تقديم طلب الحصول على النسخة التجريبية' }}</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
