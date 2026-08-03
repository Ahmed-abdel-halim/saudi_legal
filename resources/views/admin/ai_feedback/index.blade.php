@extends('layouts.admin')

@section('title', 'تقييمات إجابات المساعد الذكي')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">تقييمات وملاحظات المساعد الذكي</h1>
        <p class="text-slate-500 mt-1">مراجعة تقييمات المستخدمين للإجابات (👍 / 👎) وقراءة أسباب وملاحظات عدم الإفادة لتطوير النظام.</p>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm text-sm mb-6 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">إجمالي التقييمات</span>
        <span class="text-3xl font-black text-slate-800 relative z-10">{{ number_format($stats['total_feedback']) }}</span>
    </div>

    <div class="bg-white border border-emerald-200 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider relative z-10">إجابات مفيدة 👍</span>
        <span class="text-3xl font-black text-emerald-600 relative z-10">{{ number_format($stats['total_likes']) }}</span>
    </div>

    <div class="bg-white border border-rose-200 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider relative z-10">إجابات غير مفيدة 👎</span>
        <span class="text-3xl font-black text-rose-600 relative z-10">{{ number_format($stats['total_dislikes']) }}</span>
    </div>

    <div class="bg-white border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider relative z-10">تقييمات بملاحظات 💬</span>
        <span class="text-3xl font-black text-amber-600 relative z-10">{{ number_format($stats['with_reasons']) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-4 bg-slate-50/50">
        <form method="GET" action="{{ route('admin.ai_feedback.index') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[250px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="ابحث بسبب عدم الإفادة، بالسؤال، بالإجابة أو بالاسم..."
                    class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 pl-9 rtl:pr-9 rtl:pl-3 p-2.5 transition outline-none">
            </div>

            {{-- Rating Filter --}}
            <select name="rating" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2.5 transition min-w-[160px]">
                <option value="">جميع التقييمات</option>
                <option value="like" {{ request('rating') == 'like' ? 'selected' : '' }}>👍 مفيدة (Like)</option>
                <option value="dislike" {{ request('rating') == 'dislike' ? 'selected' : '' }}>👎 غير مفيدة (Dislike)</option>
            </select>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm px-5 py-2.5 rounded-lg transition duration-200 shadow-sm">
                تصفية
            </button>

            @if(request()->anyFilled(['search', 'rating']))
                <a href="{{ route('admin.ai_feedback.index') }}" class="text-slate-500 hover:text-slate-800 text-sm font-semibold px-2">
                    إلغاء التصفية
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Feedbacks List Table --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                <tr>
                    <th class="px-6 py-4">التقييم</th>
                    <th class="px-6 py-4">المستخدم</th>
                    <th class="px-6 py-4">سؤال المستخدم</th>
                    <th class="px-6 py-4">سبب عدم الإفادة (الملاحظة)</th>
                    <th class="px-6 py-4">إجابة المساعد</th>
                    <th class="px-6 py-4">التاريخ</th>
                    <th class="px-6 py-4 text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($feedbacks as $fb)
                    <tr class="hover:bg-slate-50/80 transition">
                        {{-- Rating Badge --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($fb->rating === 'like')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-thumbs-up"></i> مفيدة
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fa-solid fa-thumbs-down"></i> غير مفيدة
                                </span>
                            @endif
                        </td>

                        {{-- User Details --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($fb->user)
                                <div class="font-bold text-slate-800">{{ $fb->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $fb->user->email }}</div>
                            @else
                                <span class="text-slate-500 italic text-xs font-semibold">زائر (Guest)</span>
                            @endif
                        </td>

                        {{-- User Query --}}
                        <td class="px-6 py-4 max-w-xs">
                            <div class="text-slate-800 font-medium line-clamp-2" title="{{ $fb->user_query }}">
                                {{ $fb->user_query ?? '—' }}
                            </div>
                        </td>

                        {{-- Reason / Note --}}
                        <td class="px-6 py-4 max-w-xs">
                            @if($fb->reason)
                                <div class="bg-amber-50 border border-amber-200 text-amber-900 text-xs p-2.5 rounded-xl font-semibold leading-relaxed">
                                    <i class="fa-solid fa-comment-dots text-amber-500 ml-1"></i>
                                    {{ $fb->reason }}
                                </div>
                            @else
                                <span class="text-slate-400 text-xs italic">بدون ملاحظة</span>
                            @endif
                        </td>

                        {{-- AI Response --}}
                        <td class="px-6 py-4 max-w-sm">
                            <div class="text-slate-600 text-xs line-clamp-2 leading-relaxed" title="{{ strip_tags($fb->ai_response) }}">
                                {{ Str::limit(strip_tags($fb->ai_response), 120) ?? '—' }}
                            </div>
                        </td>

                        {{-- Date --}}
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                            <div>{{ $fb->created_at->format('Y/m/d') }}</div>
                            <div class="text-slate-400">{{ $fb->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <form action="{{ route('admin.ai_feedback.destroy', $fb->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت تأكد من حذف هذا التقييم؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="حذف">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fa-solid fa-comment-slash text-3xl text-slate-300"></i>
                                <span>لا توجد تقييمات مسجلة حالياً.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($feedbacks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $feedbacks->links() }}
        </div>
    @endif
</div>

@endsection
