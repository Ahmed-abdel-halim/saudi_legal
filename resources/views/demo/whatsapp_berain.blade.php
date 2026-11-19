@extends('layouts.app')

@section('title', 'ديمو مياه بيرين التفاعلي | المساعد الذكي "نادية"')

@section('content')

<style>
    /* WhatsApp Simulator Background Pattern */
    .whatsapp-bg {
        background-color: #0b141a;
        background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                          radial-gradient(rgba(18, 140, 126, 0.05) 1px, transparent 1px);
        background-size: 20px 20px;
        background-position: 0 0, 10px 10px;
    }

    /* Custom Scrollbar for Chat */
    .chat-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .chat-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .chat-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 4px;
    }

    /* Typing Dots Animation */
    .typing-dot {
        animation: typingAnimation 1.4s infinite ease-in-out both;
    }
    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typingAnimation {
        0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* Pulse Glow */
    .glow-berain {
        box-shadow: 0 0 25px rgba(14, 165, 233, 0.25);
    }
</style>

<div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Executive Showcase Header --}}
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between mb-8 pb-6 border-b border-slate-800/80 gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-400 border border-sky-500/25 mb-3">
                    <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                    <span>عرض حصري للمديرين والتنفيذيين • Enterprise AI Showcase</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500/20 to-blue-600/20 border border-sky-500/30 flex items-center justify-center text-sky-400 text-2xl shadow-glow flex-shrink-0">
                        💧
                    </span>
                    <span>ديمو مياه بيرين × رديف للذكاء الاصطناعي</span>
                </h1>
                <p class="text-slate-400 text-sm sm:text-base mt-2 max-w-3xl leading-relaxed">
                    مساعد خدمة عملاء حقيقي بشخصية <strong class="text-sky-300 font-bold">"نادية"</strong>، تفهم كافة اللهجات العربية (السعودية، المصرية، الشامية) واللغات العالمية، مدعمة بكتالوج المنتجات والأسعار، سياسات الاسترجاع، خدمة المساجد، وسجل العميل الذهبي <strong class="text-white">محمد عبدالحميد</strong>.
                </p>
            </div>

            {{-- Real WhatsApp Action Button --}}
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <a href="https://wa.me/{{ $cleanBotPhone }}?text={{ urlencode('ديمو مياه بيرين') }}" target="_blank"
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm transition shadow-lg shadow-emerald-600/20">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                    <span>تجربة على واتساب الحقيقي 📱</span>
                </a>
                <button onclick="window.resetChat()"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 font-bold text-sm border border-slate-700/60 transition">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>إعادة الضبط</span>
                </button>
            </div>
        </div>

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Left Column: Executive Customer Intelligence & Quick Scenarios (5 Cols) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Customer Profile Card (محمد عبدالحميد) --}}
                <div class="bg-slate-900/90 border border-slate-800/90 rounded-2xl p-5 shadow-xl relative overflow-hidden backdrop-blur-md">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-sky-500/5 rounded-full blur-xl pointer-events-none"></div>

                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <span class="text-xs font-black text-sky-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-id-card"></i> ملف العميل المتصل (Customer Profile)
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-crown text-[10px]"></i> عميل ذهبي
                        </span>
                    </div>

                    <div class="flex items-center gap-3.5 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-600 to-indigo-600 text-white flex items-center justify-center font-black text-base shadow-md flex-shrink-0">
                            م.ع
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-white text-base truncate">{{ $customer['full_name'] ?? 'محمد عبدالحميد' }}</h4>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">+{{ $customer['mobile_number'] ?? '966540506796' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs font-medium mb-4">
                        <div class="bg-slate-800/60 p-2.5 rounded-xl border border-slate-700/40">
                            <span class="text-slate-400 text-[11px] block">نقاط الولاء (قطاف / مكافأة):</span>
                            <span class="text-white font-mono font-bold text-sm text-sky-300">{{ $points }} نقطة</span>
                        </div>
                        <div class="bg-slate-800/60 p-2.5 rounded-xl border border-slate-700/40">
                            <span class="text-slate-400 text-[11px] block">المنتج المفضل:</span>
                            <span class="text-white font-bold text-[11px] truncate block text-emerald-400">بيرين 600 مل (24 عبوة)</span>
                        </div>
                        <div class="bg-slate-800/60 p-2.5 rounded-xl border border-slate-700/40 col-span-2">
                            <span class="text-slate-400 text-[11px] block">آخر طلب مسجل:</span>
                            <div class="flex items-center justify-between mt-0.5 font-mono">
                                <span class="text-white font-bold">#{{ $lastOrder['order_id'] ?? '695818066' }}</span>
                                <span class="text-amber-400 font-bold">{{ $lastOrder['amount'] ?? '48.77' }} ر.س</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-400 bg-sky-500/5 p-2.5 rounded-xl border border-sky-500/15 flex items-start gap-2">
                        <i class="fa-solid fa-location-dot text-sky-400 mt-0.5"></i>
                        <span><strong>العناوين المسجلة:</strong> حي الورود (الرياض) • جمعية جوار لسقيا المساجد (مكة المكرمة).</span>
                    </div>
                </div>

                {{-- Interactive Quick Scenarios (One-Click Testing) --}}
                <div class="bg-slate-900/90 border border-slate-800/90 rounded-2xl p-5 shadow-xl backdrop-blur-md">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-black text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-flask-vial text-sky-400"></i> اختبار السيناريوهات واللهجات بنقرة واحدة
                        </h3>
                        <span class="text-[10px] text-slate-400">اضغط للإرسال المباشر</span>
                    </div>

                    <div class="space-y-2.5">
                        {{-- Saudi Dialect --}}
                        <button onclick="window.sendScenario('هلا نادية، بالله كرري لي طلبي الأخير على عنواني بالرياض وأبي معه 5 كراتين موية أبو نص')"
                                class="w-full text-right p-3 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-sky-500/50 transition flex items-start gap-3 group">
                            <span class="text-lg flex-shrink-0">🇸🇦</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-sky-300 group-hover:text-sky-200">لهجة سعودية (تكرار الطلب + موية أبو نص)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">"هلا نادية، بالله كرري لي طلبي الأخير على عنواني بالرياض..."</div>
                            </div>
                            <i class="fa-solid fa-paper-plane text-xs text-slate-400 group-hover:text-sky-400 mt-1 transition"></i>
                        </button>

                        {{-- Egyptian Dialect --}}
                        <button onclick="window.sendScenario('إزيك يا نادية، عايز أطلب كرتونتين مية 600 مل وقارورة كبيرة للمبرد، وهل ينفع أدفع بنقاط قطاف؟')"
                                class="w-full text-right p-3 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-sky-500/50 transition flex items-start gap-3 group">
                            <span class="text-lg flex-shrink-0">🇪🇬</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-emerald-300 group-hover:text-emerald-200">لهجة مصرية (مية للمبرد + نقاط قطاف)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">"إزيك يا نادية، عايز أطلب كرتونتين مية 600 مل وقارورة كبيرة..."</div>
                            </div>
                            <i class="fa-solid fa-paper-plane text-xs text-slate-400 group-hover:text-emerald-400 mt-1 transition"></i>
                        </button>

                        {{-- Mosque Charity --}}
                        <button onclick="window.sendScenario('نادية أبي أرسل 10 كراتين موية لمسجد في مكة عن طريق جمعية جوار')"
                                class="w-full text-right p-3 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-sky-500/50 transition flex items-start gap-3 group">
                            <span class="text-lg flex-shrink-0">🕋</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-amber-300 group-hover:text-amber-200">سقيا المساجد بمكة (جمعية جوار)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">"نادية أبي أرسل 10 كراتين موية لمسجد في مكة..."</div>
                            </div>
                            <i class="fa-solid fa-paper-plane text-xs text-slate-400 group-hover:text-amber-400 mt-1 transition"></i>
                        </button>

                        {{-- English Multilingual --}}
                        <button onclick="window.sendScenario('Hi Nadia, how much sodium is in Berain water? And can you repeat my last order?')"
                                class="w-full text-left p-3 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-sky-500/50 transition flex items-start gap-3 group" dir="ltr">
                            <span class="text-lg flex-shrink-0">🇬🇧</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-indigo-300 group-hover:text-indigo-200">English Language (Health & Re-order)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">"Hi Nadia, how much sodium is in Berain water?..."</div>
                            </div>
                            <i class="fa-solid fa-paper-plane text-xs text-slate-400 group-hover:text-indigo-400 mt-1 transition"></i>
                        </button>

                        {{-- Urdu Multilingual --}}
                        <button onclick="window.sendScenario('نادیہ، مجھے اپنے گھر کے لیے پانی کے 5 کارٹن آرڈر کرنے ہیں، قیمت کیا ہے؟')"
                                class="w-full text-right p-3 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-sky-500/50 transition flex items-start gap-3 group" dir="rtl">
                            <span class="text-lg flex-shrink-0">🇵🇰</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-teal-300 group-hover:text-teal-200">Urdu / Hindi (عمالة وشركات)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">"نادیہ، مجھے اپنے گھر کے لیے پانی کے 5 کارٹن آرڈر کرنے ہیں..."</div>
                            </div>
                            <i class="fa-solid fa-paper-plane text-xs text-slate-400 group-hover:text-teal-400 mt-1 transition"></i>
                        </button>
                    </div>
                </div>

                {{-- Enterprise Key Capabilities --}}
                <div class="bg-gradient-to-br from-slate-900 to-slate-900/60 border border-slate-800 rounded-2xl p-4 text-xs text-slate-400 space-y-2">
                    <div class="font-bold text-white text-xs mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-sparkles text-amber-400"></i> مميزات النموذج المخصص لمياه بيرين:
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-emerald-400 text-[10px]"></i>
                        <span>تقمص بشري 100% وإلغاء الردود الروبوتية الجافة.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-emerald-400 text-[10px]"></i>
                        <span>تكامل مباشر مع قاعدة بيانات المنتجات، نقاط قطاف ومكافأة الراجحي.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-emerald-400 text-[10px]"></i>
                        <span>دعم كامل للرسائل الصوتية وتفريغها وسماع الرد بصوت نادية.</span>
                    </div>
                </div>

            </div>

            {{-- Right Column: WhatsApp Interactive Smartphone Simulator (7 Cols) --}}
            <div class="lg:col-span-7 flex justify-center">

                {{-- Smartphone Mockup Frame --}}
                <div class="w-full max-w-[430px] rounded-[42px] p-3.5 bg-slate-900 border-[3px] border-slate-700/80 shadow-2xl glow-berain relative">

                    {{-- Top Speaker Notch / Dynamic Island --}}
                    <div class="w-28 h-4 bg-slate-950 rounded-full mx-auto mb-2 flex items-center justify-center">
                        <div class="w-3 h-3 rounded-full bg-slate-900 border border-slate-800"></div>
                    </div>

                    {{-- Inner Screen --}}
                    <div class="rounded-[32px] overflow-hidden bg-[#0b141a] border border-slate-800 flex flex-col h-[670px] relative">

                        {{-- WhatsApp Header --}}
                        <div class="bg-[#202c33] text-white px-3.5 py-3 flex items-center justify-between border-b border-slate-800 z-10">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <a href="javascript:void(0)" class="text-slate-400 hover:text-white transition text-sm">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                {{-- Nadia Avatar --}}
                                <div class="relative flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                        💧
                                    </div>
                                    <span class="absolute bottom-0 left-0 w-3 h-3 bg-emerald-500 border-2 border-[#202c33] rounded-full"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-xs sm:text-sm text-slate-100 truncate flex items-center gap-1.5">
                                        <span>نادية</span>
                                        <span class="text-[10px] text-sky-400 font-normal">| بيرين 💧</span>
                                    </div>
                                    <div class="text-[10px] text-emerald-400 truncate">
                                        متصل الآن • تدعم كافة اللهجات 🌍
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-slate-400 text-sm">
                                <button onclick="window.speakLastReply()" title="سماع رد نادية الصوتي" class="hover:text-sky-400 transition">
                                    <i class="fa-solid fa-volume-high"></i>
                                </button>
                                <a href="https://wa.me/{{ $cleanBotPhone }}?text={{ urlencode('ديمو مياه بيرين') }}" target="_blank" title="محادثة واتساب الحقيقية" class="hover:text-emerald-400 transition">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                                <button onclick="window.resetChat()" title="إعادة بدء المحادثة" class="hover:text-white transition">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                            </div>
                        </div>

                        {{-- WhatsApp Chat Area --}}
                        <div id="chatMessages" class="flex-1 whatsapp-bg overflow-y-auto p-3.5 space-y-3.5 chat-scroll text-xs">
                            
                            {{-- Security Notice --}}
                            <div class="text-center my-1">
                                <span class="inline-block bg-[#182229] text-[#ffd279] text-[10px] px-3 py-1 rounded-lg border border-[#ffd279]/15 shadow-sm max-w-[90%]">
                                    🔒 الرسائل مشفرة تماماً. أنت تتحدث الآن مع "نادية" ممثلة مياه بيرين.
                                </span>
                            </div>

                            {{-- Date Bubble --}}
                            <div class="text-center my-1">
                                <span class="inline-block bg-[#182229] text-slate-400 text-[10px] px-2.5 py-0.5 rounded-md font-mono">
                                    اليوم
                                </span>
                            </div>

                            {{-- Initial Nadia Welcome Message --}}
                            <div class="flex flex-col items-start message-bubble">
                                <div class="bg-[#202c33] text-slate-100 rounded-2xl rounded-tr-none px-3.5 py-2.5 max-w-[88%] shadow-md border border-slate-700/30 relative">
                                    <div class="font-bold text-[11px] text-sky-400 mb-1 flex items-center justify-between gap-2">
                                        <span>نادية (عناية العملاء)</span>
                                        <button onclick="window.speakText(this.closest('.message-bubble').querySelector('.bubble-text').innerText)" class="text-slate-400 hover:text-sky-300" title="استماع">
                                            <i class="fa-solid fa-volume-low text-[10px]"></i>
                                        </button>
                                    </div>
                                    <div class="bubble-text leading-relaxed whitespace-pre-line text-[12px]">
يا هلا والله أستاذ محمد! 👋💧
معك <strong class="text-sky-300">نادية</strong> من عناية العملاء في مياه بيرين.. نورتنا والله!

بشرني، حاب نكرر طلبك الأخير زي العادة ولا ودك نطلب شي جديد اليوم للبيت أو للمسجد؟ 🤍

_(خذ راحتك كلمني بأي لهجة أو لغة تحبها، كتابة أو فويس 🎙️)_
                                    </div>
                                    <div class="text-[9px] text-slate-400 text-left mt-1 font-mono">
                                        {{ now()->format('h:i A') }}
                                    </div>
                                </div>

                                {{-- Quick Reply Chips inside WhatsApp --}}
                                <div class="flex flex-wrap gap-1.5 mt-2 max-w-[90%]">
                                    <button onclick="window.sendScenario('كرري لي طلبي الأخير على حي الورود')"
                                            class="bg-[#202c33]/90 hover:bg-sky-950 text-sky-300 border border-sky-500/30 rounded-xl px-2.5 py-1 text-[11px] font-bold transition flex items-center gap-1 shadow-sm">
                                        <span>🔄 تكرار طلبي الأخير</span>
                                    </button>
                                    <button onclick="window.sendScenario('كم سعر كرتون مياه 600 مل؟')"
                                            class="bg-[#202c33]/90 hover:bg-sky-950 text-slate-300 border border-slate-700/60 rounded-xl px-2.5 py-1 text-[11px] font-medium transition shadow-sm">
                                        <span>💧 سعر كرتون 600 مل</span>
                                    </button>
                                    <button onclick="window.sendScenario('كم رصيدي من النقاط؟')"
                                            class="bg-[#202c33]/90 hover:bg-sky-950 text-slate-300 border border-slate-700/60 rounded-xl px-2.5 py-1 text-[11px] font-medium transition shadow-sm">
                                        <span>🎁 رصيد نقاطي</span>
                                    </button>
                                    <button onclick="window.sendScenario('أبي أتبرع لمسجد بمكة بموية بيرين')"
                                            class="bg-[#202c33]/90 hover:bg-sky-950 text-slate-300 border border-slate-700/60 rounded-xl px-2.5 py-1 text-[11px] font-medium transition shadow-sm">
                                        <span>🕋 سقيا المساجد</span>
                                    </button>
                                </div>
                            </div>

                        </div>

                        {{-- Typing Indicator Bubble (Hidden by default) --}}
                        <div id="typingIndicator" class="hidden px-3.5 py-2 bg-[#0b141a] z-10">
                            <div class="bg-[#202c33] text-slate-400 rounded-2xl rounded-tr-none px-3.5 py-2 inline-flex items-center gap-2 shadow-sm border border-slate-700/40">
                                <span class="text-[11px] text-sky-400 font-bold" id="typingText">نادية تكتب الآن</span>
                                <div class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Voice Recording Bar (Hidden by default) --}}
                        <div id="recordingBar" class="hidden bg-[#202c33] p-2.5 border-t border-slate-800 flex items-center justify-between text-xs text-rose-400 z-10">
                            <div class="flex items-center gap-2 font-bold animate-pulse">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span>جاري تسجيل صوتك... تحدث بأي لهجة أو لغة 🎙️</span>
                            </div>
                            <button onclick="window.stopRecording()" class="bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 px-3 py-1 rounded-lg text-xs font-bold transition">
                                إرسال الصوت
                            </button>
                        </div>

                        {{-- WhatsApp Bottom Input Bar --}}
                        <div class="bg-[#202c33] p-2.5 border-t border-slate-800/80 flex items-center gap-2 z-10">
                            
                            {{-- Microphone Button --}}
                            <button id="micBtn" onclick="window.toggleRecording()"
                                    class="w-9 h-9 rounded-full bg-[#182229] hover:bg-sky-600 text-slate-300 hover:text-white flex items-center justify-center transition flex-shrink-0"
                                    title="تحدث بالمايكروفون (Voice Note)">
                                <i class="fa-solid fa-microphone text-xs"></i>
                            </button>

                            {{-- Text Input --}}
                            <div class="flex-1 relative">
                                <input type="text" id="userInput"
                                       placeholder="اكتب لنادية بأي لهجة أو لغة..."
                                       class="w-full bg-[#2a3942] text-white text-xs rounded-2xl px-3.5 py-2.5 outline-none placeholder-slate-400 border border-transparent focus:border-sky-500/50 transition">
                            </div>

                            {{-- Send Button --}}
                            <button id="sendBtn" onclick="window.sendMessage()"
                                    class="w-9 h-9 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center transition shadow-md flex-shrink-0"
                                    title="إرسال">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
    // Global Conversation History
    window.chatHistory = [];
    window.lastReplyText = '';
    window.isRecording = false;
    window.mediaRecorder = null;
    window.audioChunks = [];

    // Scroll chat to bottom
    function scrollToBottom() {
        const container = document.getElementById('chatMessages');
        container.scrollTop = container.scrollHeight;
    }

    // Format current time
    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    // Send predefined scenario
    window.sendScenario = function(text) {
        document.getElementById('userInput').value = text;
        window.sendMessage();
    };

    // Reset Chat
    window.resetChat = function() {
        window.chatHistory = [];
        window.location.reload();
    };

    // Text to Speech playback for Nadia
    window.speakText = function(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();

        // Remove emojis and asterisks from speech
        const clean = text.replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F1E0}-\u{1F1FF}\*\_]/gu, '');

        const utterance = new SpeechSynthesisUtterance(clean);
        // Detect language
        const isEnglish = /[a-zA-Z]{5,}/.test(clean);
        utterance.lang = isEnglish ? 'en-US' : 'ar-SA';
        utterance.rate = 1.0;
        utterance.pitch = 1.1; // Gentle feminine pitch

        window.speechSynthesis.speak(utterance);
    };

    window.speakLastReply = function() {
        if (window.lastReplyText) {
            window.speakText(window.lastReplyText);
        }
    };

    // Send Message Action
    window.sendMessage = async function() {
        const input = document.getElementById('userInput');
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        // 1. Append User Message to UI
        const chatContainer = document.getElementById('chatMessages');
        const time = getCurrentTime();

        const userHtml = `
            <div class="flex flex-col items-end message-bubble">
                <div class="bg-[#005c4b] text-white rounded-2xl rounded-tl-none px-3.5 py-2.5 max-w-[85%] shadow-md border border-emerald-600/30">
                    <div class="bubble-text leading-relaxed whitespace-pre-line text-[12px]">${escapeHtml(text)}</div>
                    <div class="text-[9px] text-emerald-200 text-left mt-1 font-mono flex items-center justify-end gap-1">
                        <span>${time}</span>
                        <i class="fa-solid fa-check-double text-[8px] text-sky-300"></i>
                    </div>
                </div>
            </div>
        `;
        chatContainer.insertAdjacentHTML('beforeend', userHtml);
        scrollToBottom();

        // 2. Show Typing Indicator
        const typingIndicator = document.getElementById('typingIndicator');
        const typingText = document.getElementById('typingText');
        const isEn = /[a-zA-Z]{5,}/.test(text);
        typingText.innerText = isEn ? 'Nadia is typing' : 'نادية تكتب الآن';
        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        // 3. Send AJAX Request to backend
        try {
            const response = await fetch("{{ route('demo.berain.chat') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: text,
                    history: window.chatHistory
                })
            });

            const data = await response.json();
            typingIndicator.classList.add('hidden');

            if (data.success && data.reply) {
                const replyText = data.reply;
                window.lastReplyText = replyText;

                // Update chat history
                window.chatHistory.push({ role: 'user', content: text });
                window.chatHistory.push({ role: 'assistant', content: replyText });

                // Render Nadia's Reply
                const replyHtml = `
                    <div class="flex flex-col items-start message-bubble">
                        <div class="bg-[#202c33] text-slate-100 rounded-2xl rounded-tr-none px-3.5 py-2.5 max-w-[88%] shadow-md border border-slate-700/30">
                            <div class="font-bold text-[11px] text-sky-400 mb-1 flex items-center justify-between gap-2">
                                <span>نادية (عناية العملاء)</span>
                                <button onclick="window.speakText(this.closest('.message-bubble').querySelector('.bubble-text').innerText)" class="text-slate-400 hover:text-sky-300" title="استماع">
                                    <i class="fa-solid fa-volume-low text-[10px]"></i>
                                </button>
                            </div>
                            <div class="bubble-text leading-relaxed whitespace-pre-line text-[12px]">${formatMarkdown(replyText)}</div>
                            <div class="text-[9px] text-slate-400 text-left mt-1 font-mono">
                                ${data.timestamp || getCurrentTime()}
                            </div>
                        </div>
                    </div>
                `;
                chatContainer.insertAdjacentHTML('beforeend', replyHtml);
                scrollToBottom();

                // Auto speak in demo mode if enabled
                // window.speakText(replyText);
            } else {
                throw new Error(data.reply || 'حدث خطأ غير متوقع');
            }

        } catch (err) {
            typingIndicator.classList.add('hidden');
            const errHtml = `
                <div class="flex flex-col items-start">
                    <div class="bg-[#202c33] text-rose-300 rounded-2xl px-3.5 py-2.5 max-w-[88%] shadow-md border border-rose-500/30 text-[11px]">
                        يا هلا والله أستاذ محمد! حدث ضغط مؤقت على الخادم، أعد إرسال سؤالك وأنا بالخدمة.
                    </div>
                </div>
            `;
            chatContainer.insertAdjacentHTML('beforeend', errHtml);
            scrollToBottom();
        }
    };

    // Enter Key to Send
    document.getElementById('userInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.sendMessage();
        }
    });

    // Audio Voice Note Recording Support
    window.toggleRecording = async function() {
        if (window.isRecording) {
            window.stopRecording();
            return;
        }

        // Web Speech Recognition Fallback for instant client-side speech
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
            const rec = new SpeechRec();
            rec.lang = 'ar-SA';
            rec.continuous = false;
            rec.interimResults = false;

            const recBar = document.getElementById('recordingBar');
            recBar.classList.remove('hidden');
            window.isRecording = true;

            rec.onresult = function(event) {
                const spoken = event.results[0][0].transcript;
                recBar.classList.add('hidden');
                window.isRecording = false;
                if (spoken) {
                    document.getElementById('userInput').value = spoken;
                    window.sendMessage();
                }
            };

            rec.onerror = function(event) {
                recBar.classList.add('hidden');
                window.isRecording = false;
                startMediaRecorder(); // Fallback to raw audio upload
            };

            rec.onend = function() {
                recBar.classList.add('hidden');
                window.isRecording = false;
            };

            rec.start();
            return;
        }

        startMediaRecorder();
    };

    async function startMediaRecorder() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            window.audioChunks = [];
            window.mediaRecorder = new MediaRecorder(stream);

            window.mediaRecorder.ondataavailable = e => window.audioChunks.push(e.data);
            window.mediaRecorder.onstop = uploadRecordedAudio;

            window.mediaRecorder.start();
            window.isRecording = true;
            document.getElementById('recordingBar').classList.remove('hidden');
        } catch (err) {
            alert('يرجى السماح بالوصول إلى المايكروفون لتجربة الصوت.');
        }
    }

    window.stopRecording = function() {
        if (window.mediaRecorder && window.isRecording) {
            window.mediaRecorder.stop();
            window.isRecording = false;
            document.getElementById('recordingBar').classList.add('hidden');
        }
    };

    async function uploadRecordedAudio() {
        const audioBlob = new Blob(window.audioChunks, { type: 'audio/webm' });
        const formData = new FormData();
        formData.append('audio', audioBlob, 'voice_note.webm');

        const typingIndicator = document.getElementById('typingIndicator');
        const typingText = document.getElementById('typingText');
        typingText.innerText = 'جاري تفريغ الصوت...';
        typingIndicator.classList.remove('hidden');

        try {
            const res = await fetch("{{ route('demo.berain.transcribe') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            const data = await res.json();
            typingIndicator.classList.add('hidden');

            if (data.success && data.text) {
                document.getElementById('userInput').value = data.text;
                window.sendMessage();
            } else {
                alert('تعذر تفريغ الصوت، جرب تسجيل صوت أوضح.');
            }
        } catch (e) {
            typingIndicator.classList.add('hidden');
        }
    }

    // Helper: Escape HTML
    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // Helper: Basic Markdown formatting (**bold**, _italic_)
    function formatMarkdown(text) {
        let t = escapeHtml(text);
        t = t.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-sky-200">$1</strong>');
        t = t.replace(/\*(.*?)\*/g, '<strong class="font-bold text-sky-200">$1</strong>');
        t = t.replace(/\_(.*?)\_/g, '<em class="italic text-slate-300">$1</em>');
        return t;
    }
</script>
@endpush

@endsection
