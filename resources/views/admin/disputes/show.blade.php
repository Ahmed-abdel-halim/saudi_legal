@extends('layouts.admin')

@section('title', __('admin.dispute_details') ?? 'Dispute Details')

@section('content')

{{-- Breadcrumbs & Header --}}
<div class="flex items-center gap-3 mb-8">
    <a href="{{ route('admin.disputes.index') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-primary hover:border-primary transition shadow-sm">
        <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>
    </a>
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">
            {!! __('admin.dispute_details') ?? 'Dispute Details' !!}
            <span class="text-lg text-slate-400 font-medium ml-2">#{{ $contract->id }}</span>
        </h1>
        <div class="flex items-center gap-2 mt-2">
            @if($type === 'offer')
                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] font-bold uppercase tracking-wider">Project</span>
            @else
                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold uppercase tracking-wider">Hourly</span>
            @endif
            <span class="text-sm font-bold text-red-500"><i class="fa-solid fa-circle text-[8px] mr-1"></i> {!! __('admin.status_disputed') ?? 'Needs Resolution' !!}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- Left Side: Contract & Conversation --}}
    <div class="lg:col-span-2 space-y-8">
        
        {{-- Contract Info --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-file-contract text-primary rtl:ml-2"></i> {!! __('admin.contract_information') ?? 'Contract Information' !!}</h3>
            </div>
            <div class="p-6">
                <h4 class="text-xl font-black text-slate-800 mb-4">
                    {{ $type === 'offer' ? $contract->project->title : 'Hourly Service Purchase' }}
                </h4>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{!! __('admin.amount') ?? 'Amount' !!}</span>
                        <span class="font-black text-slate-800 text-lg">
                            ${{ number_format($type === 'offer' ? $contract->expert_amount : $contract->total_price, 2) }}
                        </span>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{!! __('admin.created_on') ?? 'Created On' !!}</span>
                        <span class="font-bold text-slate-700">{{ $contract->created_at->format('M d, Y - h:i A') }}</span>
                    </div>
                </div>

                @if($type === 'offer')
                <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{!! __('admin.project_details') ?? 'Project Scope' !!}</span>
                    <p class="text-slate-600 text-sm leading-relaxed">{{ $contract->project->description ?? 'No description provided.' }}</p>
                </div>
                @else
                <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{!! __('admin.hours_purchased') ?? 'Hours Purchased' !!}</span>
                    <p class="font-black text-slate-800 text-lg">{{ $contract->hours_purchased }} Hours</p>
                </div>
                @endif
                
            </div>
        </div>

        {{-- Conversation History --}}
        @if($contract->conversation)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col" style="max-height: 600px;">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-regular fa-comments text-primary rtl:ml-2"></i> {!! __('admin.conversation_log') ?? 'Conversation Log' !!}</h3>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 bg-slate-50 space-y-4">
                @forelse($contract->conversation->messages as $msg)
                    @php
                        $isSystem = $msg->user_id === null;
                        $isExpert = !$isSystem && $msg->user_id === $contract->expert_id;
                    @endphp
                    
                    @if($isSystem)
                        <div class="flex justify-center my-4">
                            <span class="bg-slate-200 text-slate-600 text-[11px] font-bold px-3 py-1 rounded-full text-center">
                                <i class="fa-solid fa-robot mr-1"></i> {{ $msg->message }}
                            </span>
                        </div>
                    @else
                        <div class="flex gap-3 {{ $isExpert ? 'rtl:flex-row-reverse flex-row-reverse' : '' }}">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center font-bold text-primary text-xs">
                                {{ substr($msg->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="max-w-[75%]">
                                <div class="bg-white border border-slate-200 p-3 rounded-2xl shadow-sm text-sm text-slate-700 {{ $isExpert ? 'rounded-tr-none' : 'rounded-tl-none' }}">
                                    {{ $msg->message }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 {{ $isExpert ? 'text-right rtl:text-left' : 'text-left rtl:text-right' }}">
                                    {{ $msg->user->name ?? 'Unknown' }} • {{ $msg->created_at->format('h:i A \o\n M d') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-regular fa-message text-3xl mb-2 text-slate-300 block"></i>
                        No messages in this conversation.
                    </div>
                @endforelse
            </div>

            {{-- Send Admin Message --}}
            <div class="p-4 border-t border-slate-100 bg-white">
                <form action="{{ route('admin.disputes.message', ['type' => $type, 'id' => $contract->id]) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="message" placeholder="{!! __('admin.send_admin_message') ?? 'Send an official admin response...' !!}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition outline-none" required>
                    <button type="submit" class="bg-primary hover:bg-primary/90 text-white w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition shadow-sm">
                        <i class="fa-solid fa-paper-plane rtl:-scale-x-100"></i>
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="bg-slate-50 border border-slate-200 border-dashed rounded-2xl p-8 text-center text-slate-500">
            <i class="fa-regular fa-comments text-4xl mb-3 text-slate-300 block"></i>
            No conversation linked to this contract.
        </div>
        @endif
    </div>

    {{-- Right Side: Parties & Resolution Actions --}}
    <div class="lg:col-span-1 space-y-8">
        
        {{-- Parties --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">{!! __('admin.involved_parties') ?? 'Involved Parties' !!}</h3>
            </div>
            <div class="p-6 space-y-6">
                
                {{-- Company / Requester --}}
                @php $company = $type === 'offer' ? $contract->project->requester : $contract->client; @endphp
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 block">{!! __('admin.company_client') ?? 'Client / Company' !!}</span>
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($company->name ?? 'Company') }}&background=E2E8F0&color=475569" class="w-12 h-12 rounded-xl object-cover border border-slate-200">
                        <div>
                            <div class="font-bold text-slate-800">{{ $company->name ?? 'Unknown Company' }}</div>
                            <div class="text-xs text-slate-500">{{ $company->email ?? '' }}</div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                {{-- Expert --}}
                @php $expert = $contract->expert; @endphp
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 block">{!! __('admin.expert') ?? 'Expert Provider' !!}</span>
                    <div class="flex items-center gap-3">
                        <img src="{{ $expert->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($expert->name ?? 'Expert').'&background=eef2ff&color=4f46e5' }}" class="w-12 h-12 rounded-full object-cover border border-slate-200">
                        <div>
                            <div class="font-bold text-slate-800">{{ $expert->name ?? 'Unknown Expert' }}</div>
                            <div class="text-xs text-slate-500">{{ $expert->email ?? '' }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Resolution Action --}}
        <div class="bg-red-50 border border-red-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-red-100 bg-white/50">
                <h3 class="text-lg font-bold text-red-600"><i class="fa-solid fa-gavel rtl:ml-2"></i> {!! __('admin.resolve_dispute') ?? 'Resolve Dispute' !!}</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-700 mb-6 font-medium leading-relaxed">
                    Review the contract details and conversation log carefully before making a final decision. <strong class="text-red-600">This action cannot be undone.</strong>
                </p>

                {{-- Resolve for Company --}}
                <form action="{{ route('admin.disputes.resolve-company', ['type' => $type, 'id' => $contract->id]) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-slate-500 mb-1">{!! __('admin.resolution_note') ?? 'Resolution Note (Required)' !!}</label>
                        <textarea name="resolution_note" rows="2" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-sm focus:ring-red-500 focus:border-red-500 outline-none" placeholder="Reason for siding with the client..." required></textarea>
                    </div>
                    <button type="submit" onclick="return confirm('Are you sure you want to refund the client?')" class="w-full bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 font-bold px-4 py-2.5 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2 group">
                        <i class="fa-solid fa-building text-slate-400 group-hover:text-slate-600 transition"></i> {!! __('admin.resolve_favor_company') ?? 'Refund Client (Company Wins)' !!}
                    </button>
                </form>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-red-200"></div>
                    <span class="flex-shrink-0 mx-4 text-red-400 text-xs font-bold">OR</span>
                    <div class="flex-grow border-t border-red-200"></div>
                </div>

                {{-- Resolve for Expert --}}
                <form action="{{ route('admin.disputes.resolve-expert', ['type' => $type, 'id' => $contract->id]) }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-slate-500 mb-1">{!! __('admin.resolution_note') ?? 'Resolution Note (Required)' !!}</label>
                        <textarea name="resolution_note" rows="2" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-sm focus:ring-red-500 focus:border-red-500 outline-none" placeholder="Reason for siding with the expert..." required></textarea>
                    </div>
                    <button type="submit" onclick="return confirm('Are you sure you want to release payment to the expert?')" class="w-full bg-red-600 text-white hover:bg-red-700 font-bold px-4 py-2.5 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2 group">
                        <i class="fa-solid fa-user-tie text-red-200 group-hover:text-white transition"></i> {!! __('admin.resolve_favor_expert') ?? 'Release Payment (Expert Wins)' !!}
                    </button>
                </form>

            </div>
        </div>
        
    </div>
</div>

@endsection
