@extends('layouts.admin')

@section('title', __('admin.sentiment_tasks') ?? 'Sentiment Tasks')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.sentiment_tasks') !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.tasks_tracker_desc') ?? 'Monitor all imported sentiment tasks across the platform.' !!}</p>
    </div>
</div>

{{-- Tasks Tracker --}}
<div class="mt-8 pt-8 border-t border-slate-200">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">{!! __('admin.tasks_tracker') ?? 'Tasks Tracker' !!}</h2>
            <p class="text-sm text-slate-500 mt-1">{!! __('admin.tasks_tracker_desc') ?? 'Monitor all imported sentiment tasks across CSV files.' !!}</p>
        </div>
        
        <form action="{{ route('admin.sentiment.index') }}" method="GET" class="flex flex-wrap gap-3">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400 rtl:right-3 rtl:left-auto text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{!! __('admin.search_tasks') ?? 'Search text or file...' !!}" class="w-full md:w-64 pl-9 rtl:pr-9 rtl:pl-4 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition shadow-sm">
            </div>
            <select name="status" class="bg-white border border-slate-200 rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-primary/20 outline-none shadow-sm" onchange="this.form.submit()">
                <option value="">{!! __('admin.all_statuses') ?? 'All Statuses' !!}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{!! __('admin.pending') ?? 'Pending' !!}</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{!! __('admin.in_progress') ?? 'In Progress' !!}</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{!! __('admin.completed') ?? 'Completed' !!}</option>
            </select>
        </form>
    </div>

    {{-- Batches Overview Sidebar + Tasks Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        {{-- CSV Files Batches --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm"><i class="fa-solid fa-folder-open text-primary mr-2 rtl:ml-2"></i> {!! __('admin.uploaded_files') ?? 'Uploaded Files' !!}</h3>
                </div>
                <div class="p-0 max-h-[600px] overflow-y-auto">
                    @forelse($batches as $batch)
                        <div class="px-5 py-4 border-b border-slate-50 hover:bg-slate-50 transition cursor-pointer group" onclick="document.querySelector('input[name=search]').value='{{ $batch->csv_file }}'; document.forms[1].submit();">
                            <p class="text-xs font-bold text-slate-700 truncate group-hover:text-primary transition" title="{{ $batch->csv_file }}">{{ $batch->csv_file }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-[10px] font-bold text-slate-400">{{ number_format($batch->total) }} {!! __('admin.tasks') ?? 'Tasks' !!}</span>
                                @if($batch->total > 0 && $batch->completed == $batch->total)
                                    <span class="bg-emerald-100 text-emerald-700 text-[9px] px-2 py-0.5 rounded-md font-bold">100% {!! __('admin.done') ?? 'Done' !!}</span>
                                @else
                                    <span class="bg-indigo-50 text-primary text-[9px] px-2 py-0.5 rounded-md font-bold">{{ round(($batch->completed / max($batch->total, 1)) * 100) }}%</span>
                                @endif
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1 mt-2">
                                <div class="bg-primary h-1 rounded-full transition-all" style="width: {{ ($batch->completed / max($batch->total, 1)) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-slate-400 text-xs py-8">
                            <i class="fa-solid fa-box-open text-3xl text-slate-200 mb-3 block"></i>
                            {!! __('admin.no_batches') ?? 'No files uploaded yet.' !!}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tasks Table --}}
        <div class="lg:col-span-3">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm rtl:text-right">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-bold tracking-wider text-[11px] uppercase">ID</th>
                                <th class="px-5 py-4 font-bold tracking-wider text-[11px] uppercase">{!! __('admin.comment_text') ?? 'Comment / Text' !!}</th>
                                <th class="px-5 py-4 font-bold tracking-wider text-[11px] uppercase">{!! __('admin.domain') ?? 'Domain' !!}</th>
                                <th class="px-5 py-4 font-bold tracking-wider text-[11px] uppercase">{!! __('admin.status_col') ?? 'Status' !!}</th>
                                <th class="px-5 py-4 font-bold tracking-wider text-[11px] uppercase">{!! __('admin.expert') ?? 'Expert' !!}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tasks as $task)
                                <tr class="hover:bg-slate-50/50 transition relative group">
                                    <td class="px-5 py-4 text-xs font-bold text-slate-400">#{{ $task->id }}</td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-medium text-slate-800 line-clamp-2 max-w-sm" title="{{ $task->comment_text }}">
                                            {{ $task->comment_text }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1 flex flex-wrap gap-2">
                                            <span>{!! __('admin.proposed') ?? 'Proposed' !!}: <strong class="text-indigo-500">{{ $task->proposed_classification }}</strong></span>
                                            @if($task->correct_classification)
                                                <span>{!! __('admin.actual') ?? 'Actual' !!}: <strong class="{{ $task->is_correct ? 'text-emerald-500' : 'text-red-500' }}">{{ $task->correct_classification }}</strong></span>
                                            @endif
                                            @if($task->csv_file)
                                                <span class="text-slate-300">|</span>
                                                <span class="text-slate-400 flex items-center gap-1"><i class="fa-solid fa-file-csv"></i> {{ Str::limit($task->csv_file, 15) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg text-[10px] font-bold">{{ $task->domain }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($task->status == 'pending')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] uppercase tracking-wide font-bold bg-amber-50 text-amber-600 border border-amber-100"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {!! __('admin.pending') ?? 'Pending' !!}</span>
                                        @elseif($task->status == 'in_progress')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] uppercase tracking-wide font-bold bg-blue-50 text-blue-600 border border-blue-100"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> {!! __('admin.in_progress') ?? 'In Progress' !!}</span>
                                        @elseif($task->status == 'completed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] uppercase tracking-wide font-bold bg-emerald-50 text-emerald-600 border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {!! __('admin.completed') ?? 'Completed' !!}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] uppercase tracking-wide font-bold bg-slate-50 text-slate-600 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ ucfirst($task->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($task->expert)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[9px] font-bold">
                                                    {{ mb_substr($task->expert->name, 0, 1) }}
                                                </div>
                                                <span class="text-xs font-bold text-slate-700 whitespace-nowrap">{{ $task->expert->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-1 rounded">{{ __('admin.unassigned') ?? 'Unassigned' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-16 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                                <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                                            </div>
                                            <p class="font-bold text-slate-700 text-base">{!! __('admin.no_tasks_found') ?? 'No tasks found' !!}</p>
                                            <p class="text-sm mt-1 text-slate-400">{!! __('admin.upload_dataset_to_start') ?? 'Upload a CSV dataset above to generate tasks, or adjust search filters.' !!}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($tasks->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $tasks->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
