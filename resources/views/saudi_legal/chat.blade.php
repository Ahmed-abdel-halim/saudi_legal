<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المساعد القانوني الذكي | رديف</title>
    
    {{-- External CSS/JS Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Tailwind Configuration --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Tajawal', 'sans-serif'],
                    },
                    colors: {
                        'dark-navy':       '#0b1120',
                        'dark-card':       '#111827',
                        'dark-border':     '#1f2d40',
                        'slate-light':     '#F8FAFC',
                        'brand-primary':   '#4F46E5',
                        'brand-secondary': '#8B5CF6',
                        'brand-dark':      '#1E293B',
                        'brand-green':     '#0d9488',
                        'brand-green-dim': '#0f766e',
                        'brand-teal':      '#0d9488',
                        'brand-cyan':      '#06b6d4',
                    },
                    backgroundImage: {
                        'gradient-primary': 'linear-gradient(135deg, #4F46E5 0%, #8B5CF6 100%)',
                        'gradient-green':   'linear-gradient(135deg, #0f766e 0%, #0d9488 100%)',
                    },
                    boxShadow: {
                        'glow':       '0 0 20px rgba(79, 70, 229, 0.4)',
                        'green-glow': '0 0 20px rgba(13, 148, 136, 0.35)',
                        'teal-glow':  '0 0 15px rgba(13, 148, 136, 0.3)',
                    }
                }
            }
        }
    </script>
    <script>
        // Init theme immediately to prevent layout shift
        if (!localStorage.getItem('color-theme')) {
            localStorage.setItem('color-theme', 'dark');
        }
        if (localStorage.getItem('color-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <style>
        body, input, button, select, textarea { font-family: 'Tajawal', sans-serif; }
        
        /* Radial Gradients Background (Platform Identity) */
        .premium-bg {
            background-color: transparent;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(79, 70, 229, 0.03) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(13, 148, 136, 0.03) 0%, transparent 55%);
            position: relative;
            overflow: hidden;
        }
        .dark .premium-bg {
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(79, 70, 229, 0.08) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(13, 148, 136, 0.07) 0%, transparent 55%);
        }

        /* Subtle dot-grid overlay on the page body */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(15, 23, 42, 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }
        .dark body::before {
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
        }

        /* Glassmorphism Classes (Light/Dark Responsive) */
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 32px 0 rgba(15, 23, 42, 0.03);
        }
        .dark .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-left: 1px solid rgba(15, 23, 42, 0.05);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.01);
        }
        .dark .glass-sidebar {
            background: rgba(17, 24, 39, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-left: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: -4px 0 24px rgba(0, 0, 0, 0.2);
        }

        .glass-bubble {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border-radius: 2rem 2rem 0 2rem;
        }
        .dark .glass-bubble {
            background: rgba(17, 24, 39, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        }

        /* Gradient Text (Brand Colors) */
        .text-gradient {
            background: linear-gradient(to left, #0d9488, #4F46E5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Input Glow */
        .input-glow:focus-within {
            box-shadow: 0 0 30px rgba(13, 148, 136, 0.15);
            border-color: rgba(13, 148, 136, 0.4);
        }
        .dark .input-glow:focus-within {
            box-shadow: 0 0 30px rgba(13, 148, 136, 0.25);
            border-color: rgba(13, 148, 136, 0.5);
        }

        /* Scrollbar styles matching the brand colors */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(13, 148, 136, 0.3);
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(13, 148, 136, 0.6);
        }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-dark-navy h-screen h-[100dvh] overflow-hidden flex font-sans antialiased text-slate-800 dark:text-slate-100 transition-colors duration-300">

    <!-- Sidebar (سجل المحادثات الجانبي) -->
    <aside id="chat-sidebar" class="w-80 h-full glass-sidebar flex flex-col z-30 transition-all duration-300 fixed right-0 top-0 transform translate-x-0">
        <!-- Sidebar Header: New Chat Button -->
        <div class="p-4 border-b border-slate-200/50 dark:border-white/10">
            <button onclick="startNewChat()" class="w-full py-3.5 px-4 bg-gradient-to-br from-brand-green to-brand-green-dim text-white rounded-2xl flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-brand-green/20 active:scale-[0.98] transition-all cursor-pointer font-bold text-sm shadow-md">
                <i class="fa-solid fa-plus text-xs"></i>
                محادثة جديدة
            </button>
        </div>

        <!-- Sidebar Content: Chat List -->
        <div class="flex-1 overflow-y-auto px-3 py-4 custom-scrollbar flex flex-col gap-2" id="conversations-list">
            <!-- Loading Skeleton -->
            <div class="animate-pulse flex flex-col gap-3 p-2">
                <div class="h-10 bg-slate-200/60 dark:bg-slate-700/60 rounded-xl"></div>
                <div class="h-10 bg-slate-200/60 dark:bg-slate-700/60 rounded-xl"></div>
                <div class="h-10 bg-slate-200/60 dark:bg-slate-700/60 rounded-xl"></div>
            </div>
        </div>

        <!-- Sidebar Usage Limit Widget (مؤشر حدود الاستخدام والرسائل المجانية) -->
        <div id="usage-limit-widget" class="px-4 py-3.5 mx-3 mb-4 bg-white/70 dark:bg-dark-card/70 border border-slate-200/50 dark:border-white/10 rounded-2xl shadow-sm hidden">
            <div class="flex items-center justify-between text-[11px] font-bold mb-1.5 text-slate-700 dark:text-slate-350">
                <span id="usage-limit-label">الرسائل المتبقية:</span>
                <span id="usage-limit-ratio">...</span>
            </div>
            <div class="w-full bg-slate-200/80 dark:bg-slate-700/80 rounded-full h-1.5 overflow-hidden">
                <div id="usage-limit-bar" class="bg-gradient-to-r from-brand-green to-brand-teal h-full rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div id="usage-limit-action" class="mt-2 text-center">
                <!-- Injected link/button -->
            </div>
        </div>

        <!-- Sidebar Footer: Logged User info -->
        <div class="p-4 border-t border-slate-200/50 dark:border-white/10 bg-white/20 dark:bg-white/5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brand-green/10 text-brand-green flex items-center justify-center text-sm font-bold shadow-sm">
                    @auth
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    @else
                        ز
                    @endauth
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black text-slate-700 dark:text-slate-300">
                        @auth
                            {{ auth()->user()->name }}
                        @else
                            زائر الخدمة
                        @endauth
                    </span>
                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500">الوصول المجاني</span>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden w-8 h-8 rounded-lg bg-slate-200/60 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 flex items-center justify-center text-xs hover:bg-slate-200 dark:hover:bg-slate-800">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </aside>

    <!-- Backdrop for mobile sidebar -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-20 hidden backdrop-blur-sm transition-all duration-300"></div>

    <!-- Main Chat Window (النافذة الرئيسية) -->
    <div id="main-content" class="flex-1 h-full flex flex-col relative overflow-hidden transition-all duration-300 md:mr-80">
        
        <!-- Navbar -->
        <nav class="relative z-10 glass-panel px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="w-10 h-10 rounded-full bg-white dark:bg-white/5 flex items-center justify-center text-slate-500 hover:text-brand-green hover:bg-teal-50 dark:hover:bg-teal-950/30 transition shadow-sm cursor-pointer border border-slate-200/50 dark:border-white/10">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="flex items-center gap-2 sm:gap-3">
                    <img src="{{ asset('images/icon.png') }}"
                        onerror="this.src='https://placehold.co/40x40/0d9488/0b1120?text=R'"
                        alt="Logo"
                        class="h-8 w-8 sm:h-10 sm:w-10 rounded-full shadow-md object-cover ring-2 ring-brand-green/30">
                    <div>
                        <h1 class="text-sm sm:text-lg font-black text-slate-800 dark:text-slate-100 tracking-tight">رديف القانوني</h1>

                    </div>
                </div>
            </div>

            <!-- Title in center -->
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 hidden lg:block">
                <h2 class="text-2xl font-black text-gradient tracking-tight">المستشار القضائي والنظامي الذكي</h2>
            </div>

            <!-- Exit button -->
            <a href="{{ url('/') }}" class="w-10 h-10 rounded-full bg-white dark:bg-white/5 flex items-center justify-center text-slate-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition shadow-sm border border-slate-200/50 dark:border-white/10">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>

        <!-- Chat Container -->
        <div class="premium-bg flex-1 w-full flex flex-col relative overflow-hidden">
            <main id="chat-container" class="flex-1 relative z-10 flex flex-col max-w-7xl mx-auto w-full px-4 pt-10 pb-4 overflow-y-auto custom-scrollbar">
                
                <!-- Welcome visuals / screen -->
                <div class="flex-1 flex flex-col items-center justify-center relative mb-12" id="welcome-visuals">
                    <img src="{{ asset('images/icon.png') }}"
                        onerror="this.src='https://placehold.co/80x80/0d9488/0b1120?text=R'"
                        alt="Radiif Logo"
                        class="w-20 h-20 rounded-full shadow-xl object-cover ring-4 ring-brand-green/30 mb-6">
                    <div class="glass-bubble p-8 max-w-2xl text-center relative border border-slate-200/50 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-800 dark:text-slate-100 mb-3">مرحباً بك في مستشارك القانوني الذكي!</h2>
                        <p class="text-sm text-slate-650 dark:text-slate-300 leading-relaxed font-medium">
                            أنا هنا لمساعدتك في استخراج السوابق والأحكام القضائية، وقراءة نصوص الأنظمة والتشريعات السعودية المترابطة بالمعنى الدلالي. محادثتي الآن مزودة بالذاكرة الكاملة لحفظ سياق حديثك.
                        </p>
                    </div>
                </div>

                <!-- Chat messages log -->
                <div id="chat-messages" class="flex flex-col w-full max-w-4xl mx-auto mt-6 px-2 pb-4">
                </div>
                
            </main>

            <!-- Chat input area -->
            <div class="w-full relative z-20 pb-[calc(env(safe-area-inset-bottom)+2rem)] md:pb-6 pt-2">
                <div class="max-w-4xl mx-auto px-4">
                    <div class="relative w-full input-glow transition-all duration-300 rounded-full bg-white dark:bg-dark-card border border-slate-200/50 dark:border-white/10 shadow-xl shadow-black/5">
                        <input type="text" id="question-input" 
                            class="w-full bg-transparent border-none focus:ring-0 px-6 md:px-8 py-4 md:py-5 text-sm md:text-base text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 dark:placeholder-slate-500 outline-none pr-16 md:pr-20 text-right"
                            placeholder="اكتب سؤالك القانوني هنا... (مثال: ما هي شروط تملك العقار؟)"
                            onkeypress="if(event.key === 'Enter') submitQuestion()">
                        
                        <button onclick="submitQuestion()" id="btn-send" class="absolute right-2 md:right-3 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-brand-green to-brand-teal text-white rounded-full flex items-center justify-center hover:scale-105 transition shadow-lg shadow-brand-green/20 cursor-pointer z-50">
                            <i class="fa-solid fa-paper-plane text-base md:text-lg rtl:-scale-x-100"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script logic -->
    <script>
        let currentConversationUuid = null;
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        let remainingMessagesCount = 10;

        // دالة لعرض المصادر مقسمة: أحكام + مواد نظامية، كل مجموعة بزرار عرض المزيد / عرض أقل
        function renderCitations(citations, msgId) {
            const judgments = citations.filter(c => c.type === 'judgment');
            const articles = citations.filter(c => c.type === 'article');

            function renderGroup(items, groupId, label, icon, isJudgment) {
                if (items.length === 0) return '';
                
                const initialVisibleCount = 2;
                const showMoreBtn = items.length > initialVisibleCount;
                
                let visibleHtml = '';
                let hiddenHtml = '';
                
                items.forEach((item, index) => {
                    const title = item.title || 'مرجع قانوني';
                    const text = item.text || '';
                    const confidence = item.score ? Math.round(item.score * 100) : null;
                    const confidenceBadge = confidence ? `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-white/5 shrink-0 select-none">${confidence}% تطابق</span>` : '';
                    
                    let titleHtml = '';
                    if (isJudgment) {
                        const systemInfo = item.system ? `<span class="text-[10px] font-black text-brand-green dark:text-brand-teal tracking-wide leading-tight">${item.system}</span>` : '';
                        const articleInfo = item.article_number ? `<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-0.5">${item.article_number}</span>` : '';
                        titleHtml = `
                            <div class="flex flex-col text-right">
                                <span class="text-xs font-black text-slate-800 dark:text-slate-100">قضية رقم: ${title}</span>
                                ${systemInfo}
                                ${articleInfo}
                            </div>
                        `;
                    } else if (!isJudgment && (title.includes(' - ') || title.includes('-'))) {
                        const separator = title.includes(' - ') ? ' - ' : '-';
                        const parts = title.split(separator);
                        const articleNum = parts[0].trim();
                        const systemName = parts[1].trim();
                        titleHtml = `
                            <div class="flex flex-col text-right">
                                <span class="text-[10px] font-black text-brand-green dark:text-brand-teal tracking-wide leading-tight">${systemName}</span>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-100 mt-0.5">${articleNum}</span>
                            </div>
                        `;
                    } else {
                        titleHtml = `<span class="text-xs font-black text-slate-700 dark:text-slate-200 leading-snug">${title}</span>`;
                    }

                    const typeBadge = isJudgment 
                        ? `<span class="text-[9px] font-bold px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-455 border border-indigo-100 dark:border-indigo-900/30 flex items-center gap-1 shrink-0"><i class="fa-solid fa-gavel text-[8px]"></i> حكم قضائي</span>`
                        : `<span class="text-[9px] font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-450 border border-emerald-100 dark:border-emerald-900/30 flex items-center gap-1 shrink-0"><i class="fa-solid fa-scroll text-[8px]"></i> مادة نظامية</span>`;

                    const borderClass = isJudgment
                        ? 'border-r-4 border-r-indigo-500'
                        : 'border-r-4 border-r-brand-green';

                    const cardHtml = `
                        <div class="p-4 rounded-2xl bg-white dark:bg-dark-card border border-slate-200/50 dark:border-white/10 hover:shadow-md hover:border-slate-350 dark:hover:border-slate-600 transition flex flex-col gap-2.5 relative ${borderClass}">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 dark:border-white/5 pb-2.5">
                                ${titleHtml}
                                <div class="flex flex-col items-end gap-1.5 shrink-0">
                                    ${typeBadge}
                                    ${confidenceBadge}
                                </div>
                            </div>
                            <p class="text-xs text-slate-800 dark:text-slate-100 leading-relaxed text-right font-medium max-h-24 overflow-y-auto custom-scrollbar pr-1.5">${text}</p>
                        </div>
                    `;
                    
                    if (index < initialVisibleCount) {
                        visibleHtml += cardHtml;
                    } else {
                        hiddenHtml += cardHtml;
                    }
                });

                let actionsHtml = '';
                if (showMoreBtn) {
                    actionsHtml = `
                        <div class="col-span-1 md:col-span-2 flex justify-center mt-3 relative z-10">
                            <button id="btn-more-${groupId}" onclick="toggleCitationGroup('${groupId}', true)" class="py-2 px-5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-full text-xs font-black text-slate-600 dark:text-slate-400 hover:text-brand-green dark:hover:text-brand-teal hover:border-brand-green/30 transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                                <i class="fa-solid fa-angle-down text-[10px]"></i> عرض المزيد من المصادر (${items.length - initialVisibleCount})
                            </button>
                            <button id="btn-less-${groupId}" onclick="toggleCitationGroup('${groupId}', false)" class="py-2 px-5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-full text-xs font-black text-slate-600 dark:text-slate-400 hover:text-brand-green dark:hover:text-brand-teal hover:border-brand-green/30 transition flex items-center gap-1.5 shadow-sm cursor-pointer hidden">
                                <i class="fa-solid fa-angle-up text-[10px]"></i> عرض أقل
                            </button>
                        </div>
                    `;
                }

                const labelColorClass = isJudgment 
                    ? 'text-indigo-600 dark:text-indigo-400 font-extrabold' 
                    : 'text-brand-green dark:text-brand-teal font-extrabold';
                
                const iconColorClass = isJudgment
                    ? 'text-indigo-500/70 dark:text-indigo-500/50'
                    : 'text-brand-green/70 dark:text-brand-teal/50';

                return `
                    <div class="mt-6 relative z-10">
                        <div class="flex items-center gap-2 mb-3 justify-end pb-1 border-b border-slate-100 dark:border-white/5">
                            <span class="text-xs tracking-wider ${labelColorClass}">${label}</span>
                            <i class="fa-solid ${icon} ${iconColorClass} text-sm"></i>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            ${visibleHtml}
                        </div>
                        
                        <div id="more-${groupId}" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 hidden">
                            ${hiddenHtml}
                        </div>
                        
                        ${actionsHtml}
                    </div>
                `;
            }

            const judgmentsHtml = renderGroup(judgments, `j-${msgId}`, 'السوابق والأحكام القضائية', 'fa-gavel', true);
            const articlesHtml = renderGroup(articles, `a-${msgId}`, 'النصوص النظامية والمواد القانونية', 'fa-book-open', false);

            return `
                <div class="mt-8 pt-5 border-t border-slate-200/50 dark:border-white/10 relative z-10">
                    ${judgmentsHtml}
                    ${articlesHtml}
                </div>
            `;
        }

        function toggleCitationGroup(groupId, expand) {
            const hiddenDiv = document.getElementById(`more-${groupId}`);
            const btnMore = document.getElementById(`btn-more-${groupId}`);
            const btnLess = document.getElementById(`btn-less-${groupId}`);
            if (expand) {
                if (hiddenDiv) hiddenDiv.classList.remove('hidden');
                if (btnMore) btnMore.classList.add('hidden');
                if (btnLess) btnLess.classList.remove('hidden');
            } else {
                if (hiddenDiv) hiddenDiv.classList.add('hidden');
                if (btnMore) btnMore.classList.remove('hidden');
                if (btnLess) btnLess.classList.add('hidden');
            }
        }

        // دالة لعرض شارة مؤشر الثقة بتنسيق جميل
        function renderConfidenceBadge(score) {
            let colorClass = 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-450';
            let dotClass = 'bg-emerald-500';
            
            if (score < 75) {
                colorClass = 'bg-rose-50 dark:bg-rose-950/30 border-rose-100 dark:border-rose-500/20 text-rose-700 dark:text-rose-450';
                dotClass = 'bg-rose-500';
            } else if (score < 90) {
                colorClass = 'bg-amber-50 dark:bg-amber-950/30 border-amber-100 dark:border-amber-500/20 text-amber-700 dark:text-amber-450';
                dotClass = 'bg-amber-500';
            }
            
            return `
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-[11px] font-black tracking-tight shrink-0 select-none shadow-sm ${colorClass}">
                    <span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span>
                    <span>نسبة مطابقة: ${score}%</span>
                </div>
            `;
        }

        let userReferralLink = '';

        function updateUsageUi(usage) {
            const widget = document.getElementById('usage-limit-widget');
            const labelSpan = document.getElementById('usage-limit-label');
            const ratioSpan = document.getElementById('usage-limit-ratio');
            const bar = document.getElementById('usage-limit-bar');
            const actionDiv = document.getElementById('usage-limit-action');
            
            userReferralLink = usage.referral_link || '';
            
            widget.classList.remove('hidden');
            const remaining = Math.max(0, usage.limit - usage.count);
            remainingMessagesCount = remaining;
            labelSpan.textContent = 'الرسائل المتبقية:';
            ratioSpan.textContent = `${remaining} رسالة`;
            
            const percentage = Math.min(100, (usage.count / usage.limit) * 100);
            bar.style.width = `${percentage}%`;
            
            // تلوين البار حسب الاستهلاك
            if (percentage >= 90) {
                bar.className = "bg-gradient-to-r from-rose-500 to-orange-500 h-full rounded-full transition-all duration-300";
            } else if (percentage >= 70) {
                bar.className = "bg-gradient-to-r from-amber-500 to-yellow-500 h-full rounded-full transition-all duration-300";
            } else {
                bar.className = "bg-gradient-to-r from-brand-green to-brand-teal h-full rounded-full transition-all duration-300";
            }
            
            // تعيين الرابط أو الزر
            if (!usage.is_logged_in) {
                actionDiv.innerHTML = `
                    <a href="/register/company" class="text-[10px] font-black text-brand-green hover:underline flex items-center justify-center gap-1">
                        <i class="fa-solid fa-user-plus"></i> سجل مجاناً لفتح 10 رسائل إضافية!
                    </a>
                `;
            } else {
                actionDiv.innerHTML = `
                    <button onclick="copyReferralLink()" class="text-[10px] font-black text-brand-teal hover:underline flex items-center justify-center gap-1 mx-auto cursor-pointer border-none bg-transparent">
                        <i class="fa-solid fa-share-nodes"></i> انسخ رابط الدعوة لفتح 20 رسالة إضافية!
                    </button>
                `;
            }
            
            // إظهار المودال مباشرة إذا انتهى الرصيد
            if (usage.count >= usage.limit) {
                showLimitModal(usage.is_logged_in);
            }
        }

        function showLimitModal(isLoggedIn) {
            const modal = document.getElementById('limit-modal');
            const box = document.getElementById('limit-modal-box');
            const title = document.getElementById('limit-modal-title');
            const desc = document.getElementById('limit-modal-description');
            const actions = document.getElementById('limit-modal-actions');
            
            modal.classList.remove('hidden');
            setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 50);
            
            if (!isLoggedIn) {
                title.textContent = 'لقد استنفدت الرسائل التجريبية!';
                desc.textContent = 'لقد استخدمت الحد الأقصى المتاح للزوار وهو 10 رسائل. للتكملة والحصول على 10 رسائل إضافية، يرجى تسجيل بياناتك مجاناً.';
                actions.innerHTML = `
                    <a href="/register/company" class="py-3 px-4 bg-gradient-to-br from-brand-green to-brand-green-dim text-white rounded-2xl font-bold shadow-lg shadow-brand-green/20 active:scale-[0.98] transition-all block">
                        تسجيل حساب جديد مجاناً
                    </a>
                    <a href="/login" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 mt-2 block">
                        لديك حساب بالفعل؟ تسجيل الدخول
                    </a>
                `;
            } else {
                title.textContent = 'لقد نفدت رسائلك المجانية للأعضاء!';
                desc.textContent = 'لقد استخدمت 20 رسالة مجانية. لفتح 20 رسالة إضافية ومواصلة استشاراتك القانونية، يرجى نسخ رابط الدعوة وإرساله لصديق للتسجيل في منصتنا.';
                actions.innerHTML = `
                    <button onclick="copyReferralLink()" class="w-full py-3.5 px-4 bg-gradient-to-br from-brand-green to-brand-teal text-white rounded-2xl font-bold shadow-lg shadow-brand-green/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer border-none">
                        <i class="fa-solid fa-copy"></i> نسخ رابط الدعوة الخاص بك
                    </button>
                `;
            }
        }

        function closeLimitModal() {
            const modal = document.getElementById('limit-modal');
            const box = document.getElementById('limit-modal-box');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function copyReferralLink() {
            const link = userReferralLink || (window.location.origin + '/register/company');
            navigator.clipboard.writeText(link).then(() => {
                alert('تم نسخ رابط الدعوة الخاص بك بنجاح! شاركه مع صديق لفتح 20 رسالة إضافية فور تسجيله.');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

        function getLocalGuestConversations() {
            try {
                const data = localStorage.getItem('guest_ai_conversations');
                return data ? JSON.parse(data) : [];
            } catch (e) {
                return [];
            }
        }

        function saveGuestConversationUuid(uuid) {
            try {
                const uuids = getLocalGuestConversations();
                if (!uuids.includes(uuid)) {
                    uuids.push(uuid);
                    localStorage.setItem('guest_ai_conversations', JSON.stringify(uuids));
                }
            } catch (e) {
                console.error(e);
            }
        }

        function removeLocalGuestConversation(uuid) {
            try {
                let uuids = getLocalGuestConversations();
                uuids = uuids.filter(id => id !== uuid);
                localStorage.setItem('guest_ai_conversations', JSON.stringify(uuids));
            } catch (e) {
                console.error(e);
            }
        }

        // عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            loadConversations();
            
            // تهيئة السايدبار وحالة الخلفية المظللة حسب حجم الشاشة
            const sidebar = document.getElementById('chat-sidebar');
            const mainContent = document.getElementById('main-content');
            if (window.innerWidth < 768) {
                sidebar.classList.add('translate-x-full');
                sidebar.classList.remove('translate-x-0');
                mainContent.classList.remove('md:mr-80');
            } else {
                sidebar.classList.remove('translate-x-full');
                sidebar.classList.add('translate-x-0');
                mainContent.classList.add('md:mr-80');
            }
        });

        // إغلاق وفتح المنيو الجانبي في الموبايل والديسك توب
        function toggleSidebar() {
            const sidebar = document.getElementById('chat-sidebar');
            const mainContent = document.getElementById('main-content');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            if (sidebar.classList.contains('translate-x-0')) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('translate-x-full');
                mainContent.classList.remove('md:mr-80');
                if (backdrop) backdrop.classList.add('hidden');
            } else {
                sidebar.classList.remove('translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (window.innerWidth >= 768) {
                    mainContent.classList.add('md:mr-80');
                } else {
                    if (backdrop) backdrop.classList.remove('hidden');
                }
            }
        }

        // وضع نص السؤال المقترح في الخانة
        function setQuery(text) {
            document.getElementById('question-input').value = text;
            submitQuestion();
        }

        // جلب المحادثات السابقة
        async function loadConversations() {
            const listContainer = document.getElementById('conversations-list');
            try {
                let url = '/legal-assistant/conversations';
                if (!isLoggedIn) {
                    const guestUuids = getLocalGuestConversations();
                    url = `/legal-assistant/conversations?guest_uuids=${guestUuids.join(',')}`;
                }

                const response = await fetch(url);
                const data = await response.json();
                const conversations = data.conversations || [];
                const usage = data.usage;
                
                // تحديث الـ Widget بناءً على الاستهلاك الحالي المرسل من السيرفر
                if (usage) {
                    updateUsageUi(usage);
                }

                if (conversations.length === 0) {
                    listContainer.innerHTML = `<span class="text-xs text-slate-400 dark:text-slate-500 font-bold text-center mt-8 block">لا توجد محادثات سابقة</span>`;
                    return;
                }

                let html = '';
                conversations.forEach(c => {
                    const activeClass = c.uuid === currentConversationUuid ? 'bg-white/85 dark:bg-white/10 border-brand-green/30 dark:border-brand-green/45 shadow-sm text-brand-green dark:text-brand-teal font-bold' : 'hover:bg-white/40 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300 border-transparent';
                    html += `
                        <div class="group flex items-center justify-between p-3 rounded-xl border transition-all duration-200 cursor-pointer ${activeClass}" onclick="loadConversation('${c.uuid}')">
                            <div class="flex items-center gap-2.5 overflow-hidden flex-1">
                                <i class="fa-regular fa-message text-sm text-slate-400 dark:text-slate-500 shrink-0"></i>
                                <span class="text-xs truncate text-right flex-1">${c.title}</span>
                            </div>
                            <button onclick="deleteConversation('${c.uuid}', event)" class="opacity-0 group-hover:opacity-100 w-6 h-6 rounded-md hover:bg-red-50 dark:hover:bg-red-950/30 hover:text-red-600 dark:hover:text-red-400 text-slate-400 dark:text-slate-500 flex items-center justify-center transition-all">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    `;
                });
                listContainer.innerHTML = html;

            } catch (error) {
                console.error("Failed to load conversations:", error);
                listContainer.innerHTML = `<span class="text-xs text-red-500 font-bold text-center">فشل تحميل السجل</span>`;
            }
        }

        // حذف محادثة
        async function deleteConversation(uuid, event) {
            event.stopPropagation();
            if(!confirm('هل أنت متأكد من رغبتك في حذف هذه المحادثة؟')) return;

            try {
                const response = await fetch(`/legal-assistant/conversations/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    if (!isLoggedIn) {
                        removeLocalGuestConversation(uuid);
                    }
                    if (currentConversationUuid === uuid) {
                        startNewChat();
                    } else {
                        loadConversations();
                    }
                }
            } catch (error) {
                console.error(error);
                alert('فشل في حذف المحادثة');
            }
        }

        // بدء محادثة جديدة وتصفير الشاشة
        function startNewChat() {
            currentConversationUuid = null;
            document.getElementById('chat-messages').innerHTML = '';
            document.getElementById('welcome-visuals').style.display = 'flex';
            loadConversations();
            
            // إغلاق في الموبايل
            if (window.innerWidth < 768) {
                document.getElementById('chat-sidebar').classList.add('translate-x-full');
                document.getElementById('chat-sidebar').classList.remove('translate-x-0');
                document.getElementById('main-content').classList.remove('md:mr-80');
                document.getElementById('sidebar-backdrop')?.classList.add('hidden');
            }
        }

        // تحميل رسائل محادثة معينة عند النقر عليها
        async function loadConversation(uuid) {
            currentConversationUuid = uuid;
            const chatMessages = document.getElementById('chat-messages');
            const mainContainer = document.getElementById('chat-container');
            
            // إخفاء الواجهة الترحيبية
            document.getElementById('welcome-visuals').style.display = 'none';

            // عرض تأثير التحميل (Skeleton)
            chatMessages.innerHTML = `
                <div class="animate-pulse flex flex-col gap-6 w-full max-w-4xl mx-auto p-4">
                    <div class="h-10 bg-white/80 dark:bg-slate-800/80 rounded-2xl w-2/3 self-start shadow-sm"></div>
                    <div class="h-28 bg-white/80 dark:bg-slate-800/80 rounded-2xl w-full self-end shadow-sm"></div>
                </div>
            `;

            // تحديث حالة المحادثة النشطة في القائمة
            loadConversations();

            try {
                const response = await fetch(`/legal-assistant/conversations/${uuid}`);
                if (!response.ok) {
                    throw new Error("HTTP status " + response.status);
                }
                const data = await response.json();
                
                chatMessages.innerHTML = '';
                
                data.messages.forEach(m => {
                    if (m.role === 'user') {
                        const userHtml = `
                            <div class="flex justify-start mb-8">
                                <div class="bg-gradient-to-br from-brand-primary to-brand-secondary text-white rounded-3xl rounded-tr-none px-4 md:px-6 py-3 md:py-4 shadow-xl shadow-indigo-950/20 max-w-[90%] md:max-w-[85%] border border-brand-primary/20 dark:border-brand-primary/10">
                                    <p class="text-sm md:text-base font-bold leading-relaxed">${m.message}</p>
                                </div>
                            </div>
                        `;
                        chatMessages.insertAdjacentHTML('beforeend', userHtml);
                    } else {
                        // إعداد المراجع ومؤشر الثقة
                        let citationsContainer = '';
                        let confidenceScore = 95;
                        let citations = m.citations;

                        if (citations) {
                            if (citations.confidence_score !== undefined) {
                                confidenceScore = citations.confidence_score;
                                citations = citations.items || [];
                            } else if (!Array.isArray(citations) && citations.items !== undefined) {
                                confidenceScore = citations.confidence_score || 95;
                                citations = citations.items || [];
                            }
                            if (Array.isArray(citations) && citations.length > 0) {
                                const msgId = 'msg-' + Math.random().toString(36).substr(2, 9);
                                citationsContainer = renderCitations(citations, msgId);
                            }
                        }

                        const formattedAnswer = m.message ? m.message.replace(/\n/g, '<br>') : '';
                        const confidenceHtml = renderConfidenceBadge(confidenceScore);
                        
                        const aiHtml = `
                            <div class="flex justify-end mb-8">
                                <div class="bg-white/95 dark:bg-dark-card/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/5 border border-slate-200/50 dark:border-white/5 px-4 md:px-8 py-5 md:py-7 w-full md:max-w-[95%] rounded-3xl rounded-tl-none relative overflow-hidden">
                                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-brand-green/10 rounded-full blur-3xl"></div>
                                    
                                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 dark:border-white/5 pb-4 relative z-10">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ asset('images/icon.png') }}"
                                                onerror="this.src='https://placehold.co/40x40/0d9488/0b1120?text=R'"
                                                alt="Radiif Logo"
                                                class="w-10 h-10 rounded-xl shadow-sm object-cover ring-2 ring-brand-green/20">
                                            <span class="text-sm font-black text-transparent bg-clip-text bg-gradient-to-r from-brand-green to-brand-primary dark:to-brand-secondary">المستشار القانوني الذكي</span>
                                        </div>
                                        ${confidenceHtml}
                                    </div>
                                    
                                    <div class="prose prose-teal max-w-none text-slate-800 dark:text-slate-100 leading-loose font-medium text-right text-sm relative z-10">
                                        ${formattedAnswer}
                                    </div>
                                    ${citationsContainer}
                                </div>
                            </div>
                        `;
                        chatMessages.insertAdjacentHTML('beforeend', aiHtml);
                    }
                });

                mainContainer.scrollTop = mainContainer.scrollHeight;

                // إغلاق في الموبايل
                if (window.innerWidth < 768) {
                    document.getElementById('chat-sidebar').classList.add('translate-x-full');
                    document.getElementById('chat-sidebar').classList.remove('translate-x-0');
                    document.getElementById('main-content').classList.remove('md:mr-80');
                    document.getElementById('sidebar-backdrop')?.classList.add('hidden');
                }

            } catch (error) {
                console.error(error);
                chatMessages.innerHTML = `<span class="text-xs text-red-500 font-bold text-center mt-8 block">حدث خطأ أثناء تحميل الرسائل</span>`;
            }
        }

        // إرسال السؤال الجديد
        async function submitQuestion() {
            const input = document.getElementById('question-input');
            const question = input.value.trim();
            if(!question) return;

            // منع الإرسال محلياً إذا تم تخطي الحد
            if (remainingMessagesCount <= 0) {
                showLimitModal(isLoggedIn);
                return;
            }

            const chatMessages = document.getElementById('chat-messages');
            const mainContainer = document.getElementById('chat-container');

            // إخفاء المحتويات الترحيبية
            const visuals = document.getElementById('welcome-visuals');
            if(visuals) visuals.style.display = 'none';

            // إضافة رسالة المستخدم للشاشة
            const userMsgHtml = `
                <div class="flex justify-start mb-8">
                    <div class="bg-gradient-to-br from-brand-primary to-brand-secondary text-white rounded-3xl rounded-tr-none px-4 md:px-6 py-3 md:py-4 shadow-xl shadow-indigo-950/20 max-w-[90%] md:max-w-[85%] border border-brand-primary/20 dark:border-brand-primary/10">
                        <p class="text-sm md:text-base font-bold leading-relaxed">${question}</p>
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', userMsgHtml);
            
            const loadingId = 'loading-' + Date.now();
            const loadingHtml = `
                <div id="${loadingId}" class="flex justify-end mb-8">
                    <div class="bg-white/95 dark:bg-dark-card/95 backdrop-blur-2xl shadow-xl border border-slate-200/50 dark:border-white/5 rounded-3xl rounded-tl-none px-4 md:px-6 py-3 md:py-4 flex items-center gap-3">
                        <div class="w-2 h-2 bg-brand-green rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-brand-teal rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 ml-2">جاري استخراج السوابق والأنظمة القانونية...</span>
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', loadingHtml);

            input.value = '';
            document.getElementById('btn-send').disabled = true;
            document.getElementById('btn-send').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            mainContainer.scrollTop = mainContainer.scrollHeight;

            try {
                const response = await fetch('/legal-assistant/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        question: question,
                        conversation_uuid: currentConversationUuid
                    })
                });

                if (!response.ok) {
                    if (response.status === 403) {
                        try {
                            const errData = await response.json();
                            if (errData.error === 'limit_reached') {
                                document.getElementById(loadingId)?.remove();
                                // إزالة رسالة المستخدم الأخيرة من الشاشة لعدم اكتمال الإرسال
                                const lastUserMsg = chatMessages.lastElementChild;
                                if (lastUserMsg) lastUserMsg.remove();
                                
                                // إرجاع السؤال المكتوب وإعادة تفعيل زر الإرسال
                                input.value = question;
                                document.getElementById('btn-send').disabled = false;
                                document.getElementById('btn-send').innerHTML = '<i class="fa-solid fa-paper-plane text-lg rtl:-scale-x-100"></i>';
                                
                                showLimitModal(isLoggedIn);
                                return;
                            }
                        } catch(e) {}
                    }
                    throw new Error("HTTP status " + response.status);
                }

                const data = await response.json();
                if (data.usage) {
                    updateUsageUi(data.usage);
                }
                if (!data || !data.answer) {
                    throw new Error("Empty answer from API");
                }
                
                document.getElementById(loadingId).remove();

                // تحديث الـ UUID للمحادثة الحالية إذا كانت جديدة
                if (!currentConversationUuid && data.conversation_uuid) {
                    currentConversationUuid = data.conversation_uuid;
                    if (!isLoggedIn) {
                        saveGuestConversationUuid(data.conversation_uuid);
                    }
                }

                let citationsContainer = '';
                let confidenceScore = 95;
                let citations = data.citations;

                if (citations) {
                    if (citations.confidence_score !== undefined) {
                        confidenceScore = citations.confidence_score;
                        citations = citations.items || [];
                    }
                    if (Array.isArray(citations) && citations.length > 0) {
                        const msgId = 'msg-' + Math.random().toString(36).substr(2, 9);
                        citationsContainer = renderCitations(citations, msgId);
                    }
                }

                const formattedAnswer = data.answer ? data.answer.replace(/\n/g, '<br>') : '';
                const confidenceHtml = renderConfidenceBadge(confidenceScore);
                
                const aiMsgHtml = `
                    <div class="flex justify-end mb-8">
                        <div class="bg-white/95 dark:bg-dark-card/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/5 border border-slate-200/50 dark:border-white/5 px-4 md:px-8 py-5 md:py-7 w-full md:max-w-[95%] rounded-3xl rounded-tl-none relative overflow-hidden">
                            <div class="absolute -top-10 -left-10 w-40 h-40 bg-brand-green/10 rounded-full blur-3xl"></div>
                            
                            <div class="flex items-center justify-between mb-6 border-b border-slate-100 dark:border-white/5 pb-4 relative z-10">
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('images/icon.png') }}"
                                        onerror="this.src='https://placehold.co/40x40/0d9488/0b1120?text=R'"
                                        alt="Radiif Logo"
                                        class="w-10 h-10 rounded-xl shadow-sm object-cover ring-2 ring-brand-green/20">
                                    <span class="text-sm font-black text-transparent bg-clip-text bg-gradient-to-r from-brand-green to-brand-primary dark:to-brand-secondary">المستشار القانوني الذكي</span>
                                </div>
                                ${confidenceHtml}
                            </div>
                            
                            <div class="prose prose-teal max-w-none text-slate-800 dark:text-slate-100 leading-loose font-medium text-right text-sm relative z-10">
                                ${formattedAnswer}
                            </div>
                            ${citationsContainer}
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', aiMsgHtml);
                
                // إعادة تحميل قائمة المحادثات لتحديث العنوان
                loadConversations();

            } catch (error) {
                console.error(error);
                document.getElementById(loadingId)?.remove();
                chatMessages.insertAdjacentHTML('beforeend', `
                    <div class="flex justify-end mb-8">
                        <div class="bg-white/95 dark:bg-dark-card/95 backdrop-blur-2xl px-6 py-4 rounded-3xl rounded-tl-none border border-red-100 dark:border-rose-950/20 shadow-sm">
                            <span class="text-rose-500 dark:text-rose-450 font-bold">حدث خطأ تقني، يرجى المحاولة مرة أخرى.</span>
                        </div>
                    </div>
                `);
            } finally {
                document.getElementById('btn-send').disabled = false;
                document.getElementById('btn-send').innerHTML = '<i class="fa-solid fa-paper-plane text-lg rtl:-scale-x-100"></i>';
                mainContainer.scrollTop = mainContainer.scrollHeight;
            }
        }
    </script>

    <!-- Limit Reached Modal (شاشة حظر تخطي الحد المجاني) -->
    <div id="limit-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="bg-white/95 dark:bg-dark-card/95 rounded-3xl p-8 max-w-md w-full shadow-2xl border border-slate-200/50 dark:border-white/10 text-center transform transition-all duration-300 scale-95 opacity-0 relative" id="limit-modal-box">
            <!-- Close Button -->
            <button onclick="closeLimitModal()" class="absolute left-4 top-4 w-8 h-8 rounded-full flex items-center justify-center bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-400 dark:text-slate-500 hover:text-slate-650 dark:hover:text-slate-350 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-green to-brand-green-dim text-white flex items-center justify-center text-2xl shadow-lg shadow-brand-green/20 mx-auto mb-5">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-2" id="limit-modal-title">لقد نفدت رسائلك المجانية!</h3>
            <p class="text-sm text-slate-650 dark:text-slate-350 leading-relaxed mb-6 font-semibold" id="limit-modal-description">
                يرجى التسجيل لفتح 10 رسائل إضافية مجاناً ومتابعة استشارتك القانونية.
            </p>
            <div class="flex flex-col gap-3" id="limit-modal-actions">
                <!-- Action buttons -->
            </div>
        </div>
    </div>

</body>
</html>
