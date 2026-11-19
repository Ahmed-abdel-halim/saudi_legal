@extends('layouts.app')

@section('title', 'ديمو مياه بيرين × رديف للذكاء الاصطناعي | المساعد الذكي "نادية"')

@section('content')

<style>
    /* Luxury Dark Atmosphere */
    .berain-bg-ambient {
        background-color: #060b13;
        background-image: 
            radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.12) 0px, transparent 50%),
            radial-gradient(at 50% 100%, rgba(30, 58, 138, 0.2) 0px, transparent 60%);
    }

    /* WhatsApp Wallpaper Pattern */
    .whatsapp-doodle-bg {
        background-color: #0b141a;
        background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                          radial-gradient(rgba(14, 165, 233, 0.06) 1px, transparent 1px);
        background-size: 22px 22px;
        background-position: 0 0, 11px 11px;
    }

    /* Titanium Phone Mockup */
    .phone-titanium {
        box-shadow: 
            0 0 0 3px #334155,
            0 0 0 5px #1e293b,
            0 25px 50px -12px rgba(0, 0, 0, 0.7),
            0 0 35px rgba(14, 165, 233, 0.25);
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

    /* Glowing Badge Pulse */
    @keyframes pulseGlow {
        0%, 100% { opacity: 0.8; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.05); }
    }
    .pulse-glow {
        animation: pulseGlow 2.5s infinite ease-in-out;
    }
</style>

<div class="min-h-screen berain-bg-ambient text-slate-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- Top Executive Co-Branding Banner --}}
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
                
                {{-- Title & Branding --}}
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-sky-500/15 text-sky-400 border border-sky-500/30 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                            <span>Enterprise AI Showcase</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                            <i class="fa-brands fa-whatsapp text-xs"></i>
                            <span>واتساب + ويب متزامن</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                            <i class="fa-solid fa-crown text-[10px]"></i>
                            <span>شخصية حقيقية 100%</span>
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight flex items-center gap-3">
                        <span class="text-3xl sm:text-5xl">💧</span>
                        <span class="bg-gradient-to-r from-white via-sky-200 to-sky-400 bg-clip-text text-transparent">
                            مياه بيرين × رديف للذكاء الاصطناعي
                        </span>
                    </h1>

                    <p class="text-slate-300 text-xs sm:text-sm max-w-3xl leading-relaxed">
                        نموذج مخصص فائق الذكاء لخدمة عملاء شركة <strong class="text-white font-bold">مياه بيرين</strong> بشخصية <strong class="text-sky-300 font-bold">"نادية"</strong>. يتحدث كإنسان حقيقي ويفهم اللهجات السعودية والمصرية والخليجية وكافة اللغات العالمية، مع استرجاع بيانات العميل الذهبي <strong class="text-white font-bold">محمد عبدالحميد</strong> ونقاطه وطلباته بدقة تامة.
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto flex-shrink-0">
                    <a href="https://wa.me/{{ $cleanBotPhone }}?text={{ urlencode('ديمو مياه بيرين') }}" target="_blank"
                       class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm transition shadow-lg shadow-emerald-500/25 group">
                        <i class="fa-brands fa-whatsapp text-xl group-hover:scale-110 transition-transform"></i>
                        <span>فتح على واتساب الحقيقي 📱</span>
                    </a>

                    <button onclick="window.resetChat()"
                       class="inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 hover:text-white font-bold text-xs border border-slate-700/80 transition shadow-sm"
                       title="إعادة بدء المحادثة">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>إعادة الضبط</span>
                    </button>
                </div>

            </div>

            {{-- 4 Pillars Highlights --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-slate-800/80 text-xs">
                <div class="flex items-center gap-2.5 text-slate-300">
                    <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-bolt text-xs"></i>
                    </div>
                    <span>استجابة فورية أقل من ثانية</span>
                </div>
                <div class="flex items-center gap-2.5 text-slate-300">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-microphone text-xs"></i>
                    </div>
                    <span>دعم الصوت والمحادثة الحية</span>
                </div>
                <div class="flex items-center gap-2.5 text-slate-300">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-language text-xs"></i>
                    </div>
                    <span>فهم كافة اللهجات واللغات</span>
                </div>
                <div class="flex items-center gap-2.5 text-slate-300">
                    <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                    </div>
                    <span>صفر فبركة (Grounded 100%)</span>
                </div>
            </div>
        </div>

        {{-- Main Two-Column Showcase Area --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Right Column (RTL: Left in display): The Smartphone Simulator (7 Cols) --}}
            <div class="lg:col-span-7 flex justify-center order-1 lg:order-2">

                {{-- Phone Outer Bezel --}}
                <div class="w-full max-w-[440px] rounded-[50px] p-3.5 bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 phone-titanium relative">
                    
                    {{-- Volume & Power Buttons Simulation --}}
                    <div class="absolute -left-1.5 top-28 w-1 h-12 bg-slate-700 rounded-l-md"></div>
                    <div class="absolute -left-1.5 top-44 w-1 h-12 bg-slate-700 rounded-l-md"></div>
                    <div class="absolute -right-1.5 top-36 w-1 h-16 bg-slate-700 rounded-r-md"></div>

                    {{-- Dynamic Island Top Notch --}}
                    <div class="w-32 h-4 bg-black rounded-full mx-auto mb-2 flex items-center justify-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-slate-900 border border-slate-800"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-[#0a1218] border border-slate-800"></div>
                    </div>

                    {{-- WhatsApp Screen Container --}}
                    <div class="rounded-[38px] overflow-hidden bg-[#0b141a] border border-slate-800/80 flex flex-col h-[700px] shadow-inner relative">

                        {{-- WhatsApp Header --}}
                        <div class="bg-[#202c33] text-white px-4 py-3 flex items-center justify-between border-b border-slate-800 z-10 shadow-md">
                            <div class="flex items-center gap-3 min-w-0">
                                <a href="javascript:void(0)" class="text-slate-400 hover:text-white transition text-xs">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                {{-- Nadia Avatar --}}
                                <div class="relative flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-400 via-sky-600 to-blue-700 flex items-center justify-center text-white font-black text-base shadow-md ring-2 ring-sky-400/30">
                                        💧
                                    </div>
                                    <span class="absolute bottom-0 left-0 w-3 h-3 bg-emerald-500 border-2 border-[#202c33] rounded-full"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-slate-100 truncate flex items-center gap-1.5">
                                        <span>نادية</span>
                                        <span class="text-[10px] text-sky-400 font-semibold bg-sky-500/10 px-1.5 py-0.5 rounded border border-sky-500/20">بيرين 💧</span>
                                    </div>
                                    <div class="text-[11px] text-emerald-400 truncate flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span>متصل الآن • تفهم كل اللهجات 🌍</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2.5 text-slate-400 text-sm">
                                <button onclick="window.speakLastReply()" title="استماع لرد نادية الصوتي" class="w-8 h-8 rounded-full hover:bg-white/5 hover:text-sky-400 flex items-center justify-center transition">
                                    <i class="fa-solid fa-volume-high text-xs"></i>
                                </button>
                                <a href="https://wa.me/{{ $cleanBotPhone }}?text={{ urlencode('ديمو مياه بيرين') }}" target="_blank" title="محادثة واتساب الحقيقية" class="w-8 h-8 rounded-full hover:bg-white/5 hover:text-emerald-400 flex items-center justify-center transition">
                                    <i class="fa-brands fa-whatsapp text-base"></i>
                                </a>
                                <button onclick="window.resetChat()" title="إعادة تشغيل المحادثة" class="w-8 h-8 rounded-full hover:bg-white/5 hover:text-white flex items-center justify-center transition">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- WhatsApp Chat Area --}}
                        <div id="chatMessages" class="flex-1 whatsapp-doodle-bg overflow-y-auto p-4 space-y-4 chat-scroll text-xs">
                            
                            {{-- Security Encryption Banner --}}
                            <div class="text-center my-1">
                                <span class="inline-block bg-[#182229]/95 text-[#ffd279] text-[10px] px-3.5 py-1.5 rounded-xl border border-[#ffd279]/20 shadow-sm max-w-[92%] leading-relaxed font-medium">
                                    🔒 الرسائل مشفرة تماماً. أنت تتحدث الآن مع "نادية" من عناية العملاء في مياه بيرين.
                                </span>
                            </div>

                            {{-- Date Bubble --}}
                            <div class="text-center my-1">
                                <span class="inline-block bg-[#182229] text-slate-400 text-[10px] px-3 py-0.5 rounded-lg font-mono border border-white/5">
                                    اليوم
                                </span>
                            </div>

                            {{-- Initial Welcoming Message --}}
                            <div class="flex flex-col items-start message-bubble">
                                <div class="bg-[#202c33] text-slate-100 rounded-2xl rounded-tr-none p-3.5 max-w-[90%] shadow-lg border border-slate-700/40 relative">
                                    <div class="font-bold text-[11px] text-sky-400 mb-1.5 flex items-center justify-between gap-2 border-b border-white/5 pb-1">
                                        <span class="flex items-center gap-1">
                                            <span>نادية</span>
                                            <span class="text-slate-400 font-normal text-[10px]">(عناية عملاء مياه بيرين)</span>
                                        </span>
                                        <button onclick="window.speakText(this.closest('.message-bubble').querySelector('.bubble-text').innerText)" class="text-slate-400 hover:text-sky-300 transition" title="استماع">
                                            <i class="fa-solid fa-volume-high text-[11px]"></i>
                                        </button>
                                    </div>
                                    <div class="bubble-text leading-relaxed whitespace-pre-line text-[13px] text-slate-100">
يا هلا والله أستاذ محمد! 👋💧
معك <strong class="text-sky-300 font-bold">نادية</strong> من عناية العملاء في مياه بيرين.. نورتنا والله!

بشرني، حاب نكرر طلبك الأخير زي العادة ولا ودك نطلب شي جديد اليوم للبيت أو للمسجد؟ 🤍

_(خذ راحتك كلمني بأي لهجة أو لغة تحبها، كتابة أو فويس 🎙️)_
                                    </div>
                                    <div class="text-[10px] text-slate-400 text-left mt-1.5 font-mono">
                                        {{ now()->format('h:i A') }}
                                    </div>
                                </div>

                                {{-- Quick Reply Chips inside Phone --}}
                                <div class="flex flex-wrap gap-1.5 mt-2.5 max-w-[92%]">
                                    <button onclick="window.sendScenario('كرري لي طلبي الأخير على حي الورود')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-sky-300 hover:text-sky-200 border border-sky-500/30 rounded-xl px-3 py-1.5 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                        <span>🔄 تكرار طلبي الأخير</span>
                                    </button>
                                    <button onclick="window.sendScenario('كم سعر كرتون مياه 600 مل؟')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-slate-200 hover:text-white border border-slate-700/60 rounded-xl px-3 py-1.5 text-xs font-medium transition shadow-sm">
                                        <span>💧 سعر كرتون 600 مل</span>
                                    </button>
                                    <button onclick="window.sendScenario('كم رصيدي من النقاط؟')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-slate-200 hover:text-white border border-slate-700/60 rounded-xl px-3 py-1.5 text-xs font-medium transition shadow-sm">
                                        <span>🎁 رصيد نقاطي</span>
                                    </button>
                                    <button onclick="window.sendScenario('أبي أتبرع لمسجد بمكة بموية بيرين')"
                                            class="bg-[#202c33] hover:bg-sky-950 text-slate-200 hover:text-white border border-slate-700/60 rounded-xl px-3 py-1.5 text-xs font-medium transition shadow-sm">
                                        <span>🕋 سقيا المساجد</span>
                                    </button>
                                </div>
                            </div>

                        </div>

                        {{-- Typing Indicator Bubble (Hidden by default) --}}
                        <div id="typingIndicator" class="hidden px-4 py-2 bg-[#0b141a] z-10">
                            <div class="bg-[#202c33] text-slate-300 rounded-2xl rounded-tr-none px-4 py-2.5 inline-flex items-center gap-2.5 shadow-md border border-slate-700/50">
                                <span class="text-xs text-sky-400 font-bold" id="typingText">نادية تكتب الآن</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 typing-dot"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Voice Recording Banner --}}
                        <div id="recordingBar" class="hidden bg-[#202c33] px-4 py-3 border-t border-slate-800 flex items-center justify-between text-xs text-rose-400 z-10">
                            <div class="flex items-center gap-2.5 font-bold animate-pulse">
                                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                <span>جاري تسجيل صوتك... تحدث بأي لهجة أو لغة 🎙️</span>
                            </div>
                            <button onclick="window.stopRecording()" class="bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 px-3 py-1 rounded-xl text-xs font-bold transition">
                                إرسال التسجيل
                            </button>
                        </div>

                        {{-- WhatsApp Input Bar --}}
                        <div class="bg-[#202c33] p-3 border-t border-slate-800/80 flex items-center gap-2 z-10">
                            
                            {{-- Mic Button --}}
                            <button id="micBtn" onclick="window.toggleRecording()"
                                    class="w-10 h-10 rounded-full bg-[#182229] hover:bg-sky-600 text-slate-300 hover:text-white flex items-center justify-center transition flex-shrink-0 shadow-sm"
                                    title="تحدث بالمايكروفون (Voice Note)">
                                <i class="fa-solid fa-microphone text-sm"></i>
                            </button>

                            {{-- Input Field --}}
                            <div class="flex-1 relative">
                                <input type="text" id="userInput"
                                       placeholder="اكتب لنادية بأي لهجة أو لغة..."
                                       class="w-full bg-[#2a3942] text-white text-xs sm:text-sm rounded-2xl px-4 py-2.5 outline-none placeholder-slate-400 border border-transparent focus:border-sky-500/50 transition">
                            </div>

                            {{-- Send Button --}}
                            <button id="sendBtn" onclick="window.sendMessage()"
                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white flex items-center justify-center transition shadow-md flex-shrink-0"
                                    title="إرسال">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Left Column (RTL: Right in display): Executive Control Center (5 Cols) --}}
            <div class="lg:col-span-5 space-y-6 order-2 lg:order-1">

                {{-- VIP Customer Intelligence Card --}}
                <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl relative overflow-hidden backdrop-blur-xl">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 border border-amber-500/25 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                            <span class="text-xs font-black text-white uppercase tracking-wider">سجل العميل الذكي (CRM Context)</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-black bg-gradient-to-r from-amber-500/20 to-yellow-500/20 text-amber-300 border border-amber-500/40 px-3 py-1 rounded-full shadow-sm">
                            <i class="fa-solid fa-crown text-[11px]"></i>
                            <span>عميل ذهبي</span>
                        </span>
                    </div>

                    {{-- Customer Identity --}}
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-lg flex-shrink-0">
                            م.ع
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-white text-base truncate">{{ $customer['full_name'] ?? 'محمد عبدالحميد (Mohammed Abdulhamid)' }}</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-400 font-mono">
                                <i class="fa-solid fa-phone text-[10px] text-sky-400"></i>
                                <span>+{{ $customer['mobile_number'] ?? '966540506796' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Metrics Grid --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-slate-800/50 p-3.5 rounded-2xl border border-slate-700/50 hover:border-sky-500/30 transition">
                            <span class="text-slate-400 text-xs block mb-1">نقاط الولاء (قطاف / مكافأة):</span>
                            <div class="flex items-baseline gap-1.5 font-mono">
                                <span class="text-2xl font-black text-sky-300">{{ $points }}</span>
                                <span class="text-xs text-slate-400 font-sans">نقطة</span>
                            </div>
                        </div>

                        <div class="bg-slate-800/50 p-3.5 rounded-2xl border border-slate-700/50 hover:border-emerald-500/30 transition">
                            <span class="text-slate-400 text-xs block mb-1">المنتج المفضل كالعادة:</span>
                            <span class="text-emerald-400 font-bold text-xs truncate block mt-1.5">بيرين 600 مل (24 عبوة)</span>
                        </div>

                        <div class="bg-slate-800/50 p-3.5 rounded-2xl border border-slate-700/50 col-span-2 hover:border-amber-500/30 transition">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">آخر طلب مسجل بالمنظومة:</span>
                                <span class="text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded text-[11px]">مكتمل ✅</span>
                            </div>
                            <div class="flex items-center justify-between mt-2 font-mono">
                                <span class="text-white font-bold text-sm">#{{ $lastOrder['order_id'] ?? '695818066' }}</span>
                                <span class="text-amber-400 font-black text-sm">{{ $lastOrder['amount'] ?? '48.77' }} ر.س</span>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Address & Habits --}}
                    <div class="space-y-2 text-xs bg-slate-800/30 p-3.5 rounded-2xl border border-slate-700/40">
                        <div class="flex items-start gap-2.5 text-slate-300">
                            <i class="fa-solid fa-location-dot text-sky-400 mt-1 flex-shrink-0"></i>
                            <span><strong>العناوين:</strong> حي الورود (الرياض) • جمعية جوار لسقيا المساجد (مكة المكرمة).</span>
                        </div>
                        <div class="flex items-start gap-2.5 text-slate-300">
                            <i class="fa-regular fa-clock text-amber-400 mt-1 flex-shrink-0"></i>
                            <span><strong>الموعد المفضل:</strong> الفترة المسائية (5 إلى 9 مساءً) مع ترك الطلب عند الباب.</span>
                        </div>
                    </div>
                </div>

                {{-- Interactive Scenarios Control Center --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl backdrop-blur-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-400 border border-sky-500/25 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-flask-vial"></i>
                            </span>
                            <h3 class="text-xs font-black text-white uppercase tracking-wider">بنك السيناريوهات واللهجات بنقرة واحدة</h3>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">6 سيناريوهات</span>
                    </div>

                    <div class="space-y-3">
                        {{-- Saudi Dialect --}}
                        <div class="bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/60 hover:border-sky-500/50 rounded-2xl p-3.5 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-sky-300">
                                    <span>🇸🇦</span>
                                    <span>اللهجة السعودية الدارجة (تكرار الطلب + موية أبو نص)</span>
                                </span>
                                <button onclick="window.sendScenario('هلا نادية، بالله كرري لي طلبي الأخير على عنواني بالرياض وأبي معه 5 كراتين موية أبو نص')"
                                        class="text-xs font-bold bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 border border-sky-500/30 px-3 py-1 rounded-xl transition flex items-center gap-1">
                                    <span>تشغيل</span>
                                    <i class="fa-solid fa-play text-[9px]"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                                "هلا نادية، بالله كرري لي طلبي الأخير على عنواني بالرياض وأبي معه 5 كراتين موية أبو نص"
                            </p>
                        </div>

                        {{-- Egyptian Dialect --}}
                        <div class="bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/60 hover:border-emerald-500/50 rounded-2xl p-3.5 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-300">
                                    <span>🇪🇬</span>
                                    <span>اللهجة المصرية (مية للمبرد + نقاط قطاف)</span>
                                </span>
                                <button onclick="window.sendScenario('إزيك يا نادية، عايز أطلب كرتونتين مية 600 مل وقارورة كبيرة للمبرد، وهل ينفع أدفع بنقاط قطاف؟')"
                                        class="text-xs font-bold bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/30 px-3 py-1 rounded-xl transition flex items-center gap-1">
                                    <span>تشغيل</span>
                                    <i class="fa-solid fa-play text-[9px]"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                                "إزيك يا نادية، عايز أطلب كرتونتين مية 600 مل وقارورة كبيرة للمبرد، وهل ينفع أدفع بنقاط قطاف؟"
                            </p>
                        </div>

                        {{-- Mosque Charity --}}
                        <div class="bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/60 hover:border-amber-500/50 rounded-2xl p-3.5 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-amber-300">
                                    <span>🕋</span>
                                    <span>سقيا المساجد بمكة (جمعية جوار)</span>
                                </span>
                                <button onclick="window.sendScenario('نادية أبي أرسل 10 كراتين موية لمسجد في مكة عن طريق جمعية جوار')"
                                        class="text-xs font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 px-3 py-1 rounded-xl transition flex items-center gap-1">
                                    <span>تشغيل</span>
                                    <i class="fa-solid fa-play text-[9px]"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                                "نادية أبي أرسل 10 كراتين موية لمسجد في مكة عن طريق جمعية جوار"
                            </p>
                        </div>

                        {{-- English Language --}}
                        <div class="bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/60 hover:border-indigo-500/50 rounded-2xl p-3.5 transition group" dir="ltr">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-indigo-300">
                                    <span>🇬🇧</span>
                                    <span>English (Sodium & Reorder)</span>
                                </span>
                                <button onclick="window.sendScenario('Hi Nadia, how much sodium is in Berain water? And can you repeat my last order?')"
                                        class="text-xs font-bold bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 border border-indigo-500/30 px-3 py-1 rounded-xl transition flex items-center gap-1">
                                    <span>Run</span>
                                    <i class="fa-solid fa-play text-[9px]"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                                "Hi Nadia, how much sodium is in Berain water? And can you repeat my last order?"
                            </p>
                        </div>

                        {{-- Quality SLA & Complaints --}}
                        <div class="bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/60 hover:border-rose-500/50 rounded-2xl p-3.5 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-rose-300">
                                    <span>🧪</span>
                                    <span>استفسار جودة وشكوى (SLA & تعويض)</span>
                                </span>
                                <button onclick="window.sendScenario('نادية، وصلني كرتون فيه طعم غريب وتاريخ إنتاجه جديد، ايش الإجراء؟')"
                                        class="text-xs font-bold bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 px-3 py-1 rounded-xl transition flex items-center gap-1">
                                    <span>تشغيل</span>
                                    <i class="fa-solid fa-play text-[9px]"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                                "نادية، وصلني كرتون فيه طعم غريب وتاريخ إنتاجه جديد، ايش الإجراء؟"
                            </p>
                        </div>
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
                <div class="bg-[#005c4b] text-white rounded-2xl rounded-tl-none p-3.5 max-w-[85%] shadow-md border border-emerald-600/30">
                    <div class="bubble-text leading-relaxed whitespace-pre-line text-[13px]">${escapeHtml(text)}</div>
                    <div class="text-[10px] text-emerald-200 text-left mt-1.5 font-mono flex items-center justify-end gap-1">
                        <span>${time}</span>
                        <i class="fa-solid fa-check-double text-[9px] text-sky-300"></i>
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
                        <div class="bg-[#202c33] text-slate-100 rounded-2xl rounded-tr-none p-3.5 max-w-[90%] shadow-lg border border-slate-700/40">
                            <div class="font-bold text-[11px] text-sky-400 mb-1.5 flex items-center justify-between gap-2 border-b border-white/5 pb-1">
                                <span class="flex items-center gap-1">
                                    <span>نادية</span>
                                    <span class="text-slate-400 font-normal text-[10px]">(عناية عملاء مياه بيرين)</span>
                                </span>
                                <button onclick="window.speakText(this.closest('.message-bubble').querySelector('.bubble-text').innerText)" class="text-slate-400 hover:text-sky-300 transition" title="استماع">
                                    <i class="fa-solid fa-volume-high text-[11px]"></i>
                                </button>
                            </div>
                            <div class="bubble-text leading-relaxed whitespace-pre-line text-[13px] text-slate-100">${formatMarkdown(replyText)}</div>
                            <div class="text-[10px] text-slate-400 text-left mt-1.5 font-mono">
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
                    <div class="bg-[#202c33] text-rose-300 rounded-2xl p-3.5 max-w-[90%] shadow-md border border-rose-500/30 text-xs">
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
