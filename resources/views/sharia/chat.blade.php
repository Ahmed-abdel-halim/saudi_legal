<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    @include('partials.google-analytics')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>المفتي الإسلامي الذكي | رديف - فتاوى شرعية موثقة بأدلة الكتاب والسنة</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">

    {{-- External CSS/JS Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Tailwind Configuration --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Tajawal', 'sans-serif'],
                        'quran': ['Amiri', 'serif'],
                    },
                    colors: {
                        'dark-navy':   '#070d19',
                        'dark-card':   '#0d1527',
                        'dark-border': '#1b273d',
                        'sharia-green': '#059669',
                        'sharia-gold':  '#d97706',
                        'sharia-teal':  '#0d9488',
                    }
                }
            }
        }
    </script>
    <script>
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme', 'dark');
    </script>

    <style>
        body, input, button, select, textarea { font-family: 'Tajawal', sans-serif; }
        .font-quran { font-family: 'Amiri', serif; }

        body {
            background-color: #070d19;
            background-image:
                radial-gradient(ellipse at 15% 15%, rgba(5, 150, 105, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 85% 85%, rgba(217, 119, 6, 0.06) 0%, transparent 60%);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .glass-panel {
            background: rgba(13, 21, 39, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-panel-hover:hover {
            border-color: rgba(5, 150, 105, 0.35);
            background: rgba(13, 21, 39, 0.9);
        }

        /* Custom Scrollbar */
        * { scrollbar-width: thin; scrollbar-color: rgba(5, 150, 105, 0.3) transparent; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(5, 150, 105, 0.35); border-radius: 999px; }
    </style>
</head>

<body class="text-slate-100 min-h-screen flex flex-col antialiased selection:bg-emerald-500 selection:text-white">

    <div class="flex h-screen overflow-hidden relative z-10">

        {{-- ── SIDEBAR ────────────────────────────────────────── --}}
        <aside id="sidebar"
               class="w-80 bg-[#0a1222]/95 border-l border-white/10 flex flex-col justify-between transition-all duration-300 z-40 fixed md:relative h-full -translate-x-full md:translate-x-0 rtl:translate-x-full md:rtl:translate-x-0">
            
            {{-- Header of Sidebar --}}
            <div class="p-4 border-b border-white/5 space-y-3">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-lg shadow-emerald-900/30 ring-1 ring-emerald-400/40 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-kaaba text-sm"></i>
                        </div>
                        <div>
                            <div class="font-black text-sm tracking-wide text-white group-hover:text-emerald-400 transition-colors">المفتي الذكي</div>
                            <div class="text-[10px] text-slate-400 font-medium">منظومة رديف السيادية</div>
                        </div>
                    </a>
                    <span class="text-[9px] bg-emerald-500/15 text-emerald-300 font-bold px-2.5 py-0.5 rounded-full border border-emerald-500/30 flex items-center gap-1">
                        <i class="fa-solid fa-certificate text-[8px]"></i> موثق شرعياً
                    </span>
                </div>

                {{-- New Fatwa Consultation Button --}}
                <button onclick="startNewConversation()"
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-950/40 hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>استفتاء جديد</span>
                </button>

                {{-- Switch to Legal Assistant --}}
                <a href="{{ route('legal_assistant.public') }}"
                   class="w-full py-2 px-3 rounded-lg bg-indigo-950/40 hover:bg-indigo-900/50 border border-indigo-500/20 text-indigo-300 text-[11px] font-semibold flex items-center justify-between transition">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-scale-balanced text-indigo-400 text-xs"></i>
                        الانتقال للمساعد القانوني
                    </span>
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                </a>
            </div>

            {{-- Recent Conversations List --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-1" id="conversations-list">
                <div class="text-[10px] font-bold text-slate-400 px-2 py-1 uppercase tracking-wider">سجل الفتاوى والمسائل</div>
                <div id="conversations-container" class="space-y-1">
                    {{-- Dynamically Populated --}}
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <i class="fa-solid fa-spinner fa-spin text-emerald-500 mb-2"></i>
                        <div>جارٍ جلب السجل...</div>
                    </div>
                </div>
            </div>

            {{-- Footer of Sidebar --}}
            <div class="p-3 border-t border-white/5 bg-slate-900/60 text-xs space-y-2">
                <div class="flex items-center gap-2 text-slate-300 text-[11px]">
                    <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                    <span>المنهج: <strong class="text-emerald-400">أدلة قطعية من الكتاب والسنة</strong></span>
                </div>
                <div class="text-[10px] text-slate-400 leading-relaxed">
                    مستند حصراً للقرآن وصحيح السنة وفتاوى هيئة كبار العلماء واللجنة الدائمة.
                </div>
            </div>
        </aside>

        {{-- ── MAIN CHAT AREA ────────────────────────────────── --}}
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">

            {{-- Top Navbar --}}
            <header class="h-16 border-b border-white/10 px-4 md:px-6 flex items-center justify-between glass-panel z-20">
                <div class="flex items-center gap-3">
                    {{-- Mobile toggle sidebar button --}}
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h1 class="text-sm md:text-base font-bold text-white flex items-center gap-2">
                            المفتي الإسلامي الذكي
                            <span class="hidden sm:inline-block text-[10px] font-normal text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                                الإصدار الشرعي 1.0
                            </span>
                        </h1>
                    </div>
                </div>

                {{-- Badges & Actions --}}
                <div class="flex items-center gap-2">
                    <div class="hidden lg:flex items-center gap-2 text-xs text-slate-400">
                        <span class="flex items-center gap-1 bg-white/5 px-2.5 py-1 rounded-full border border-white/5">
                            <i class="fa-solid fa-book-quran text-emerald-400"></i> نصوص القرآن
                        </span>
                        <span class="flex items-center gap-1 bg-white/5 px-2.5 py-1 rounded-full border border-white/5">
                            <i class="fa-solid fa-scroll text-amber-400"></i> الصحاح والسنن
                        </span>
                        <span class="flex items-center gap-1 bg-white/5 px-2.5 py-1 rounded-full border border-white/5">
                            <i class="fa-solid fa-user-check text-teal-400"></i> تدقيق العلماء
                        </span>
                    </div>

                    <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-white px-3 py-1.5 rounded-lg hover:bg-white/5 transition flex items-center gap-1">
                        <i class="fa-solid fa-house"></i>
                        <span class="hidden sm:inline">الرئيسية</span>
                    </a>
                </div>
            </header>

            {{-- Messages Scroll Area --}}
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">

                {{-- Hero / Welcome State (Visible when no messages yet) --}}
                <div id="welcome-screen" class="max-w-3xl mx-auto my-auto py-8 text-center space-y-6">
                    
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-xl shadow-emerald-950/50 ring-4 ring-emerald-500/20">
                        <i class="fa-solid fa-kaaba text-2xl"></i>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl md:text-3xl font-black text-white font-quran">
                            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                        </h2>
                        <p class="text-sm md:text-base text-emerald-300/90 font-medium max-w-xl mx-auto">
                            مساعد إفتائي ذكي مؤصل، يُجيبك بالأدلة القطعية من القرآن الكريم وصحيح السنة النبوية وفتاوى كبار العلماء المعتبرين.
                        </p>
                        <div class="text-xs text-slate-400 font-quran italic">
                            ﴿فَاسْأَلُوا أَهْلَ الذِّكْرِ إِن كُنتُمْ لَا تَعْلَمُونَ﴾ [الأنبياء: 7]
                        </div>
                    </div>

                    {{-- Quick Prompt Categories --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto pt-4 text-right">
                        
                        {{-- 1. العبادات --}}
                        <button onclick="askPreset('متى يجوز للمسافر قصر الصلاة وجمعها؟ وما هي المسافة والمدة المحددة شرعاً؟')"
                                class="p-3.5 rounded-xl glass-panel glass-panel-hover transition text-right group">
                            <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs mb-1">
                                <i class="fa-solid fa-mosque"></i>
                                <span>فقه العبادات والصلاة</span>
                            </div>
                            <div class="text-[12px] text-slate-300 group-hover:text-white transition leading-relaxed">
                                متى يجوز للمسافر قصر الصلاة وجمعها؟ وما هي المسافة المعتبرة؟
                            </div>
                        </button>

                        {{-- 2. المعاملات والتمويل --}}
                        <button onclick="askPreset('ما حكم الحصول على قرض بفائدة أو تمويل عقاري تقليدي يفرض نسبة زيادة على رأس المال؟')"
                                class="p-3.5 rounded-xl glass-panel glass-panel-hover transition text-right group">
                            <div class="flex items-center gap-2 text-amber-400 font-bold text-xs mb-1">
                                <i class="fa-solid fa-coins"></i>
                                <span>المعاملات المالية والتمويل</span>
                            </div>
                            <div class="text-[12px] text-slate-300 group-hover:text-white transition leading-relaxed">
                                حكم القروض البنكية بفائدة والتمويل العقاري التقليدي والمرابحة
                            </div>
                        </button>

                        {{-- 3. العملات الرقمية والأسهم --}}
                        <button onclick="askPreset('ما حكم الاستثمار والمضاربة في العملات الرقمية المشفرة مثل البيتكوين؟')"
                                class="p-3.5 rounded-xl glass-panel glass-panel-hover transition text-right group">
                            <div class="flex items-center gap-2 text-teal-400 font-bold text-xs mb-1">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>الأسهم والنوازل المعاصرة</span>
                            </div>
                            <div class="text-[12px] text-slate-300 group-hover:text-white transition leading-relaxed">
                                حكم التداول والمضاربة في العملات الرقمية المشفرة
                            </div>
                        </button>

                        {{-- 4. فقه الأسرة --}}
                        <button onclick="askPreset('ما هو الخلع شرعاً؟ وهل يلزم الزوجة إعادة المهر كاملاً؟ وما هي عدتها؟')"
                                class="p-3.5 rounded-xl glass-panel glass-panel-hover transition text-right group">
                            <div class="flex items-center gap-2 text-rose-400 font-bold text-xs mb-1">
                                <i class="fa-solid fa-people-roof"></i>
                                <span>فقه الأسرة والأحوال الشخصية</span>
                            </div>
                            <div class="text-[12px] text-slate-300 group-hover:text-white transition leading-relaxed">
                                أحكام الخلع ورد المهر وعدة المختلعة وحقوق الزوجين
                            </div>
                        </button>
                    </div>

                    {{-- Assurance Badge --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-950/40 border border-emerald-500/20 text-[11px] text-emerald-300/80">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>كل جواب موثق بنص الآية ورقمها، والحديث وتخريجه، ورأي العالم المعتبر.</span>
                    </div>

                </div>

            </div>

            {{-- ── BOTTOM INPUT BAR ──────────────────────────────── --}}
            <div class="p-4 border-t border-white/10 glass-panel relative z-20">
                <div class="max-w-4xl mx-auto">
                    <form id="chat-form" onsubmit="handleSend(event)" class="relative flex items-end gap-2">
                        
                        <div class="flex-1 relative rounded-2xl bg-slate-900/90 border border-white/10 focus-within:border-emerald-500/60 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all shadow-inner">
                            <textarea id="question-input"
                                      rows="1"
                                      placeholder="اكتب سؤالك أو استفسارك الشرعي هنا (مثال: ما حكم زكاة عروض التجارة وكيف تُحسب؟)..."
                                      class="w-full bg-transparent text-white px-4 py-3.5 text-sm resize-none focus:outline-none placeholder-slate-500 max-h-36 overflow-y-auto"
                                      onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); handleSend(event); }"></textarea>
                        </div>

                        <button type="submit"
                                id="send-btn"
                                class="h-12 w-12 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-950/50 hover:scale-105 active:scale-95 transition-all flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-paper-plane text-sm rtl:-scale-x-100"></i>
                        </button>
                    </form>

                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2 px-1">
                        <span>المفتي الذكي يعتمد حصراً على المراجع الموثقة ويلتزم بالورع الشرعي في عدم الإفتاء عند غياب الدليل.</span>
                        <span class="text-emerald-400/90 text-[11px] font-semibold flex items-center gap-1.5 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                            <i class="fa-solid fa-check-double text-[9px]"></i> مؤصل وموثق
                        </span>
                    </div>
                </div>
            </div>

        </main>
    </div>

    {{-- ── JAVASCRIPT LOGIC ────────────────────────────────── --}}
    <script>
        let currentConversationUuid = null;
        let isGenerating = false;

        // Toggle mobile sidebar
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            sb.classList.toggle('-translate-x-full');
            sb.classList.toggle('rtl:translate-x-full');
        }

        // Start new conversation
        function startNewConversation() {
            currentConversationUuid = null;
            document.getElementById('chat-messages').innerHTML = '';
            document.getElementById('welcome-screen')?.remove();
            
            // Re-render hero screen
            location.reload();
        }

        // Quick ask preset
        function askPreset(text) {
            const input = document.getElementById('question-input');
            input.value = text;
            handleSend(new Event('submit'));
        }

        // Handle Send
        async function handleSend(e) {
            if (e) e.preventDefault();
            if (isGenerating) return;

            const input = document.getElementById('question-input');
            const question = input.value.trim();
            if (!question) return;

            // Remove welcome screen if present
            document.getElementById('welcome-screen')?.remove();

            // Append User Bubble
            appendUserMessage(question);
            input.value = '';
            input.style.height = 'auto';

            // Show Loading Bubble
            const loadingId = appendLoadingBubble();
            isGenerating = true;
            document.getElementById('send-btn').disabled = true;

            try {
                const res = await fetch("{{ route('fatwa_assistant.public.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        question: question,
                        conversation_uuid: currentConversationUuid
                    })
                });

                const data = await res.json();
                removeLoadingBubble(loadingId);

                if (data.conversation_uuid) {
                    currentConversationUuid = data.conversation_uuid;
                }

                appendAssistantMessage(data.answer, data.citations, data.ai_message_id, question);
                loadConversations();

            } catch (err) {
                console.error(err);
                removeLoadingBubble(loadingId);
                appendAssistantMessage("نعتذر، حدث خطأ أثناء الاتصال بالنظام الشرعي. يرجى إعادة المحاولة.", null, null, question);
            } finally {
                isGenerating = false;
                document.getElementById('send-btn').disabled = false;
                scrollToBottom();
            }
        }

        function appendUserMessage(text) {
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = "flex items-start gap-3 justify-end";
            div.innerHTML = `
                <div class="max-w-2xl bg-gradient-to-br from-emerald-600/90 to-teal-800/90 text-white rounded-2xl rounded-tr-none px-4 py-3 text-sm shadow-md border border-emerald-400/20 leading-relaxed">
                    ${escapeHtml(text)}
                </div>
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center text-slate-300 text-xs flex-shrink-0">
                    <i class="fa-solid fa-user"></i>
                </div>
            `;
            container.appendChild(div);
            scrollToBottom();
        }

        function appendLoadingBubble() {
            const id = 'loading-' + Date.now();
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.id = id;
            div.className = "flex items-start gap-3";
            div.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-xs flex-shrink-0">
                    <i class="fa-solid fa-kaaba"></i>
                </div>
                <div class="glass-panel text-slate-300 rounded-2xl rounded-tl-none p-4 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin text-emerald-400"></i>
                    <span>جارٍ استرجاع الأدلة من القرآن وصحيح السنة وتأصيل الفتوى...</span>
                </div>
            `;
            container.appendChild(div);
            scrollToBottom();
            return id;
        }

        function removeLoadingBubble(id) {
            document.getElementById(id)?.remove();
        }

        function appendAssistantMessage(text, citations, messageId, originalQuestion) {
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = "flex items-start gap-3";

            // Render Citations HTML
            let citationsHtml = '';
            if (citations) {
                let quranCards = '';
                if (citations.quran && citations.quran.length > 0) {
                    quranCards = `
                        <div class="space-y-1.5 mt-3">
                            <div class="text-[11px] font-bold text-emerald-400 flex items-center gap-1">
                                <i class="fa-solid fa-book-quran"></i> الأدلة من القرآن الكريم:
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                ${citations.quran.map(q => `
                                    <div class="p-2.5 rounded-lg bg-emerald-950/30 border border-emerald-500/20 text-xs">
                                        <div class="font-quran text-sm text-emerald-200">﴿ ${escapeHtml(q.text || '')} ﴾</div>
                                        <div class="text-[10px] text-emerald-400/80 mt-1 font-sans">سورة ${escapeHtml(q.surah || '')} - آية ${q.ayah || ''}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                let hadithCards = '';
                if (citations.hadiths && citations.hadiths.length > 0) {
                    hadithCards = `
                        <div class="space-y-1.5 mt-3">
                            <div class="text-[11px] font-bold text-amber-400 flex items-center gap-1">
                                <i class="fa-solid fa-scroll"></i> الأدلة من السنة النبوية المطهرة:
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                ${citations.hadiths.map(h => `
                                    <div class="p-2.5 rounded-lg bg-amber-950/25 border border-amber-500/20 text-xs">
                                        <div class="text-amber-100 font-medium">«${escapeHtml(h.text || '')}»</div>
                                        <div class="text-[10px] text-amber-400/80 mt-1 flex items-center gap-2">
                                            <span>المصدر: ${escapeHtml(h.source || '')}</span>
                                            <span class="bg-amber-500/20 text-amber-300 px-1.5 py-0.2 rounded">${escapeHtml(h.grade || 'صحيح')}</span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                let authorityBadge = '';
                if (citations.authorities && citations.authorities.length > 0) {
                    authorityBadge = `
                        <div class="mt-3 pt-2 border-t border-white/5 flex items-center justify-between text-[11px] text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-certificate text-emerald-400"></i>
                                <span>المرجع المعتمد: <strong class="text-slate-200">${escapeHtml(citations.authorities.join('، '))}</strong></span>
                            </div>
                            <span class="bg-emerald-500/15 text-emerald-300 px-2 py-0.5 rounded-full text-[10px] border border-emerald-500/30">
                                <i class="fa-solid fa-check-double text-[9px]"></i> مدققة شرعياً
                            </span>
                        </div>
                    `;
                }

                citationsHtml = quranCards + hadithCards + authorityBadge;
            }

            // Formatted text
            const formattedText = formatMarkdown(text);

            div.innerHTML = `
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white text-xs flex-shrink-0 shadow-md">
                    <i class="fa-solid fa-kaaba"></i>
                </div>
                <div class="max-w-3xl glass-panel text-slate-100 rounded-2xl rounded-tl-none p-5 text-sm shadow-xl leading-relaxed border border-white/10 space-y-3">
                    <div class="prose prose-invert prose-emerald max-w-none text-slate-200">
                        ${formattedText}
                    </div>

                    ${citationsHtml}

                    {{-- Feedback & Actions Bar --}}
                    <div class="pt-3 mt-3 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-3">
                            <button onclick="copyToClipboard(this)" data-text="${escapeHtml(text)}" class="hover:text-emerald-400 transition flex items-center gap-1">
                                <i class="fa-regular fa-copy"></i>
                                <span>نسخ</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2" id="feedback-actions-${messageId}">
                            <span class="text-[11px]">هل كانت الفتوى واضحة؟</span>
                            <button onclick="submitFeedback('like', ${messageId}, '${escapeHtml(originalQuestion)}')" class="p-1 text-slate-400 hover:text-emerald-400 transition" title="مفيدة وموثقة">
                                <i class="fa-regular fa-thumbs-up"></i>
                            </button>
                            <button onclick="submitFeedback('dislike', ${messageId}, '${escapeHtml(originalQuestion)}')" class="p-1 text-slate-400 hover:text-rose-400 transition" title="غير واضحة أو تحتاج تدقيقاً">
                                <i class="fa-regular fa-thumbs-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(div);
            scrollToBottom();
        }

        // Format basic markdown
        function formatMarkdown(content) {
            if (!content) return '';
            let html = escapeHtml(content);

            // Bold
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-white font-bold">$1</strong>');

            // Quran brackets ﴿ ... ﴾
            html = html.replace(/﴿([^﴾]+)﴾/g, '<span class="font-quran text-base text-emerald-300 font-semibold px-1">﴿ $1 ﴾</span>');

            // Hadith quotes « ... »
            html = html.replace(/«([^»]+)»/g, '<span class="text-amber-200 font-medium bg-amber-950/20 px-1 rounded">«$1»</span>');

            // Line breaks
            html = html.replace(/\n\n/g, '<div class="my-3"></div>');
            html = html.replace(/\n/g, '<br>');

            return html;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function scrollToBottom() {
            const container = document.getElementById('chat-messages');
            container.scrollTop = container.scrollHeight;
        }

        function copyToClipboard(btn) {
            const text = btn.getAttribute('data-text');
            navigator.clipboard.writeText(text).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i> <span class="text-emerald-400">تم النسخ!</span>';
                setTimeout(() => { btn.innerHTML = orig; }, 2000);
            });
        }

        async function submitFeedback(rating, messageId, query) {
            try {
                const res = await fetch("{{ route('fatwa_assistant.feedback') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        rating: rating,
                        ai_message_id: messageId,
                        conversation_uuid: currentConversationUuid,
                        user_query: query
                    })
                });
                const data = await res.json();
                const container = document.getElementById(`feedback-actions-${messageId}`);
                if (container) {
                    container.innerHTML = `<span class="text-emerald-400 font-medium text-[11px]"><i class="fa-solid fa-check"></i> ${data.message}</span>`;
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function loadConversations() {
            try {
                const res = await fetch("{{ route('fatwa_assistant.conversations') }}");
                const data = await res.json();
                const container = document.getElementById('conversations-container');
                if (!container) return;

                if (!data.conversations || data.conversations.length === 0) {
                    container.innerHTML = '<div class="text-slate-400 text-xs text-center py-4">لا توجد استشارات سابقة بعد.</div>';
                    return;
                }

                container.innerHTML = data.conversations.map(c => `
                    <div class="flex items-center justify-between p-2 rounded-lg hover:bg-white/5 group text-xs transition cursor-pointer ${currentConversationUuid === c.uuid ? 'bg-emerald-950/40 border border-emerald-500/30' : ''}"
                         onclick="loadConversationMessages('${c.uuid}')">
                        <div class="flex items-center gap-2 truncate flex-1">
                            <i class="fa-solid fa-kaaba text-emerald-500 text-[11px]"></i>
                            <span class="truncate text-slate-300 group-hover:text-white">${escapeHtml(c.title || 'استفتاء شرعي')}</span>
                        </div>
                    </div>
                `).join('');
            } catch (e) {
                console.error(e);
            }
        }

        async function loadConversationMessages(uuid) {
            currentConversationUuid = uuid;
            const container = document.getElementById('chat-messages');
            container.innerHTML = '<div class="text-center py-12 text-slate-400 text-xs"><i class="fa-solid fa-spinner fa-spin text-emerald-500 text-lg mb-2"></i><div>جارٍ تحميل الفتوى...</div></div>';

            try {
                const res = await fetch(`/fatwa-assistant/conversations/${uuid}`);
                const data = await res.json();
                container.innerHTML = '';

                data.messages.forEach(m => {
                    if (m.role === 'user') {
                        appendUserMessage(m.message);
                    } else {
                        appendAssistantMessage(m.message, m.citations, null, '');
                    }
                });
                loadConversations();
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="text-center py-8 text-rose-400 text-xs">تعذر تحميل المحادثة.</div>';
            }
        }

        // Auto-resize textarea
        document.getElementById('question-input')?.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 140) + 'px';
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            loadConversations();
        });
    </script>
</body>
</html>
