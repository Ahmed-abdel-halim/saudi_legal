@extends('layouts.admin')

@section('title', 'محادثات البوت واستفسارات المستخدمين')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">سجل محادثات البوت</h1>
        <p class="text-slate-500 mt-1">مراقبة كافة الاستفسارات الحية للمستخدمين والزوار وتحليل جودة إجابات الذكاء الاصطناعي.</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-slate-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">إجمالي المحادثات</span>
        <span class="text-3xl font-black text-slate-800 relative z-10">{{ number_format($stats['total_conversations']) }}</span>
    </div>

    <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-emerald-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-emerald-500 uppercase tracking-wider relative z-10">إجمالي الرسائل</span>
        <span class="text-3xl font-black text-emerald-600 relative z-10">{{ number_format($stats['total_messages']) }}</span>
    </div>

    <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-blue-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-blue-500 uppercase tracking-wider relative z-10">مستخدمين مسجلين</span>
        <span class="text-3xl font-black text-blue-600 relative z-10">{{ number_format($stats['user_conversations']) }}</span>
    </div>

    <div class="bg-white border border-purple-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-purple-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-purple-500 uppercase tracking-wider relative z-10">محادثات الزوار (Guests)</span>
        <span class="text-3xl font-black text-purple-600 relative z-10">{{ number_format($stats['guest_conversations']) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-4 bg-slate-50/50">
        <form method="GET" action="{{ route('admin.ai_chats.index') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[250px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="ابحث بالنص، العنوان، الـ UUID أو بيانات المستخدم..."
                    class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 pl-9 rtl:pr-9 rtl:pl-3 p-2.5 transition outline-none">
            </div>

            {{-- User Type Filter --}}
            <select name="user_type" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2.5 transition min-w-[160px]">
                <option value="">جميع المستخدمين</option>
                <option value="registered" {{ request('user_type') == 'registered' ? 'selected' : '' }}>مستخدمين مسجلين</option>
                <option value="guest" {{ request('user_type') == 'guest' ? 'selected' : '' }}>زوار (Guest Sessions)</option>
            </select>

            {{-- Date From --}}
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg p-2.5 transition">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg p-2.5 transition">

            <button type="submit" class="bg-slate-800 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-slate-900 transition shadow-sm">
                <i class="fa-solid fa-filter me-1"></i> فلترة
            </button>

            @if(request()->anyFilled(['search', 'user_type', 'date_from', 'date_to']))
                <a href="{{ route('admin.ai_chats.index') }}" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-lg text-sm font-bold border border-red-100 hover:bg-red-100 transition">
                    <i class="fa-solid fa-xmark me-1"></i> مسح
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="p-4">المحادثة / العنوان</th>
                    <th class="p-4">صاحب الاستفسار</th>
                    <th class="p-4">عدد الرسائل</th>
                    <th class="p-4">تاريخ المحادثة</th>
                    <th class="p-4 text-left">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($conversations as $conv)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-bold text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-comments"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-slate-900 max-w-xs truncate">{{ $conv->title ?: 'محادثة قانونية بدون عنوان' }}</div>
                                    <span class="text-[11px] font-mono text-slate-400">#{{ substr($conv->uuid, 0, 8) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @if($conv->user)
                                <div class="font-semibold text-slate-800">{{ $conv->user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $conv->user->email }}</div>
                            @else
                                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-2.5 py-1 rounded-full text-xs font-medium border border-purple-100">
                                    <i class="fa-solid fa-user-secret text-[10px]"></i> زائر (Guest)
                                </span>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-slate-700">
                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs">
                                {{ $conv->messages_count }} رسالة
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500 dir-ltr text-right">
                            {{ $conv->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="p-4 text-left">
                            <a href="{{ route('admin.ai_chats.show', $conv->uuid) }}" class="inline-flex items-center gap-1 bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                                <i class="fa-solid fa-eye"></i> عرض الحوار
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-comments text-4xl mb-3 text-slate-300"></i>
                            <p class="font-bold">لا توجد محادثات مطابقة للشروط.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($conversations->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $conversations->links() }}
        </div>
    @endif
</div>

@endsection
