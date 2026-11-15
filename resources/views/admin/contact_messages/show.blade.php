@extends('layouts.admin')

@section('title', 'تفاصيل رسالة التواصل - ' . $message->name)

@section('content')

{{-- Back Link & Header --}}
<div class="mb-8">
    <a href="{{ route('admin.contact_messages.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-teal-400 mb-3 transition">
        <i class="fa-solid fa-arrow-right rtl:rotate-180"></i> العودة لسجل رسائل اتصل بنا
    </a>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center text-lg shadow-glow">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </span>
                <span>رسالة من: {{ $message->name }}</span>
            </h1>
            <p class="text-gray-400 text-xs mt-1">تاريخ الاستلام: {{ $message->created_at->format('Y-m-d H:i') }} ({{ $message->created_at->diffForHumans() }})</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Toggle Read Status --}}
            <form action="{{ route('admin.contact_messages.toggle_read', $message->id) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-1.5 {{ $message->is_read ? 'bg-gray-500/10 text-gray-400 border-gray-500/30 hover:bg-gray-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20' }}">
                    <i class="fa-solid {{ $message->is_read ? 'fa-envelope' : 'fa-check' }}"></i>
                    <span>{{ $message->is_read ? 'تحديد كغير مقروءة' : 'تحديد كمقروءة' }}</span>
                </button>
            </form>

            {{-- Delete --}}
            <form action="{{ route('admin.contact_messages.destroy', $message->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة نهائياً؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i>
                    <span>حذف الرسالة</span>
                </button>
            </form>
        </div>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left/Main 2 Cols: Message Body & Reply Actions --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Message Card --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-dark-border flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 font-bold">الموضوع:</span>
                    @php
                        $subjectBadges = [
                            'General Inquiry'    => ['bg' => 'bg-blue-500/15', 'text' => 'text-blue-400', 'border' => 'border-blue-500/30', 'label' => 'استفسار عام'],
                            'Technical Support'  => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30', 'label' => 'دعم فني'],
                            'Sales/Partnership'  => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30', 'label' => 'مبيعات / شراكة'],
                            'Billing Issue'      => ['bg' => 'bg-purple-500/15', 'text' => 'text-purple-400', 'border' => 'border-purple-500/30', 'label' => 'مشكلة فواتير'],
                        ];
                        $sb = $subjectBadges[$message->subject] ?? ['bg' => 'bg-gray-500/15', 'text' => 'text-gray-300', 'border' => 'border-gray-500/30', 'label' => $message->subject ?: 'عام'];
                    @endphp
                    <span class="{{ $sb['bg'] }} {{ $sb['text'] }} border {{ $sb['border'] }} px-2.5 py-1 rounded-lg text-xs font-bold">
                        {{ $sb['label'] }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <a href="mailto:{{ $message->email }}?subject=رد على استفسارك في منصة رديف: {{ urlencode($message->subject) }}" class="bg-teal-600 hover:bg-teal-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                        <i class="fa-solid fa-reply"></i> رد عبر الإيميل
                    </a>

                    @if($message->phone)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $message->phone);
                            if (str_starts_with($cleanPhone, '05')) {
                                $cleanPhone = '966' . substr($cleanPhone, 1);
                            }
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                            <i class="fa-brands fa-whatsapp text-sm"></i> محادثة واتساب
                        </a>
                    @endif
                </div>
            </div>

            {{-- Body Content --}}
            <div class="prose prose-invert max-w-none text-sm text-gray-200 leading-relaxed whitespace-pre-wrap font-sans bg-dark-navy/50 p-6 rounded-xl border border-white/5">
{{ $message->message }}
            </div>
        </div>

        {{-- Admin Notes Form --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl p-6">
            <h3 class="text-sm font-bold text-white mb-2 flex items-center gap-2">
                <i class="fa-solid fa-note-sticky text-amber-400"></i>
                ملاحظات فريق الإدارة (داخلية فقط)
            </h3>
            <p class="text-xs text-gray-400 mb-4">هذه الملاحظات خاصة بإدارة الموقع فقط لمتابعة حالة التواصل مع العميل.</p>

            <form action="{{ route('admin.contact_messages.notes', $message->id) }}" method="POST">
                @csrf
                <textarea name="admin_notes" rows="3" placeholder="أضف ملاحظة (مثال: تم التواصل هاتفياً وتوضيح الباقة المناسبة...)" class="w-full bg-dark-navy/60 border border-dark-border text-white text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 p-3 transition outline-none mb-3">{{ old('admin_notes', $message->admin_notes) }}</textarea>
                <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> حفظ الملاحظات
                </button>
            </form>
        </div>

    </div>

    {{-- Right 1 Col: Sender Profile & Metadata --}}
    <div class="space-y-6">

        {{-- Sender Details --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl p-6">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-dark-border flex items-center gap-2">
                <i class="fa-solid fa-user-circle text-teal-400"></i>
                بيانات صاحب الرسالة
            </h3>

            <div class="space-y-4 text-xs">
                <div>
                    <span class="text-gray-400 block mb-1">الاسم الكامل:</span>
                    <span class="font-bold text-white text-sm">{{ $message->name }}</span>
                </div>

                <div>
                    <span class="text-gray-400 block mb-1">البريد الإلكتروني:</span>
                    <a href="mailto:{{ $message->email }}" class="font-bold text-teal-400 hover:underline flex items-center gap-1.5">
                        <i class="fa-regular fa-envelope"></i>
                        <span>{{ $message->email }}</span>
                    </a>
                </div>

                <div>
                    <span class="text-gray-400 block mb-1">رقم الجوال:</span>
                    @if($message->phone)
                        <a href="tel:{{ $message->phone }}" class="font-bold text-white hover:text-teal-400 flex items-center gap-1.5" dir="ltr">
                            <i class="fa-solid fa-phone text-gray-400"></i>
                            <span>{{ $message->phone }}</span>
                        </a>
                    @else
                        <span class="text-gray-500 italic">غير متوفر</span>
                    @endif
                </div>

                <div class="pt-3 border-t border-dark-border">
                    <span class="text-gray-400 block mb-1">حالة الرسالة:</span>
                    @if(!$message->is_read)
                        <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 px-2 py-0.5 rounded text-[10px] font-black inline-flex items-center gap-1">
                            <i class="fa-solid fa-circle text-[6px]"></i> غير مقروءة
                        </span>
                    @else
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded text-[10px] font-black inline-flex items-center gap-1">
                            <i class="fa-solid fa-check text-[8px]"></i> مقروءة
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Technical Metadata --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl p-6">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-dark-border flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-blue-400"></i>
                البيانات التقنية
            </h3>

            <div class="space-y-3 text-xs text-gray-400">
                <div>
                    <span class="block text-gray-500 text-[11px] mb-0.5">عنوان IP:</span>
                    <span class="font-mono text-gray-300 text-xs">{{ $message->ip_address ?: 'غير متوفر' }}</span>
                </div>

                <div>
                    <span class="block text-gray-500 text-[11px] mb-0.5">تاريخ ووقت الإرسال:</span>
                    <span class="text-gray-300">{{ $message->created_at->format('Y-m-d - h:i A') }}</span>
                </div>

                @if($message->user_agent)
                <div>
                    <span class="block text-gray-500 text-[11px] mb-0.5">المتصفح ونظام التشغيل:</span>
                    <p class="text-[11px] text-gray-400 font-mono break-words leading-snug bg-dark-navy/40 p-2 rounded-lg border border-white/5">
                        {{ $message->user_agent }}
                    </p>
                </div>
                @endif
            </div>
        </div>

    </div>

</div>

@endsection
