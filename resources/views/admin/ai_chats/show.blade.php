@extends('layouts.admin')

@section('title', 'تفاصيل المحادثة - ' . ($conversation->title ?: 'سجل الحوار'))

@section('content')

{{-- Top Header & Navigation --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <a href="{{ route('admin.ai_chats.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-emerald-400 mb-2 transition">
            <i class="fa-solid fa-arrow-right rtl:rotate-180"></i> العودة لسجل المحادثات
        </a>
        <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
            <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm shadow-green-glow">
                <i class="fa-solid fa-comments"></i>
            </span>
            {{ $conversation->title ?: 'محادثة قانونية مع البوت' }}
        </h1>
        <p class="text-xs text-gray-500 mt-1 font-mono">UUID: {{ $conversation->uuid }}</p>
    </div>

    <div class="flex items-center gap-3">
        @if($conversation->user)
            <div class="bg-dark-card border border-dark-border rounded-xl px-4 py-2 flex items-center gap-3 shadow-md">
                <div class="w-8 h-8 rounded-full bg-brand-primary/20 text-brand-primary border border-brand-primary/30 flex items-center justify-center text-xs font-bold shadow-glow">
                    {{ mb_substr($conversation->user->name, 0, 1) }}
                </div>
                <div>
                    <div class="text-xs font-bold text-white">{{ $conversation->user->name }}</div>
                    <div class="text-[11px] text-gray-400">{{ $conversation->user->email }}</div>
                </div>
            </div>
        @else
            <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl px-4 py-2 text-purple-300 text-xs font-bold flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-user-secret text-purple-400"></i> محادثة زائر (Guest Session)
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl text-xs font-bold flex items-center justify-between shadow-md">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
        <a href="{{ route('dashboard.expert.legal_workbench') }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1 rounded-lg text-xs font-bold transition shadow-green-glow">
            فتح شاشة الخبراء Workbench <i class="fa-solid fa-up-right-from-square ms-1"></i>
        </a>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-md">
        <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Conversation Timeline --}}
<div class="max-w-4xl mx-auto space-y-6 mb-12">
    @forelse($conversation->messages as $msg)
        @if($msg->role === 'user')
            {{-- User Message Bubble --}}
            <div class="flex items-start gap-3 flex-row-reverse">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary text-white flex items-center justify-center text-xs shadow-glow flex-shrink-0">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="bg-dark-card border border-brand-primary/40 text-white rounded-2xl rounded-tr-none p-5 shadow-xl max-w-2xl text-sm leading-relaxed">
                    <div class="text-[11px] text-gray-400 mb-1 font-bold flex items-center justify-between gap-4">
                        <span class="text-brand-primary font-extrabold">سؤال المستخدم</span>
                        <span class="dir-ltr text-gray-500">{{ $msg->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="whitespace-pre-line text-gray-100 font-medium">{{ $msg->message }}</div>

                    {{-- Actions --}}
                    <div class="mt-4 pt-3 border-t border-dark-border flex items-center justify-end gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.ai_chats.convert_task', $msg->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-green-glow inline-flex items-center gap-1.5" onclick="return confirm('هل تريد تحويل هذا الاستفسار لمهمة مراجعة خبير على الـ Workbench؟')">
                                <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i> تحويل لمهمة تقييم خبير (RLHF Task)
                            </button>
                        </form>
                        {{-- 🌐 1-Click SEO Publish Button --}}
                        <form method="POST" action="{{ route('admin.ai_chats.publish_public', $conversation->id) }}" class="inline">
                            @csrf
                            <input type="hidden" name="message_id" value="{{ $msg->id }}">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-glow inline-flex items-center gap-1.5"
                                    onclick="return confirm('نشر هذا السؤال والإجابة كصفحة عامة مفهرسة في جوجل؟')">
                                <i class="fa-solid fa-globe text-sky-300"></i> نشر كصفحة SEO عامة 🌐
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            {{-- Assistant Response Bubble --}}
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white flex items-center justify-center text-xs shadow-green-glow flex-shrink-0">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="bg-dark-card border border-dark-border rounded-2xl rounded-tl-none p-5 shadow-xl max-w-2xl text-sm leading-relaxed text-gray-200">
                    <div class="text-[11px] text-emerald-400 mb-2 font-bold flex items-center justify-between gap-4 border-b border-dark-border/60 pb-2">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-brain text-emerald-400"></i> رد الذكاء الاصطناعي (رديف AI)</span>
                        <span class="dir-ltr text-gray-500 font-normal">{{ $msg->created_at->format('Y-m-d H:i') }}</span>
                    </div>

                    <div class="text-gray-200 leading-relaxed whitespace-pre-line text-sm">
                        {{ $msg->message }}
                    </div>

                    {{-- Citations / Legal Articles Used --}}
                    @php
                        $citationItems = [];
                        if (!empty($msg->citations) && is_array($msg->citations)) {
                            if (isset($msg->citations['items']) && is_array($msg->citations['items'])) {
                                $citationItems = $msg->citations['items'];
                            } else {
                                $citationItems = array_filter($msg->citations, 'is_array');
                            }
                        }
                    @endphp

                    @if(!empty($citationItems))
                        <div class="mt-5 pt-4 border-t border-dark-border">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <i class="fa-solid fa-book-bookmark text-emerald-400"></i> المراجع والنصوص القانونية المستشهد بها ({{ count($citationItems) }}):
                            </h4>
                            <div class="space-y-3">
                                @foreach($citationItems as $cite)
                                    @php
                                        if (!is_array($cite)) continue;

                                        // Badge label classification
                                        $rawType = $cite['type'] ?? ($cite['article'] ?? '');
                                        $badge = 'مرجع قانوني';
                                        if (in_array($rawType, ['judgment', 'Court_Judgment', 'legal_qa_pair', 'أحكام قضائية'])) {
                                            $badge = 'أحكام قضائية';
                                        } elseif (in_array($rawType, ['article', 'نص نظام'])) {
                                            $badge = 'نص نظام';
                                        } elseif (!empty($cite['article'])) {
                                            $badge = $cite['article'];
                                        }

                                        $systemName = $cite['system'] ?? ($cite['law_system'] ?? ($cite['system_name'] ?? ''));
                                        $caseRef = $cite['case_reference'] ?? ($cite['title'] ?? null);
                                        $artNum = $cite['article_number'] ?? null;

                                        if (!empty($caseRef) && is_numeric(trim($caseRef))) {
                                            $title = "القضية رقم " . trim($caseRef);
                                        } elseif (!empty($cite['title']) && is_numeric(trim($cite['title']))) {
                                            $title = "القضية رقم " . trim($cite['title']);
                                        } else {
                                            $title = $cite['title'] ?? ($caseRef ?: ($systemName ?: 'مرجع قانوني مرتبط'));
                                        }

                                        $textContent = $cite['text'] ?? ($cite['content'] ?? null);
                                    @endphp
                                    <div class="bg-dark-navy/90 border border-dark-border rounded-xl p-4 text-xs shadow-md">
                                        <div class="font-bold text-white flex flex-wrap items-center gap-2 mb-2">
                                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2.5 py-0.5 rounded-md text-[11px] font-bold">
                                                <i class="fa-solid fa-scale-balanced text-[10px] me-1"></i> {{ $badge }}
                                            </span>
                                            
                                            <span class="text-white font-extrabold text-sm">{{ $title }}</span>

                                            @if(!empty($systemName) && $systemName !== $title)
                                                <span class="bg-slate-800 text-gray-300 border border-slate-700 px-2 py-0.5 rounded text-[11px] font-bold">
                                                    {{ $systemName }}
                                                </span>
                                            @endif

                                            @if(!empty($artNum) && $artNum !== 'غير محدد')
                                                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded text-[11px] font-bold">
                                                    المادة ({{ $artNum }})
                                                </span>
                                            @endif
                                        </div>

                                        @if(!empty($textContent))
                                            <div x-data="{ expanded: false }" class="mt-2 text-gray-300 leading-relaxed bg-dark-card p-3 rounded-lg border border-dark-border text-xs">
                                                <div class="whitespace-pre-line" x-show="expanded" x-cloak>
                                                    <i class="fa-solid fa-quote-right text-emerald-400/50 text-xs me-1"></i>
                                                    {{ trim($textContent) }}
                                                </div>
                                                <div class="whitespace-pre-line" x-show="!expanded">
                                                    <i class="fa-solid fa-quote-right text-emerald-400/50 text-xs me-1"></i>
                                                    {{ Str::limit(trim(strip_tags($textContent)), 350) }}
                                                </div>
                                                @if(mb_strlen(strip_tags($textContent)) > 350)
                                                    <button @click="expanded = !expanded" class="mt-2 text-emerald-400 hover:text-emerald-300 font-bold text-[11px] inline-flex items-center gap-1">
                                                        <span x-text="expanded ? 'طي النص' : 'قراءة نص الحكم/المادة بالكامل...'"></span>
                                                        <i class="fa-solid" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @empty
        <div class="text-center p-8 bg-dark-card rounded-2xl border border-dark-border text-gray-500">
            لا توجد رسائل في هذه المحادثة.
        </div>
    @endforelse
</div>

@endsection
