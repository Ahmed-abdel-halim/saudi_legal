@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-dark-navy text-white pt-24 pb-16 border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    {{-- Glow background --}}
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 max-w-[1400px] relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-2/3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand-green/20 bg-brand-green/10 text-brand-green text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
                    v1.0.0 Stable
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
                    {{ __('pages.API_TITLE') }}
                </h1>
                <p class="text-base md:text-lg text-slate-400 max-w-2xl leading-relaxed">
                    {{ __('pages.API_SUBTITLE') }}
                </p>
            </div>
            <div class="lg:w-1/3 w-full">
                <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 backdrop-blur-md">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('pages.API_ENDPOINT') }}</h3>
                    <div class="flex items-center justify-between bg-slate-950/60 p-3 rounded-lg border border-white/5 font-mono text-sm text-brand-green">
                        <span>https://api.radiif.com/v1</span>
                        <button onclick="navigator.clipboard.writeText('https://api.radiif.com/v1')" class="hover:text-white transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-dark-navy py-16" x-data="{ currentTab: 'curl' }">
    <div class="container mx-auto px-6 max-w-[1400px]">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Navigation Sidebar --}}
            <div class="lg:col-span-3">
                <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-4 sticky top-28 shadow-lg backdrop-blur-md">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 px-3">{{ __('pages.DOCUMENTATION') }}</h4>
                    <nav class="space-y-1">
                        <a href="#getting-started" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-400 hover:text-brand-green hover:bg-white/5 transition-all duration-200 border-r-4 border-transparent hover:border-brand-green">{{ __('pages.API_DOCS_NAV_GETTING_STARTED') }}</a>
                        <a href="#authentication" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-400 hover:text-brand-green hover:bg-white/5 transition-all duration-200 border-r-4 border-transparent hover:border-brand-green">{{ __('pages.API_DOCS_NAV_AUTHENTICATION') }}</a>
                        <a href="#create-task" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-400 hover:text-brand-green hover:bg-white/5 transition-all duration-200 border-r-4 border-transparent hover:border-brand-green">{{ __('pages.API_DOCS_NAV_CREATE_TASK') }}</a>
                        <a href="#get-results" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-400 hover:text-brand-green hover:bg-white/5 transition-all duration-200 border-r-4 border-transparent hover:border-brand-green">{{ __('pages.API_DOCS_NAV_GET_RESULTS') }}</a>
                        <a href="#webhooks" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-400 hover:text-brand-green hover:bg-white/5 transition-all duration-200 border-r-4 border-transparent hover:border-brand-green">{{ __('pages.API_DOCS_NAV_WEBHOOKS') }}</a>
                    </nav>
                </div>
            </div>

            {{-- Main Content Section --}}
            <div class="lg:col-span-9 space-y-12">
                
                {{-- Getting Started --}}
                <section id="getting-started" class="expert-card p-8">
                    <h2 class="text-xl md:text-2xl font-black text-white mb-4 border-b border-white/5 pb-3">{{ __('pages.API_DOCS_NAV_GETTING_STARTED') }}</h2>
                    <p class="text-slate-400 leading-relaxed">
                        Welcome to the Radiif developer portal. Our API enables technology partners, AI labs, and enterprise clients to connect directly to our Saudi expert annotation network. By using the API, you can programmatically request annotation work, deliver RLHF prompts, track tasks in real-time, and download finalized datasets directly into your machine learning pipelines.
                    </p>
                </section>

                {{-- Authentication --}}
                <section id="authentication" class="expert-card p-8">
                    <h2 class="text-xl md:text-2xl font-black text-white mb-4 border-b border-white/5 pb-3">{{ __('pages.API_KEY_SECTION') }}</h2>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        {{ __('pages.API_KEY_DESC') }}
                    </p>
                    <div class="bg-slate-950/60 text-slate-300 font-mono text-sm p-4 rounded-xl border border-white/5">
                        Authorization: Bearer <span class="text-brand-green">YOUR_ORGANIZATION_TOKEN</span>
                    </div>
                </section>

                {{-- Create Task --}}
                <section id="create-task" class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <div class="expert-card p-8">
                        <h2 class="text-xl md:text-2xl font-black text-white mb-4 border-b border-white/5 pb-3">{{ __('pages.API_DOCS_NAV_CREATE_TASK') }}</h2>
                        <p class="text-slate-400 leading-relaxed mb-6">
                            Start a new annotation or RLHF preference task by sending a POST request to `/tasks`. Provide task guidelines, prompt lists, and language preferences.
                        </p>
                        
                        <div class="space-y-4">
                            <h3 class="font-bold text-white text-sm">{{ __('pages.API_REQ_PARAMS') }}</h3>
                            <div class="border border-white/5 bg-slate-950/30 rounded-xl overflow-hidden text-sm">
                                <div class="bg-slate-900/60 grid grid-cols-3 p-3 font-bold border-b border-white/5 text-slate-300">
                                    <div>Field</div>
                                    <div>Type</div>
                                    <div>Required</div>
                                </div>
                                <div class="grid grid-cols-3 p-3 border-b border-white/5 font-mono text-xs text-slate-400">
                                    <div class="text-brand-green font-bold">task_type</div>
                                    <div>string</div>
                                    <div class="text-red-400 font-semibold">Yes</div>
                                </div>
                                <div class="grid grid-cols-3 p-3 border-b border-white/5 font-mono text-xs text-slate-400">
                                    <div class="text-brand-green font-bold">prompts</div>
                                    <div>array</div>
                                    <div class="text-red-400 font-semibold">Yes</div>
                                </div>
                                <div class="grid grid-cols-3 p-3 font-mono text-xs text-slate-400">
                                    <div class="text-brand-green font-bold">guidelines</div>
                                    <div>string</div>
                                    <div class="text-slate-500">No</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Code Tabs --}}
                    <div class="expert-card p-6 flex flex-col bg-slate-950/60">
                        <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-4">
                            <h3 class="text-sm font-bold text-white">{{ __('pages.API_CODE_TITLE') }}</h3>
                            <div class="flex bg-slate-900 rounded-lg p-0.5 text-xs">
                                <button @click="currentTab = 'curl'" :class="currentTab === 'curl' ? 'bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-bold' : 'text-slate-400'" class="px-3 py-1 rounded-md transition-colors cursor-pointer">curl</button>
                                <button @click="currentTab = 'python'" :class="currentTab === 'python' ? 'bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-bold' : 'text-slate-400'" class="px-3 py-1 rounded-md transition-colors cursor-pointer">Python</button>
                                <button @click="currentTab = 'php'" :class="currentTab === 'php' ? 'bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-bold' : 'text-slate-400'" class="px-3 py-1 rounded-md transition-colors cursor-pointer">PHP</button>
                            </div>
                        </div>

                        <div class="flex-grow font-mono text-xs overflow-x-auto space-y-4">
                            <div x-show="currentTab === 'curl'">
<pre class="text-brand-green">
curl -X POST https://api.radiif.com/v1/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "task_type": "rlhf_ranking",
    "prompts": [
      {
        "prompt": "Explain the rules of PDPL in simple terms.",
        "completions": ["Model response A...", "Model response B..."]
      }
    ],
    "guidelines": "Ensure safety, cultural alignment, and correctness."
  }'
</pre>
                            </div>
                            
                            <div x-show="currentTab === 'python'" style="display: none;">
<pre class="text-teal-400">
import requests

url = "https://api.radiif.com/v1/tasks"
headers = {
    "Authorization": "Bearer YOUR_TOKEN",
    "Content-Type": "application/json"
}
payload = {
    "task_type": "rlhf_ranking",
    "prompts": [
        {
            "prompt": "Explain PDPL rules in simple terms.",
            "completions": ["Completion A...", "Completion B..."]
        }
    ]
}

response = requests.post(url, json=payload, headers=headers)
print(response.json())
</pre>
                            </div>

                            <div x-show="currentTab === 'php'" style="display: none;">
<pre class="text-emerald-400">
$ch = curl_init("https://api.radiif.com/v1/tasks");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer YOUR_TOKEN",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "task_type" => "rlhf_ranking",
    "prompts" => [
        [
            "prompt" => "Explain PDPL rules in simple terms.",
            "completions" => ["A", "B"]
        ]
    ]
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = json_decode(curl_exec($ch), true);
</pre>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Get Results --}}
                <section id="get-results" class="expert-card p-8">
                    <h2 class="text-xl md:text-2xl font-black text-white mb-4 border-b border-white/5 pb-3">{{ __('pages.API_DOCS_NAV_GET_RESULTS') }}</h2>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        Once experts submit evaluations, the tasks change status to `completed`. You can retrieve results using a GET request for a specific task.
                    </p>
                    <div class="bg-slate-950/65 text-slate-300 font-mono text-sm p-4 rounded-xl border border-white/5 mb-6">
                        <span class="text-brand-green font-bold">GET</span> /tasks/{task_id}
                    </div>
                    <div class="space-y-4">
                        <h3 class="font-bold text-white text-sm">{{ __('pages.API_RESP_FORMAT') }}</h3>
<pre class="bg-slate-950/40 border border-white/5 rounded-xl p-4 font-mono text-xs text-slate-400 overflow-x-auto">
{
  "task_id": "task_9281a82b",
  "status": "completed",
  "results": [
    {
      "prompt": "Explain PDPL rules in simple terms.",
      "best_completion_index": 0,
      "annotations": {
        "rationale": "Completion A includes detailed local hosting requirements, while B lacks SA hosting details."
      }
    }
  ]
}
</pre>
                    </div>
                </section>

                {{-- Webhooks --}}
                <section id="webhooks" class="expert-card p-8">
                    <h2 class="text-xl md:text-2xl font-black text-white mb-4 border-b border-white/5 pb-3">{{ __('pages.API_DOCS_NAV_WEBHOOKS') }}</h2>
                    <p class="text-slate-400 leading-relaxed">
                        Configure a webhook URL under your dashboard developers section. Radiif will send a POST request with payload signatures whenever task groups complete processing, allowing your ML infrastructure to automatically pull dataset outputs and trigger retraining.
                    </p>
                </section>

            </div>

        </div>
    </div>
</div>
@endsection
