@extends('layouts.admin')

@section('title', 'رسائل اتصل بنا')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center text-lg shadow-glow">
                <i class="fa-solid fa-envelope-open-text"></i>
            </span>
            رسائل اتصل بنا
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">متابعة كافة الاستفسارات والرسائل الواردة عبر نموذج "تواصل معنا" والتواصل المباشر مع أصحابها.</p>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl shadow-sm text-sm mb-6 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-dark-card border border-dark-border rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">إجمالي الرسائل</span>
        <span class="text-3xl font-black text-white">{{ number_format($stats['total']) }}</span>
    </div>

    <div class="bg-dark-card border border-rose-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-rose-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-envelope"></i> رسائل غير مقروءة
        </span>
        <span class="text-3xl font-black text-rose-400">{{ number_format($stats['unread']) }}</span>
    </div>

    <div class="bg-dark-card border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-envelope-open"></i> رسائل مقروءة
        </span>
        <span class="text-3xl font-black text-emerald-400">{{ number_format($stats['read']) }}</span>
    </div>

    <div class="bg-dark-card border border-teal-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-teal-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-calendar-day"></i> رسائل اليوم
        </span>
        <span class="text-3xl font-black text-teal-400">{{ number_format($stats['today']) }}</span>
    </div>
</div>

{{-- Category Tabs --}}
<div class="flex items-center gap-2 mb-4 overflow-x-auto pb-2">
    <a href="{{ route('admin.contact_messages.index', array_merge(request()->except('status', 'page'))) }}" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ !request('status') ? 'bg-brand-primary text-white shadow-glow' : 'bg-dark-card border border-dark-border text-gray-400 hover:text-white' }}">
        <i class="fa-solid fa-inbox"></i>
        <span>جميع الرسائل</span>
        <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-black/20">{{ $stats['total'] }}</span>
    </a>
    <a href="{{ route('admin.contact_messages.index', array_merge(request()->except('status', 'page'), ['status' => 'unread'])) }}" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ request('status') === 'unread' ? 'bg-rose-600 text-white shadow-glow' : 'bg-dark-card border border-dark-border text-gray-400 hover:text-white' }}">
        <i class="fa-solid fa-bell text-rose-400"></i>
        <span>غير مقروءة</span>
        @if($stats['unread'] > 0)
            <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-rose-900/60 text-rose-200 border border-rose-500/40 font-black animate-pulse">{{ $stats['unread'] }}</span>
        @else
            <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-black/20">0</span>
        @endif
    </a>
    <a href="{{ route('admin.contact_messages.index', array_merge(request()->except('status', 'page'), ['status' => 'read'])) }}" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ request('status') === 'read' ? 'bg-emerald-600 text-white shadow-glow' : 'bg-dark-card border border-dark-border text-gray-400 hover:text-white' }}">
        <i class="fa-solid fa-check-double text-emerald-400"></i>
        <span>تمت قراءتها</span>
        <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-black/20">{{ $stats['read'] }}</span>
    </a>
</div>

{{-- Filter Bar --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden mb-8">
    <div class="p-4 bg-white/[0.02]">
        <form method="GET" action="{{ route('admin.contact_messages.index') }}" class="flex flex-wrap gap-3 items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="ابحث بالاسم، البريد، الجوال، الموضوع أو الرسالة..."
                    class="w-full bg-dark-navy/60 border border-dark-border text-white text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 pl-9 rtl:pr-9 rtl:pl-3 py-2.5 transition outline-none">
            </div>

            {{-- Subject Filter --}}
            <select name="subject" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[160px]">
                <option value="">جميع المواضيع</option>
                <option value="General Inquiry" {{ request('subject') === 'General Inquiry' ? 'selected' : '' }}>استفسار عام</option>
                <option value="Technical Support" {{ request('subject') === 'Technical Support' ? 'selected' : '' }}>دعم فني</option>
                <option value="Sales/Partnership" {{ request('subject') === 'Sales/Partnership' ? 'selected' : '' }}>مبيعات / شراكة</option>
                <option value="Billing Issue" {{ request('subject') === 'Billing Issue' ? 'selected' : '' }}>مشكلة في الفواتير</option>
            </select>

            <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                <i class="fa-solid fa-filter"></i> تصفية
            </button>

            @if(request()->hasAny(['q', 'subject', 'status']))
                <a href="{{ route('admin.contact_messages.index') }}" class="bg-red-500/10 text-red-400 px-4 py-2.5 rounded-xl text-xs font-bold border border-red-500/30 hover:bg-red-500/20 transition">
                    <i class="fa-solid fa-rotate-left"></i> إعادة ضبط
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Messages Table --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-white/[0.02] border-b border-dark-border text-gray-400 text-xs uppercase font-bold tracking-wider">
                <tr>
                    <th class="p-4 w-12 text-center">الحالة</th>
                    <th class="p-4">المرسل</th>
                    <th class="p-4">الموضوع</th>
                    <th class="p-4 max-w-md">مقتطف من الرسالة</th>
                    <th class="p-4">تاريخ الإرسال</th>
                    <th class="p-4 text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border text-gray-300 text-xs">
                @forelse($messages as $msg)
                <tr class="hover:bg-white/[0.03] transition {{ !$msg->is_read ? 'bg-teal-500/[0.04]' : '' }}">
                    {{-- Status --}}
                    <td class="p-4 text-center">
                        @if(!$msg->is_read)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 shadow-sm" title="غير مقروءة">
                                <i class="fa-solid fa-circle text-[8px] animate-pulse"></i>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-500/10 text-gray-400 border border-gray-500/20" title="مقروءة">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </span>
                        @endif
                    </td>

                    {{-- Sender Info --}}
                    <td class="p-4">
                        <div class="font-bold text-white text-xs flex items-center gap-2">
                            <span>{{ $msg->name }}</span>
                            @if(!$msg->is_read)
                                <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 px-1.5 py-0.5 rounded text-[9px] font-black">جديد</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-gray-400 mt-0.5 flex flex-col gap-0.5">
                            <a href="mailto:{{ $msg->email }}" class="text-teal-400 hover:underline flex items-center gap-1">
                                <i class="fa-regular fa-envelope text-[10px]"></i> {{ $msg->email }}
                            </a>
                            @if($msg->phone)
                                <a href="tel:{{ $msg->phone }}" class="text-gray-400 hover:text-white flex items-center gap-1" dir="ltr">
                                    <i class="fa-solid fa-phone text-[10px]"></i> {{ $msg->phone }}
                                </a>
                            @endif
                        </div>
                    </td>

                    {{-- Subject --}}
                    <td class="p-4">
                        @php
                            $subjectBadges = [
                                'General Inquiry'    => ['bg' => 'bg-blue-500/15', 'text' => 'text-blue-400', 'border' => 'border-blue-500/30', 'label' => 'استفسار عام'],
                                'Technical Support'  => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30', 'label' => 'دعم فني'],
                                'Sales/Partnership'  => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30', 'label' => 'مبيعات / شراكة'],
                                'Billing Issue'      => ['bg' => 'bg-purple-500/15', 'text' => 'text-purple-400', 'border' => 'border-purple-500/30', 'label' => 'مشكلة فواتير'],
                            ];
                            $sb = $subjectBadges[$msg->subject] ?? ['bg' => 'bg-gray-500/15', 'text' => 'text-gray-300', 'border' => 'border-gray-500/30', 'label' => $msg->subject ?: 'غير محدد'];
                        @endphp
                        <span class="{{ $sb['bg'] }} {{ $sb['text'] }} border {{ $sb['border'] }} px-2 py-1 rounded-lg text-[10px] font-bold inline-block">
                            {{ $sb['label'] }}
                        </span>
                    </td>

                    {{-- Message Excerpt --}}
                    <td class="p-4 max-w-md">
                        <div class="text-gray-300 line-clamp-2 text-xs leading-relaxed">
                            {{ $msg->message }}
                        </div>
                        @if($msg->admin_notes)
                            <div class="mt-1 text-[10px] text-amber-400 flex items-center gap-1">
                                <i class="fa-solid fa-note-sticky"></i> ملاحظة إدارية مسجلة
                            </div>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td class="p-4 whitespace-nowrap">
                        <div class="text-white text-xs font-semibold">{{ $msg->created_at->diffForHumans() }}</div>
                        <span class="text-[10px] text-gray-500">{{ $msg->created_at->format('Y-m-d H:i') }}</span>
                    </td>

                    {{-- Actions --}}
                    <td class="p-4 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('admin.contact_messages.show', $msg->id) }}" class="p-2 bg-teal-500/10 text-teal-400 border border-teal-500/20 hover:bg-teal-500/20 rounded-lg transition" title="عرض التفاصيل والرد">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>

                            <form action="{{ route('admin.contact_messages.toggle_read', $msg->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-2 {{ $msg->is_read ? 'bg-gray-500/10 text-gray-400 border-gray-500/20 hover:bg-gray-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' }} border rounded-lg transition" title="{{ $msg->is_read ? 'تحديد كغير مقروء' : 'تحديد كمقروء' }}">
                                    <i class="fa-solid {{ $msg->is_read ? 'fa-envelope' : 'fa-envelope-open' }} text-xs"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.contact_messages.destroy', $msg->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة نهائياً؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 rounded-lg transition" title="حذف الرسالة">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <span class="w-14 h-14 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center text-2xl text-gray-600">
                                <i class="fa-solid fa-inbox"></i>
                            </span>
                            <span class="text-sm font-bold text-gray-400">لا توجد رسائل تواصل مطابقة حالياً</span>
                            <span class="text-xs text-gray-600">أي رسالة يرسلها الزوار من صفحة اتصل بنا ستظهر فوراً في هذا الجدول.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($messages->hasPages())
    <div class="p-4 border-t border-dark-border bg-white/[0.01]">
        {{ $messages->links() }}
    </div>
    @endif
</div>

@endsection

