@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-800 min-h-screen pb-20">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ app()->getLocale() == 'ar' ? '19 12H5m7 7l-7-7 7-7' : '5 12h14M12 5l7 7-7 7' }}"></path></svg>
                </a>
                <span class="font-bold text-xl text-slate-800">{{ __('dashboard.projects_contracts') }}</span>
            </div>
       <div class="hidden md:flex items-center gap-3">

        {{-- Governance / Data Refinement Button --}}
        <a href="{{ route('client.governance.dashboard') }}"
          class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-bold text-sm shadow-sm transition flex items-center gap-2">

        <span class="flex items-center gap-1">
            طلب تنقيح بيانات
            <svg class="w-4 h-4 text-indigo-500"
                 viewBox="0 0 24 24"
                 fill="currentColor">
                <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/>
            </svg>
        </span>
      </a>

      {{-- New Project Button --}}
      <a href="{{ route('requests.browse') }}"
         class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-md transition flex items-center gap-2">

         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4v16m8-8H4"/>
        </svg>

        <span>{{ __('dashboard.btn_new_project') }}</span>
      </a>

</div>

        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">

        @if(count($projects) > 0)
            
            <!-- Projects List -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-bold">{{ __('dashboard.tbl_project') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('dashboard.tbl_role') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('dashboard.tbl_partner') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('dashboard.tbl_status') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('dashboard.tbl_budget') }}</th>
                                <th class="px-6 py-4 font-bold text-center">{{ __('dashboard.tbl_action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($projects as $proj)
                                @php 
                                    $is_requester = ($proj->requester_company_id == ($company->company_id ?? 0));
                                    $role_label = $is_requester ? __('dashboard.role_requester') : __('dashboard.role_supplier');
                                    $role_color = $is_requester ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700';
                                    
                                    $partner_name = $is_requester ? ($proj->supplier_name ?? '---') : $proj->requester_name;
                                    
                                    $status_color = 'bg-slate-100 text-slate-600';
                                    $status_text = $proj->status;
                                        
                                    if ($proj->status == 'open') { 
                                        $status_color = 'bg-green-100 text-green-700'; 
                                        $status_text = __('dashboard.status_open'); 
                                    } elseif ($proj->status == 'in_progress') { 
                                        $status_color = 'bg-indigo-100 text-indigo-700'; 
                                        $status_text = __('dashboard.status_in_progress'); 
                                    } elseif ($proj->status == 'completed') { 
                                        $status_color = 'bg-emerald-100 text-emerald-700'; 
                                        $status_text = __('dashboard.status_completed'); 
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        {{ $proj->title }}
                                        <div class="text-xs text-slate-400 font-normal mt-1">{{ \Carbon\Carbon::parse($proj->created_at)->format('Y-m-d') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold {{ $role_color }}">
                                            {{ $role_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium">
                                        {{ $partner_name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $status_color }}">
                                            {{ $status_text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-slate-700">
                                        {{ number_format($proj->budget ?? 0) }} {{ __('dashboard.currency') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        {{-- Link to view detail --}}
                                        <a href="#" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline transition">
                                            {{ __('dashboard.btn_view') }} &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @else

            <!-- Empty State -->
            <div class="max-w-2xl mx-auto mt-12 text-center">
                <div class="bg-white rounded-3xl p-10 shadow-sm border border-slate-200">
                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-3">{{ __('dashboard.empty_title') }}</h2>
                    <p class="text-slate-500 mb-8 leading-relaxed max-w-md mx-auto">
                        {{ __('dashboard.empty_desc') }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('requests.browse') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1">
                            {{ __('dashboard.btn_new_project') }}
                        </a>
                        {{-- Assuming search.php is general search or services browse --}}
                        <a href="{{ route('services.browse') }}" class="bg-white border border-slate-300 text-slate-700 hover:border-slate-400 px-8 py-3 rounded-xl font-bold transition">
                            {{ __('dashboard.btn_find_expert') }}
                        </a>
                        <a href="{{ route('client.governance.dashboard') }}"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold
                            shadow-lg shadow-emerald-200 transition transform hover:-translate-y-1
                            inline-flex items-center justify-center gap-3">
                            <span>
                                {{ app()->getLocale() == 'ar' ? 'طلب تنقيح بيانات' : 'Data Annotation' }}
                            </span>

                            {{-- Sparkles Icon (same one, resized & centered) --}}
                            <svg class="w-10 h-10"
                                viewBox="0 0 512 512"
                                aria-hidden="true">
                                <path fill="#2DD4FF" d="M256 32l40 144 144 40-144 40-40 144-40-144-144-40 144-40z"/>
                                <path fill="#A5F3FC" d="M400 96l20 72 72 20-72 20-20 72-20-72-72-20 72-20z"/>
                                <path fill="#34F5C5" d="M384 320l16 56 56 16-56 16-16 56-16-56-56-16 56-16z"/>
                                <path fill="#38BDF8" d="M96 112l12 40 40 12-40 12-12 40-12-40-40-12 40-12z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tips -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8 rtl:text-right ltr:text-left">
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <span class="text-2xl block mb-2">⚡</span>
                        <h4 class="font-bold text-sm text-slate-800">{{ __('dashboard.quick_post') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('dashboard.quick_post_desc') }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <span class="text-2xl block mb-2">🛡️</span>
                        <h4 class="font-bold text-sm text-slate-800">{{ __('dashboard.full_protection') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('dashboard.full_protection_desc') }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <span class="text-2xl block mb-2">🤝</span>
                        <h4 class="font-bold text-sm text-slate-800">{{ __('dashboard.verified_experts') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('dashboard.verified_experts_desc') }}</p>
                    </div>
                </div>
            </div>

        @endif

    </div>

</div>
@endsection
