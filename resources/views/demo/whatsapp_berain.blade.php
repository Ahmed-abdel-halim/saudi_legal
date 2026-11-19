@extends('layouts.app')

@section('title', 'ديمو مياه بيرين × رديف للذكاء الاصطناعي | المساعد الذكي "نادية"')

@section('content')

<style>
    /* Luxury Dark Background */
    .berain-bg-ambient {
        background-color: #060b13;
        background-image: 
            radial-gradient(at 10% 10%, rgba(14, 165, 233, 0.12) 0px, transparent 40%),
            radial-gradient(at 90% 10%, rgba(16, 185, 129, 0.10) 0px, transparent 40%),
            radial-gradient(at 50% 90%, rgba(30, 58, 138, 0.15) 0px, transparent 50%);
    }

    /* WhatsApp Doodle Pattern */
    .whatsapp-doodle-bg {
        background-color: #0b141a;
        background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                          radial-gradient(rgba(14, 165, 233, 0.05) 1px, transparent 1px);
        background-size: 22px 22px;
        background-position: 0 0, 11px 11px;
    }

    /* Titanium Phone Mockup */
    .phone-titanium {
        box-shadow: 
            0 0 0 3px #334155,
            0 0 0 5px #1e293b,
            0 25px 50px -12px rgba(0, 0, 0, 0.75),
            0 0 30px rgba(14, 165, 233, 0.2);
    }

    /* Chat Scrollbar */
    .chat-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .chat-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .chat-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 999px;
    }
    .chat-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(14, 165, 233, 0.5);
    }

    /* Typing Dots Animation */
    .typing-dot {
        animation: typingAnimation 1.4s infinite ease-in-out both;
    }
    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typingAnimation {
        0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
        40% { transform: scale(1); opacity: 1; }
    }
</style>

<div class="min-h-screen berain-bg-ambient text-slate-100 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Clean & Prestigious Executive Header --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 via-blue-600 to-teal-500 flex items-center justify-center text-white text-2xl shadow-lg shadow-sky-500/20 flex-shrink-0">
                    💧
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-sky-400 bg-sky-500/10 px-2.5 py-0.5 rounded-full border border-sky-500/20">
                            Enterprise AI Demo
                        </span>
                        <span class="text-xs text-slate-400 font-mono">• مياه بيرين × رديف</span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-black text-white mt-0.5">
                        المساعد الذكي لخدمة العملاء <span class="text-sky-300">"نادية"</span>
                    </h1>
                </div>
            </div>

            {{-- CTA Actions --}}
            <div class="flex items-center gap-2.5 w-full md:w-auto justify-end">
                <a href="https://wa.me/{{ $cleanBotPhone }}?text={{ urlencode('ديمو مياه بيرين') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs transition shadow-md shadow-emerald-600/20 group">
                    <i class="fa-brands fa-whatsapp text-base group-hover:scale-110 transition-transform"></i>
                    <span>فتح على واتساب الحقيقي 📱</span>
                </a>

                <button onclick="window.resetChat()"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 hover:text-white font-bold text-xs border border-slate-700/80 transition"
                   title="إعادة بدء المحادثة">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>إعادة ضبط</span>
                </button>
            </div>
        </div>

        {{-- Main Two-Column Harmonious Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Column 1: Smartphone Simulator (5 Cols on desktop - centered frame) --}}
            <div class="lg:col-span-5 flex justify-center">

                {{-- Phone Outer Bezel --}}
                <div class="w-full max-w-[390px] rounded-[46px] p-3 bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 phone-titanium relative">
                    
                    {{-- Volume & Power Buttons Simulation --}}
                    <div class="absolute -left-1 top-24 w-1 h-10 bg-slate-700 rounded-l-md"></div>
                    <div class="absolute -left-1 top-38 w-1 h-10 bg-slate-700 rounded-l-md"></div>
                    <div class="absolute -right-1 top-32 w-1 h-14 bg-slate-700 rounded-r-md"></div>

                    {{-- Dynamic Island Top Notch --}}
                    <div class="w-28 h-3.5 bg-black rounded-full mx-auto mb-2 flex items-center justify-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-slate-900 border border-slate-800"></div>
                        <div class="w-2 h-2 rounded-full bg-[#0a1218] border border-slate-800"></div>
                    </div>

                    {{-- WhatsApp Screen Container --}}
                    <div class="rounded-[34px] overflow-hidden bg-[#0b141a] border border-slate-800/80 flex flex-col h-[640px] shadow-inner relative">

                        {{-- WhatsApp Header --}}
                        <div class="bg-[#202c33] text-white px-3.5 py-2.5 flex items-center justify-between border-b border-slate-800 z-10 shadow-md">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <a href="javascript:void(0)" class="text-slate-400 hover:text-white transition text-xs">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                {{-- Nadia Avatar --}}
                                <div class="relative flex-shrink-0">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-sky-400 via-sky-600 to-blue-700 flex items-center justify-center text-white font-black text-sm shadow-md ring-2 ring-sky-400/30">
                                        💧
                                    </div>
                                    <span class="absolute bottom-0 left-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-[#202c33] rounded-full"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-100 truncate flex items-center gap-1">
                                        <span>نادية</span>
                                        <span class="text-[9px] text-sky-400 font-semibold bg-sky-500/10 px-1 py-0.2 rounded border border-sky-500/20">بيرين 💧</span>
                                    </div>
                                    <div class="text-[10px] text-emerald-400 truncate flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span>متصل الآن • تفهم كل اللهجات 🌍</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Header Icons --}}
                            <div class="flex items-center gap-2 text-slate-400 text-xs">
                                <button onclick="window.speakLastReply()" title="استماع لرد نادية الصوتي" class="w-7 h-7 rounded-full hover:bg-white/5 hover:text-sky-400 flex items-center justify-center transition">
                                    <i class="fa-solid fa-volume-high"></i>
                                </button>
                                <a href="https://wa.me/{{ $cleanBotPhone }}?text={{ urlencode('ديمو مياه بيرين') }}" target="_blank" title="محادثة واتساب الحقيقية" class="w-7 h-7 rounded-full hover:bg-white/5 hover:text-emerald-400 flex items-center justify-center transition">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                                <button onclick="window.resetChat()" title="إعادة تشغيل المحادثة" class="w-7 h-7 rounded-full hover:bg-white/5 hover:text-white flex items-center justify-center transition">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>

                        {{-- WhatsApp Chat Area --}}
                        <div id="chatMessages" class="flex-1 whatsapp-doodle-bg overflow-y-auto p-3.5 space-y-3 chat-scroll text-xs">
                            
                            {{-- Security Encryption Banner --}}
                            <div class="text-center my-0.5">
                                <span class="inline-block bg-[#182229]/95 text-[#ffd279] text-[9px] px-3 py-1 rounded-lg border border-[#ffd279]/20 shadow-sm max-w-[94%] leading-relaxed">
                                    🔒 الرسائل مشفرة تماماً بين الطرفين. أنت تتحدث الآن مع "نادية" عناية العملاء.
                                </span>
                            </div>

                            {{-- Initial Welcoming Message --}}
                            <div class="flex flex-col items-start message-bubble">
                                <div class="bg-[#202c33] text-slate-100 rounded-2xl rounded-tr-none p-3 max-w-[90%] shadow-md border border-slate-700/40 relative">
                                    <div class="font-bold text-[10px] text-sky-400 mb-1 flex items-center justify-between gap-2 border-b border-white/5 pb-1">
                                        <span class="flex items-center gap-1">
                                            <span>نادية</span>
                                            <span class="text-slate-400 font-normal text-[9px]">(عناية عملاء مياه بيرين)</span>
                                        </span>
                                        <button onclick="window.speakText(this.closest('.message-bubble').querySelector('.bubble-text').innerText)" class="text-slate-400 hover:text-sky-300 transition" title="استماع">
                                            <i class="fa-solid fa-volume-high text-[10px]"></i>
                                        </button>
                                    </div>
                                    <div class="bubble-text leading-relaxed whitespace-pre-line text-xs text-slate-100">
يا هلا والله أستاذ محمد! 👋💧
معك <strong class="text-sky-300 font-bold">نادية</strong> من عناية العملاء في مياه بيرين.. نورتنا والله!

بشرني، حاب نكرر طلبك الأخير زي العادة ولا ودك نطلب شي جديد اليوم للبيت أو للمسجد؟ 🤍

_(خذ راحتك كلمني بأي لهجة أو لغة تحبها، كتابة أو فويس 🎙️)_
                                    </div>
                                    <div class="text-[9px] text-slate-400 text-left mt-1 font-mono">
                                        {{ now()->format('h:i A') }}
                                    </div>
                                </div>

                                {{-- Quick Reply Chips inside Phone --}}
                                <div class="flex flex-wrap gap-1 mt-2 max-w-[92%]">
                                    <button onclick="window.sendScenario('كرري لي طلبي الأخير على حي الورود')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-sky-300 border border-sky-500/30 rounded-xl px-2.5 py-1 text-[11px] font-bold transition flex items-center gap-1 shadow-sm">
                                        <span>🔄 تكرار طلبي الأخير</span>
                                    </button>
                                    <button onclick="window.sendScenario('كم سعر كرتون مياه 600 مل؟')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-slate-200 border border-slate-700/60 rounded-xl px-2.5 py-1 text-[11px] font-medium transition shadow-sm">
                                        <span>💧 سعر كرتون 600 مل</span>
                                    </button>
                                    <button onclick="window.sendScenario('كم رصيدي من النقاط؟')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-slate-200 border border-slate-700/60 rounded-xl px-2.5 py-1 text-[11px] font-medium transition shadow-sm">
                                        <span>🎁 رصيد نقاطي</span>
                                    </button>
                                    <button onclick="window.sendScenario('أبي أتبرع لمسجد بمكة بموية بيرين')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-slate-200 border border-slate-700/60 rounded-xl px-2.5 py-1 text-[11px] font-medium transition shadow-sm">
                                        <span>🕋 سقيا المساجد</span>
                                    </button>
                                </div>
                            </div>

                        </div>

                        {{-- Typing Indicator Bubble --}}
                        <div id="typingIndicator" class="hidden px-3.5 py-1.5 bg-[#0b141a] z-10">
                            <div class="bg-[#202c33] text-slate-300 rounded-2xl rounded-tr-none px-3.5 py-2 inline-flex items-center gap-2 shadow-md border border-slate-700/50">
                                <span class="text-[11px] text-sky-400 font-bold" id="typingText">نادية تكتب الآن</span>
                                <div class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Voice Recording Banner --}}
                        <div id="recordingBar" class="hidden bg-[#202c33] px-3 py-2.5 border-t border-slate-800 flex items-center justify-between text-xs text-rose-400 z-10">
                            <div class="flex items-center gap-2 font-bold animate-pulse text-xs">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span>جاري تسجيل صوتك... 🎙️</span>
                            </div>
                            <button onclick="window.stopRecording()" class="bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 px-2.5 py-0.5 rounded-lg text-xs font-bold transition">
                                إرسال
                            </button>
                        </div>

                        {{-- WhatsApp Input Bar --}}
                        <div class="bg-[#202c33] p-2.5 border-t border-slate-800/80 flex items-center gap-2 z-10">
                            {{-- Mic Button --}}
                            <button id="micBtn" onclick="window.toggleRecording()"
                                    class="w-9 h-9 rounded-full bg-[#182229] hover:bg-sky-600 text-slate-300 hover:text-white flex items-center justify-center transition flex-shrink-0"
                                    title="تحدث بالمايكروفون (Voice Note)">
                                <i class="fa-solid fa-microphone text-xs"></i>
                            </button>

                            {{-- Input Field --}}
                            <div class="flex-1 relative">
                                <input type="text" id="userInput"
                                       placeholder="اكتب لنادية بأي لهجة أو لغة..."
                                       class="w-full bg-[#2a3942] text-white text-xs rounded-2xl px-3.5 py-2 outline-none placeholder-slate-400 border border-transparent focus:border-sky-500/50 transition">
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

            {{-- Column 2: Harmonious Executive Control Center (7 Cols on desktop) --}}
            <div class="lg:col-span-7 space-y-4">

                {{-- VIP Customer Card (Sleek Banking/Membership Style) --}}
                <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-amber-500/30 rounded-3xl p-5 shadow-xl relative overflow-hidden backdrop-blur-xl">
                    <div class="absolute -top-10 -right-10 w-36 h-36 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    {{-- Card Header --}}
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="text-amber-400 text-base">👑</span>
                            <span class="text-xs font-black text-white tracking-wider">بطاقة العميل المتصل (Live Customer CRM)</span>
                        </div>
                        <span class="text-xs font-bold text-amber-300 bg-amber-500/15 px-2.5 py-0.5 rounded-full border border-amber-500/30 font-mono">
                            VIP GOLD
                        </span>
                    </div>

                    {{-- Customer Identity & 3 Key Stats in One Neat Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                        {{-- Name & Contact (5 cols) --}}
                        <div class="sm:col-span-5 flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-600 text-slate-950 font-black text-sm flex items-center justify-center shadow-md flex-shrink-0">
                                م.ع
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-white text-sm truncate">{{ $customer['full_name'] ?? 'محمد عبدالحميد' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">+{{ $customer['mobile_number'] ?? '966540506796' }}</div>
                            </div>
                        </div>

                        {{-- 3 Clean Stat Badges (7 cols) --}}
                        <div class="sm:col-span-7 grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="bg-slate-800/70 p-2 rounded-xl border border-slate-700/60">
                                <span class="text-[10px] text-slate-400 block">نقاط الولاء</span>
                                <span class="text-sky-300 font-black font-mono text-sm">{{ $points }}</span>
                            </div>
                            <div class="bg-slate-800/70 p-2 rounded-xl border border-slate-700/60">
                                <span class="text-[10px] text-slate-400 block">آخر طلب</span>
                                <span class="text-amber-400 font-black font-mono text-sm">{{ $lastOrder['amount'] ?? '48.77' }} <span class="text-[9px] font-normal font-sans">ر.س</span></span>
                            </div>
                            <div class="bg-slate-800/70 p-2 rounded-xl border border-slate-700/60">
                                <span class="text-[10px] text-slate-400 block">المنتج المفضل</span>
                                <span class="text-emerald-400 font-bold text-[11px] truncate block mt-0.5">بيرين 600 مل</span>
                            </div>
                        </div>
                    </div>

                    {{-- Saved Address Pill --}}
                    <div class="mt-3 pt-2.5 border-t border-white/5 flex items-center justify-between text-[11px] text-slate-300">
                        <div class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-location-dot text-sky-400 text-xs"></i>
                            <span>حي الورود (الرياض) • سقيا المساجد بمكة (جمعية جوار)</span>
                        </div>
                        <span class="text-slate-400 text-[10px] font-mono flex-shrink-0">رقم الطلب #{{ $lastOrder['order_id'] ?? '695818066' }}</span>
                    </div>
                </div>

                {{-- Interactive Scenario Bank (Organized & Harmonious 2-Column Grid) --}}
                <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-5 shadow-xl backdrop-blur-xl space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="text-sky-400 text-sm">⚡</span>
                            <h3 class="text-xs font-black text-white uppercase tracking-wider">بنك السيناريوهات الحية (اضغط للتجربة الفورية)</h3>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">نقرة واحدة تُرسل للشات</span>
                    </div>

                    {{-- 6 Balanced Scenario Cards (2x3 Grid) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        
                        {{-- Scenario 1: Saudi Dialect --}}
                        <div onclick="window.sendScenario('هلا نادية، بالله كرري لي طلبي الأخير على عنواني بالرياض وأبي معه 5 كراتين موية أبو نص')"
                             class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-sky-500/50 rounded-2xl p-3 transition cursor-pointer group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-sky-300 flex items-center gap-1.5">
                                        <span>🇸🇦</span>
                                        <span>سعودي: تكرار الطلب + موية أبو نص</span>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-sky-500/20 text-sky-300 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center text-[10px] transition">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                    "هلا نادية، بالله كرري لي طلبي الأخير على عنواني بالرياض وأبي معه 5 كراتين موية أبو نص"
                                </p>
                            </div>
                        </div>

                        {{-- Scenario 2: Egyptian Dialect --}}
                        <div onclick="window.sendScenario('إزيك يا نادية، عايز أطلب كرتونتين مية 600 مل وقارورة كبيرة للمبرد، وهل ينفع أدفع بنقاط قطاف؟')"
                             class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-emerald-500/50 rounded-2xl p-3 transition cursor-pointer group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-emerald-300 flex items-center gap-1.5">
                                        <span>🇪🇬</span>
                                        <span>مصري: مية للمبرد + نقاط قطاف</span>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 group-hover:bg-emerald-500 group-hover:text-white flex items-center justify-center text-[10px] transition">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                    "إزيك يا نادية، عايز أطلب كرتونتين مية 600 مل وقارورة كبيرة للمبرد، وهل ينفع أدفع بنقاط قطاف؟"
                                </p>
                            </div>
                        </div>

                        {{-- Scenario 3: Mosque Charity --}}
                        <div onclick="window.sendScenario('نادية أبي أرسل 10 كراتين موية لمسجد في مكة عن طريق جمعية جوار')"
                             class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-amber-500/50 rounded-2xl p-3 transition cursor-pointer group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-amber-300 flex items-center gap-1.5">
                                        <span>🕋</span>
                                        <span>سقيا المساجد: مكة (جمعية جوار)</span>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-300 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center text-[10px] transition">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                    "نادية أبي أرسل 10 كراتين موية لمسجد في مكة عن طريق جمعية جوار"
                                </p>
                            </div>
                        </div>

                        {{-- Scenario 4: English Language --}}
                        <div onclick="window.sendScenario('Hi Nadia, how much sodium is in Berain water? And can you repeat my last order?')"
                             class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-indigo-500/50 rounded-2xl p-3 transition cursor-pointer group flex flex-col justify-between" dir="ltr">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-indigo-300 flex items-center gap-1.5">
                                        <span>🇬🇧</span>
                                        <span>English: Sodium & Re-order</span>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-indigo-500/20 text-indigo-300 group-hover:bg-indigo-500 group-hover:text-white flex items-center justify-center text-[10px] transition">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                    "Hi Nadia, how much sodium is in Berain water? And can you repeat my last order?"
                                </p>
                            </div>
                        </div>

                        {{-- Scenario 5: Catalog & Pricing --}}
                        <div onclick="window.sendScenario('كم سعر كرتون بيرين 600 مل وبيرين 200 مل، وإيش الأحجام المتوفرة؟')"
                             class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-teal-500/50 rounded-2xl p-3 transition cursor-pointer group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-teal-300 flex items-center gap-1.5">
                                        <span>📦</span>
                                        <span>الأسعار والأحجام: كتالوج بيرين</span>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-teal-500/20 text-teal-300 group-hover:bg-teal-500 group-hover:text-white flex items-center justify-center text-[10px] transition">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                    "كم سعر كرتون بيرين 600 مل وبيرين 200 مل، وإيش الأحجام المتوفرة؟"
                                </p>
                            </div>
                        </div>

                        {{-- Scenario 6: Quality SLA & Complaints --}}
                        <div onclick="window.sendScenario('نادية، وصلني كرتون فيه طعم غريب وتاريخ إنتاجه جديد، ايش الإجراء؟')"
                             class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-rose-500/50 rounded-2xl p-3 transition cursor-pointer group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-rose-300 flex items-center gap-1.5">
                                        <span>🧪</span>
                                        <span>استفسار جودة: SLA والتعويض الفوري</span>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-300 group-hover:bg-rose-500 group-hover:text-white flex items-center justify-center text-[10px] transition">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                    "نادية، وصلني كرتون فيه طعم غريب وتاريخ إنتاجه جديد، ايش الإجراء؟"
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Key Capabilities Banner (Bottom Pill Bar) --}}
                <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-400"></i>
                        <span>استجابة فورية <strong>&lt; 1 ثانية</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-microphone text-emerald-400"></i>
                        <span>دعم الفويس نوت <strong>(OpenAI Whisper + Gemini)</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-sky-400"></i>
                        <span>دقة الحقائق <strong>100%</strong></span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
    window.chatHistory = [];
    window.lastReplyText = '';
    window.isRecording = false;
    window.mediaRecorder = null;
    window.audioChunks = [];

    function scrollToBottom() {
        const container = document.getElementById('chatMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    window.sendScenario = function(text) {
        const input = document.getElementById('userInput');
        input.value = text;
        window.sendMessage();
    };

    window.resetChat = function() {
        window.chatHistory = [];
        window.location.reload();
    };

    window.speakText = function(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();

        const clean = text.replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F1E0}-\u{1F1FF}\*\_]/gu, '');
        const utterance = new SpeechSynthesisUtterance(clean);
        const isEnglish = /[a-zA-Z]{5,}/.test(clean);
        utterance.lang = isEnglish ? 'en-US' : 'ar-SA';
        utterance.rate = 1.0;
        utterance.pitch = 1.1;

        window.speechSynthesis.speak(utterance);
    };

    window.speakLastReply = function() {
        if (window.lastReplyText) {
            window.speakText(window.lastReplyText);
        }
    };

    window.sendMessage = async function() {
        const input = document.getElementById('userInput');
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        const chatContainer = document.getElementById('chatMessages');
        const time = getCurrentTime();

        const userHtml = `
            <div class="flex flex-col items-end message-bubble">
                <div class="bg-[#005c4b] text-white rounded-2xl rounded-tl-none p-3 max-w-[85%] shadow-md border border-emerald-600/30">
                    <div class="bubble-text leading-relaxed whitespace-pre-line text-xs">${escapeHtml(text)}</div>
                    <div class="text-[9px] text-emerald-200 text-left mt-1 font-mono flex items-center justify-end gap-1">
                        <span>${time}</span>
                        <i class="fa-solid fa-check-double text-[8px] text-sky-300"></i>
                    </div>
                </div>
            </div>
        `;
        chatContainer.insertAdjacentHTML('beforeend', userHtml);
        scrollToBottom();

        const typingIndicator = document.getElementById('typingIndicator');
        const typingText = document.getElementById('typingText');
        const isEn = /[a-zA-Z]{5,}/.test(text);
        typingText.innerText = isEn ? 'Nadia is typing' : 'نادية تكتب الآن';
        typingIndicator.classList.remove('hidden');
        scrollToBottom();

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

                window.chatHistory.push({ role: 'user', content: text });
                window.chatHistory.push({ role: 'assistant', content: replyText });

                const replyHtml = `
                    <div class="flex flex-col items-start message-bubble">
                        <div class="bg-[#202c33] text-slate-100 rounded-2xl rounded-tr-none p-3 max-w-[90%] shadow-md border border-slate-700/40">
                            <div class="font-bold text-[10px] text-sky-400 mb-1 flex items-center justify-between gap-2 border-b border-white/5 pb-1">
                                <span class="flex items-center gap-1">
                                    <span>نادية</span>
                                    <span class="text-slate-400 font-normal text-[9px]">(عناية عملاء مياه بيرين)</span>
                                </span>
                                <button onclick="window.speakText(this.closest('.message-bubble').querySelector('.bubble-text').innerText)" class="text-slate-400 hover:text-sky-300 transition" title="استماع">
                                    <i class="fa-solid fa-volume-high text-[10px]"></i>
                                </button>
                            </div>
                            <div class="bubble-text leading-relaxed whitespace-pre-line text-xs text-slate-100">${formatMarkdown(replyText)}</div>
                            <div class="text-[9px] text-slate-400 text-left mt-1 font-mono">
                                ${data.timestamp || getCurrentTime()}
                            </div>
                        </div>
                    </div>
                `;
                chatContainer.insertAdjacentHTML('beforeend', replyHtml);
                scrollToBottom();

            } else {
                throw new Error(data.reply || 'حدث خطأ غير متوقع');
            }

        } catch (err) {
            typingIndicator.classList.add('hidden');
            const errHtml = `
                <div class="flex flex-col items-start">
                    <div class="bg-[#202c33] text-rose-300 rounded-2xl p-3 max-w-[90%] shadow-md border border-rose-500/30 text-xs">
                        يا هلا والله أستاذ محمد! حدث ضغط مؤقت على الخادم، أعد إرسال سؤالك وأنا معك بالخدمة فوراً.
                    </div>
                </div>
            `;
            chatContainer.insertAdjacentHTML('beforeend', errHtml);
            scrollToBottom();
        }
    };

    document.getElementById('userInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.sendMessage();
        }
    });

    window.toggleRecording = async function() {
        if (window.isRecording) {
            window.stopRecording();
            return;
        }

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

            rec.onerror = function() {
                recBar.classList.add('hidden');
                window.isRecording = false;
                startMediaRecorder();
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

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

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
