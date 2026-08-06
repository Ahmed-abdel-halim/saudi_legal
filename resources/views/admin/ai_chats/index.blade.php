@extends('layouts.admin')

@section('title', 'محادثات البوت واستفسارات المستخدمين')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-brand-primary/20 text-brand-primary border border-brand-primary/30 flex items-center justify-center text-lg shadow-glow">
                <i class="fa-solid fa-comments"></i>
            </span>
            سجل محادثات البوت
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">مراقبة كافة الاستفسارات الحية للمستخدمين والزوار وتحليل جودة إجابات الذكاء الاصطناعي.</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-dark-card border border-dark-border rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">إجمالي المحادثات</span>
        <span class="text-3xl font-black text-white">{{ number_format($stats['total_conversations']) }}</span>
    </div>

    <div class="bg-dark-card border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">إجمالي الرسائل</span>
        <span class="text-3xl font-black text-emerald-400">{{ number_format($stats['total_messages']) }}</span>
    </div>

    <div class="bg-dark-card border border-brand-primary/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-brand-primary uppercase tracking-wider">مستخدمين مسجلين</span>
        <span class="text-3xl font-black text-white">{{ number_format($stats['user_conversations']) }}</span>
    </div>

    <div class="bg-dark-card border border-purple-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-purple-400 uppercase tracking-wider">محادثات الزوار (Guests)</span>
        <span class="text-3xl font-black text-purple-400">{{ number_format($stats['guest_conversations']) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden mb-8">
    <div class="p-4 bg-dark-navy/60">
        <form method="GET" action="{{ route('admin.ai_chats.index') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[250px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="ابحث بالنص، العنوان، الـ UUID أو بيانات المستخدم..."
                    class="w-full bg-dark-navy border border-dark-border text-white text-xs rounded-xl focus:border-brand-primary pl-9 rtl:pr-9 rtl:pl-3 p-2.5 transition outline-none">
            </div>

            {{-- User Type Filter --}}
            <select name="user_type" class="bg-dark-navy border border-dark-border text-gray-300 text-xs rounded-xl focus:border-brand-primary p-2.5 transition min-w-[160px]">
                <option value="">جميع المستخدمين</option>
                <option value="registered" {{ request('user_type') == 'registered' ? 'selected' : '' }}>مستخدمين مسجلين</option>
                <option value="guest" {{ request('user_type') == 'guest' ? 'selected' : '' }}>زوار (Guest Sessions)</option>
            </select>

            {{-- Date From --}}
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-dark-navy border border-dark-border text-gray-300 text-xs rounded-xl p-2.5 transition">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-dark-navy border border-dark-border text-gray-300 text-xs rounded-xl p-2.5 transition">

            <button type="submit" class="bg-gradient-to-r from-brand-primary to-brand-secondary text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-glow">
                <i class="fa-solid fa-filter me-1"></i> فلترة
            </button>

            @if(request()->anyFilled(['search', 'user_type', 'date_from', 'date_to']))
                <a href="{{ route('admin.ai_chats.index') }}" class="bg-red-500/10 text-red-400 px-4 py-2.5 rounded-xl text-xs font-bold border border-red-500/30 hover:bg-red-500/20 transition">
                    <i class="fa-solid fa-xmark me-1"></i> مسح
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-xs text-gray-300">
            <thead class="bg-dark-navy/80 text-gray-400 text-[11px] font-extrabold uppercase tracking-wider border-b border-dark-border">
                <tr>
                    <th class="p-4">المحادثة / العنوان</th>
                    <th class="p-4">صاحب الاستفسار</th>
                    <th class="p-4">عدد الرسائل</th>
                    <th class="p-4">تاريخ المحادثة</th>
                    <th class="p-4 text-left">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border/60">
                @forelse($conversations as $conv)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="p-4 font-bold text-white">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-comments"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-white max-w-xs truncate text-xs">{{ $conv->title ?: 'محادثة قانونية بدون عنوان' }}</div>
                                    <span class="text-[11px] font-mono text-gray-500">#{{ substr($conv->uuid, 0, 8) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @if($conv->user)
                                <div class="font-semibold text-white text-xs">{{ $conv->user->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $conv->user->email }}</div>
                            @else
                                <span class="inline-flex items-center gap-1 bg-purple-500/10 text-purple-300 border border-purple-500/30 px-2.5 py-1 rounded-full text-[11px] font-medium">
                                    <i class="fa-solid fa-user-secret text-[10px] text-purple-400"></i> زائر (Guest)
                                </span>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-gray-300">
                            <span class="bg-dark-navy border border-dark-border px-2.5 py-1 rounded-lg text-[11px]">
                                {{ $conv->messages_count }} رسالة
                            </span>
                        </td>
                        <td class="p-4 text-[11px] text-gray-400 dir-ltr text-right">
                            {{ $conv->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="p-4 text-left">
                            <a href="{{ route('admin.ai_chats.show', $conv->uuid) }}" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-brand-primary to-brand-secondary text-white px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-glow hover:scale-105">
                                <i class="fa-solid fa-eye"></i> عرض الحوار
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <i class="fa-solid fa-comments text-4xl mb-3 text-gray-600"></i>
                            <p class="font-bold text-xs">لا توجد محادثات مطابقة للشروط.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($conversations->hasPages())
        <div class="p-4 border-t border-dark-border">
            {{ $conversations->links() }}
        </div>
    @endif
</div>

@endsection
