@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-slate-900 text-white pt-20 pb-16">
    {{-- Glow background --}}
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-primary/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-teal/20 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 max-w-7xl relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-2/3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand-primary/30 bg-brand-primary/10 text-brand-primary text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                    v1.0.0 Stable
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-none mb-6">
                    {{ __('pages.API_TITLE') }}
                </h1>
                <p class="text-lg md:text-xl text-gray-300 max-w-2xl leading-relaxed">
                    {{ __('pages.API_SUBTITLE') }}
                </p>
            </div>
            <div class="lg:w-1/3 w-full">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-3">{{ __('pages.API_ENDPOINT') }}</h3>
                    <div class="flex items-center justify-between bg-dark-navy p-3 rounded-lg border border-white/10 font-mono text-sm text-brand-teal">
                        <span>https://api.radiif.com/v1</span>
                        <button onclick="navigator.clipboard.writeText('https://api.radiif.com/v1')" class="hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-slate-50 py-16" x-data="{ currentTab: 'curl' }">
    <div class="container mx-auto px-6 max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Navigation Sidebar --}}
            <div class="lg:col-span-3">
                <div class="bg-white border border-gray-200 rounded-2xl p-4 sticky top-28 shadow-sm">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-4 px-3">{{ __('pages.DOCUMENTATION') }}</h4>
                    <nav class="space-y-1">
                        <a href="#getting-started" class="block px-3 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-slate-50 transition-colors border-r-4 border-transparent hover:border-brand-primary">{{ __('pages.API_DOCS_NAV_GETTING_STARTED') }}</a>
                        <a href="#authentication" class="block px-3 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-slate-50 transition-colors border-r-4 border-transparent hover:border-brand-primary">{{ __('pages.API_DOCS_NAV_AUTHENTICATION') }}</a>
                        <a href="#create-task" class="block px-3 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-slate-50 transition-colors border-r-4 border-transparent hover:border-brand-primary">{{ __('pages.API_DOCS_NAV_CREATE_TASK') }}</a>
                        <a href="#get-results" class="block px-3 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-slate-50 transition-colors border-r-4 border-transparent hover:border-brand-primary">{{ __('pages.API_DOCS_NAV_GET_RESULTS') }}</a>
                        <a href="#webhooks" class="block px-3 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-slate-50 transition-colors border-r-4 border-transparent hover:border-brand-primary">{{ __('pages.API_DOCS_NAV_WEBHOOKS') }}</a>
                    </nav>
                </div>
            </div>

            {{-- Main Content Section --}}
            <div class="lg:col-span-9 space-y-16">
                
                {{-- Getting Started --}}
                <section id="getting-started" class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-dark-navy mb-4">{{ __('pages.API_DOCS_NAV_GETTING_STARTED') }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Welcome to the Radiif developer portal. Our API enables technology partners, AI labs, and enterprise clients to connect directly to our Saudi expert annotation network. By using the API, you can programmatically request annotation work, deliver RLHF prompts, track tasks in real-time, and download finalized datasets directly into your machine learning pipelines.
                    </p>
                </section>

                {{-- Authentication --}}
                <section id="authentication" class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-dark-navy mb-4">{{ __('pages.API_KEY_SECTION') }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        {{ __('pages.API_KEY_DESC') }}
                    </p>
                    <div class="bg-slate-900 text-slate-300 font-mono text-sm p-4 rounded-2xl border border-slate-800">
                        Authorization: Bearer <span class="text-brand-teal">YOUR_ORGANIZATION_TOKEN</span>
                    </div>
                </section>

                {{-- Create Task --}}
                <section id="create-task" class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-dark-navy mb-4">{{ __('pages.API_DOCS_NAV_CREATE_TASK') }}</h2>
                        <p class="text-gray-600 leading-relaxed mb-6">
                            Start a new annotation or RLHF preference task by sending a POST request to `/tasks`. Provide task guidelines, prompt lists, and language preferences.
                        </p>
                        
                        <div class="space-y-4">
                            <h3 class="font-semibold text-dark-navy text-sm">{{ __('pages.API_REQ_PARAMS') }}</h3>
                            <div class="border border-gray-200 rounded-xl overflow-hidden text-sm">
                                <div class="bg-slate-50 grid grid-cols-3 p-3 font-semibold border-b border-gray-200">
                                    <div>Field</div>
                                    <div>Type</div>
                                    <div>Required</div>
                                </div>
                                <div class="grid grid-cols-3 p-3 border-b border-gray-200 font-mono text-xs">
                                    <div class="text-brand-primary">task_type</div>
                                    <div class="text-gray-500">string</div>
                                    <div class="text-red-500 font-semibold">Yes</div>
                                </div>
                                <div class="grid grid-cols-3 p-3 border-b border-gray-200 font-mono text-xs">
                                    <div class="text-brand-primary">prompts</div>
                                    <div class="text-gray-500">array</div>
                                    <div class="text-red-500 font-semibold">Yes</div>
                                </div>
                                <div class="grid grid-cols-3 p-3 font-mono text-xs">
                                    <div class="text-brand-primary">guidelines</div>
                                    <div class="text-gray-500">string</div>
                                    <div class="text-gray-400">No</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Code Tabs --}}
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 text-slate-300 flex flex-col">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-4">
                            <h3 class="text-sm font-bold text-white">{{ __('pages.API_CODE_TITLE') }}</h3>
                            <div class="flex bg-slate-800 rounded-lg p-0.5 text-xs">
                                <button @click="currentTab = 'curl'" :class="currentTab === 'curl' ? 'bg-brand-primary text-white' : 'text-slate-400'" class="px-3 py-1 rounded-md transition-colors">curl</button>
                                <button @click="currentTab = 'python'" :class="currentTab === 'python' ? 'bg-brand-primary text-white' : 'text-slate-400'" class="px-3 py-1 rounded-md transition-colors">Python</button>
                                <button @click="currentTab = 'php'" :class="currentTab === 'php' ? 'bg-brand-primary text-white' : 'text-slate-400'" class="px-3 py-1 rounded-md transition-colors">PHP</button>
                            </div>
                        </div>

                        <div class="flex-grow font-mono text-xs overflow-x-auto space-y-4">
                            <div x-show="currentTab === 'curl'">
<pre class="text-brand-teal">
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
<pre class="text-violet-400">
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
<pre class="text-sky-400">
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
                <section id="get-results" class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-dark-navy mb-4">{{ __('pages.API_DOCS_NAV_GET_RESULTS') }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Once experts submit evaluations, the tasks change status to `completed`. You can retrieve results using a GET request for a specific task.
                    </p>
                    <div class="bg-slate-900 text-slate-300 font-mono text-sm p-4 rounded-2xl border border-slate-800 mb-6">
                        <span class="text-green-500 font-bold">GET</span> /tasks/{task_id}
                    </div>
                    <div class="space-y-4">
                        <h3 class="font-semibold text-dark-navy text-sm">{{ __('pages.API_RESP_FORMAT') }}</h3>
<pre class="bg-slate-50 border border-gray-200 rounded-2xl p-4 font-mono text-xs text-gray-700 overflow-x-auto">
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
                <section id="webhooks" class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-dark-navy mb-4">{{ __('pages.API_DOCS_NAV_WEBHOOKS') }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Configure a webhook URL under your dashboard developers section. Radiif will send a POST request with payload signatures whenever task groups complete processing, allowing your ML infrastructure to automatically pull dataset outputs and trigger retraining.
                    </p>
                </section>

            </div>

        </div>
    </div>
</div>
@endsection
