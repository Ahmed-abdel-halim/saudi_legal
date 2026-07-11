<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المساعد القانوني الذكي | رديف</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        
        /* Premium Background Image */
        .premium-bg {
            background-image: url('/images/backgroundchat.png');
            background-size: 100% 100%;
            background-position: top center;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
            background-color: #f0f7f9;
        }

        /* Glassmorphism Classes */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-left: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.03);
        }

        .glass-bubble {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border-radius: 2rem 2rem 0 2rem;
        }

        /* Gradient Text */
        .text-gradient {
            background: linear-gradient(to left, #0ea5e9, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Input Glow */
        .input-glow:focus-within {
            box-shadow: 0 0 30px rgba(14, 165, 233, 0.2);
            border-color: rgba(14, 165, 233, 0.5);
        }

        /* Scrollbar styles */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-[#f0f7f9] h-screen overflow-hidden flex font-sans antialiased text-gray-800">

    <!-- Sidebar (سجل المحادثات الجانبي) -->
    <aside id="chat-sidebar" class="w-80 h-full glass-sidebar flex flex-col z-30 transition-all duration-300 fixed right-0 top-0 transform translate-x-0">
        <!-- Sidebar Header: New Chat Button -->
        <div class="p-4 border-b border-white/60">
            <button onclick="startNewChat()" class="w-full py-3.5 px-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] transition-all cursor-pointer font-bold text-sm shadow-md">
                <i class="fa-solid fa-plus text-xs"></i>
                محادثة جديدة
            </button>
        </div>

        <!-- Sidebar Content: Chat List -->
        <div class="flex-1 overflow-y-auto px-3 py-4 custom-scrollbar flex flex-col gap-2" id="conversations-list">
            <!-- Loading Skeleton -->
            <div class="animate-pulse flex flex-col gap-3 p-2">
                <div class="h-10 bg-gray-200/60 rounded-xl"></div>
                <div class="h-10 bg-gray-200/60 rounded-xl"></div>
                <div class="h-10 bg-gray-200/60 rounded-xl"></div>
            </div>
        </div>

        <!-- Sidebar Footer: Logged User info -->
        <div class="p-4 border-t border-white/60 bg-white/20 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold shadow-sm">
                    @auth
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    @else
                        ز
                    @endauth
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black text-gray-700">
                        @auth
                            {{ auth()->user()->name }}
                        @else
                            زائر الخدمة
                        @endauth
                    </span>
                    <span class="text-[9px] font-bold text-gray-400">الوصول المجاني</span>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden w-8 h-8 rounded-lg bg-gray-200/60 text-gray-600 flex items-center justify-center text-xs hover:bg-gray-200">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </aside>

    <!-- Main Chat Window (النافذة الرئيسية) -->
    <div id="main-content" class="flex-1 h-full flex flex-col relative overflow-hidden transition-all duration-300 md:mr-80">
        
        <!-- Navbar -->
        <nav class="relative z-10 glass-panel border-b border-white/60 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                        <i class="fa-solid fa-scale-balanced text-lg animate-pulse"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-black text-gray-800 tracking-tight">رديف القانوني</h1>
                        <p class="text-[10px] font-bold text-teal-600 tracking-wider flex items-center gap-1 uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-ping"></span>
                            ACTIVE VECTOR ENGINE | 53,765 POINTS
                        </p>
                    </div>
                </div>
            </div>

            <!-- Title in center -->
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 hidden lg:block">
                <h2 class="text-2xl font-black text-gradient tracking-tight">المستشار القضائي والنظامي الذكي</h2>
            </div>

            <!-- Exit button -->
            <a href="{{ url('/') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 transition shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>

        <!-- Chat Container -->
        <div class="premium-bg flex-1 w-full flex flex-col relative overflow-hidden">
            <main id="chat-container" class="flex-1 relative z-10 flex flex-col max-w-7xl mx-auto w-full px-4 pt-10 pb-4 overflow-y-auto custom-scrollbar">
                
                <!-- Welcome visuals / screen -->
                <div class="flex-1 flex flex-col items-center justify-center relative mb-12" id="welcome-visuals">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl shadow-xl shadow-blue-500/20 mb-6">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="glass-bubble p-8 max-w-2xl text-center relative border border-white/80">
                        <h2 class="text-xl font-black text-gray-800 mb-3">مرحباً بك في مستشارك القانوني الذكي!</h2>
                        <p class="text-sm text-gray-600 leading-relaxed font-medium">
                            أنا هنا لمساعدتك في استخراج السوابق والأحكام القضائية، وقراءة نصوص الأنظمة والتشريعات السعودية المترابطة بالمعنى الدلالي. محادثتي الآن مزودة بالذاكرة الكاملة لحفظ سياق حديثك.
                        </p>
                    </div>
                </div>

                <!-- Suggested queries -->
                <div class="flex flex-col gap-3 w-full max-w-4xl mx-auto" id="suggested-queries">
                    <h3 class="text-sm font-bold text-gray-500 text-right px-2">أسئلة مقترحة للبدء:</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" dir="rtl">
                        <button onclick="setQuery('ما هي شروط تملك الأجانب للعقار في السعودية؟')" class="glass-panel rounded-2xl p-4 flex items-center gap-4 hover:bg-white/80 transition group text-right cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-house-user"></i></div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-800 text-sm group-hover:text-teal-600 transition">تملك العقارات</h4>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">شروط تملك الأجانب للعقار</p>
                            </div>
                        </button>
                        <button onclick="setQuery('ما هي عقوبة الموظف العام في حال ثبوت جريمة الرشوة؟')" class="glass-panel rounded-2xl p-4 flex items-center gap-4 hover:bg-white/80 transition group text-right cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-gavel"></i></div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-800 text-sm group-hover:text-blue-600 transition">الجرائم الجنائية</h4>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">عقوبة جريمة الرشوة للموظف</p>
                            </div>
                        </button>
                        <button onclick="setQuery('متى يسقط حق الزوجة في المطالبة بالنفقة؟')" class="glass-panel rounded-2xl p-4 flex items-center gap-4 hover:bg-white/80 transition group text-right cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-scale-balanced"></i></div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-800 text-sm group-hover:text-emerald-600 transition">الأحوال الشخصية</h4>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">حالات سقوط النفقة عن الزوجة</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Chat messages log -->
                <div id="chat-messages" class="flex flex-col w-full max-w-4xl mx-auto mt-6 px-2 pb-4">
                </div>
                
            </main>

            <!-- Chat input area -->
            <div class="w-full relative z-20 pb-6 pt-2">
                <div class="max-w-4xl mx-auto px-4">
                    <div class="relative w-full input-glow transition-all duration-300 rounded-full bg-white border border-white shadow-xl shadow-blue-900/5">
                        <input type="text" id="question-input" 
                            class="w-full bg-transparent border-none focus:ring-0 px-8 py-5 text-gray-800 font-medium placeholder-gray-400 outline-none pr-20"
                            placeholder="اكتب سؤالك القانوني هنا... (مثال: ما هي شروط تملك العقار؟)"
                            onkeypress="if(event.key === 'Enter') submitQuestion()">
                        
                        <button onclick="submitQuestion()" id="btn-send" class="absolute right-3 top-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-full flex items-center justify-center hover:scale-105 transition shadow-lg shadow-blue-500/40 cursor-pointer z-50">
                            <i class="fa-solid fa-paper-plane text-lg rtl:-scale-x-100"></i>
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

        // دالة لعرض المصادر بطريقة ذكية (أول 4 ثم زرار عرض المزيد)
        function renderCitations(citations, msgId) {
            if (!citations || citations.length === 0) return '';
            
            const firstFour = citations.slice(0, 4);
            const remaining = citations.slice(4);
            
            const renderCard = (c, index) => {
                const isArticle = c.type === 'law_article' || c.type === 'article';
                const systemLine = (c.system && c.system.trim()) 
                    ? `<div class="flex items-center gap-1.5 mb-2">
                         <i class="fa-solid fa-landmark text-[10px] ${isArticle ? 'text-teal-500' : 'text-blue-500'}"></i>
                         <span class="text-[11px] font-black ${isArticle ? 'text-teal-700' : 'text-blue-700'}">${c.system}</span>
                       </div>` 
                    : '';
                return `
                <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200/60 rounded-2xl p-4 hover:shadow-md transition-all cursor-pointer group">
                    ${systemLine}
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg ${isArticle ? 'bg-teal-50 text-teal-600 group-hover:bg-teal-500' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-500'} flex items-center justify-center text-xs font-black group-hover:text-white transition-colors shrink-0">${index + 1}</div>
                        <h5 class="text-xs font-black text-gray-800 line-clamp-1 flex-1">${c.title}</h5>
                        <span class="text-[10px] font-bold ${isArticle ? 'text-teal-700 bg-teal-50 border-teal-100' : 'text-blue-700 bg-blue-50 border-blue-100'} border px-2 py-1 rounded-md shrink-0">${c.article || 'مرجع'}</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed font-medium max-h-60 overflow-y-auto custom-scrollbar pr-2 whitespace-pre-wrap">${c.text}</p>
                </div>
            `;};
            
            const firstFourHtml = firstFour.map((c, index) => renderCard(c, index)).join('');
            
            let remainingHtml = '';
            let moreBtnHtml = '';
            if (remaining.length > 0) {
                const remainingCards = remaining.map((c, index) => renderCard(c, index + 4)).join('');
                remainingHtml = `
                    <div id="more-citations-${msgId}" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 col-span-full">
                        ${remainingCards}
                    </div>
                `;
                
                moreBtnHtml = `
                    <div class="col-span-full flex justify-center mt-4" id="more-btn-wrapper-${msgId}">
                        <button onclick="showMoreCitations('${msgId}')" class="px-5 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 hover:from-blue-100 hover:to-indigo-100 text-xs font-black text-blue-700 rounded-xl transition-all flex items-center gap-2 active:scale-95 cursor-pointer shadow-sm">
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            <span>عرض المزيد من القضايا المرتبطة (${remaining.length})</span>
                        </button>
                    </div>
                `;
            }
            
            return `
                <div class="mt-8 pt-5 border-t border-gray-100 relative z-10">
                    <div class="flex items-center gap-2 mb-4 justify-end">
                        <span class="text-xs font-black text-gray-400 uppercase tracking-widest">المصادر القانونية المؤكدة</span>
                        <i class="fa-solid fa-book-open text-gray-300"></i>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${firstFourHtml}
                        ${remainingHtml}
                        ${moreBtnHtml}
                    </div>
                </div>
            `;
        }
        
        function showMoreCitations(msgId) {
            const hiddenDiv = document.getElementById(`more-citations-${msgId}`);
            const btnWrapper = document.getElementById(`more-btn-wrapper-${msgId}`);
            if (hiddenDiv) {
                hiddenDiv.classList.remove('hidden');
            }
            if (btnWrapper) {
                btnWrapper.remove();
            }
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
        });

        // إغلاق وفتح المنيو الجانبي في الموبايل والديسك توب
        function toggleSidebar() {
            const sidebar = document.getElementById('chat-sidebar');
            const mainContent = document.getElementById('main-content');
            if (sidebar.classList.contains('translate-x-0')) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('translate-x-full');
                mainContent.classList.remove('md:mr-80');
            } else {
                sidebar.classList.remove('translate-x-full');
                sidebar.classList.add('translate-x-0');
                mainContent.classList.add('md:mr-80');
            }
        }

        // وضع نص السؤال المقترح في الخانة
        function setQuery(text) {
            document.getElementById('question-input').value = text;
            submitQuestion();
        }

        // جلب سجل المحادثات السابقة
        async function loadConversations() {
            const listContainer = document.getElementById('conversations-list');
            try {
                let url = '/legal-assistant/conversations';
                if (!isLoggedIn) {
                    const localUuids = getLocalGuestConversations();
                    if (localUuids.length > 0) {
                        url += '?guest_uuids=' + encodeURIComponent(localUuids.join(','));
                    }
                }
                const response = await fetch(url);
                const conversations = await response.json();
                
                if (conversations.length === 0) {
                    listContainer.innerHTML = `
                        <div class="text-center py-8 text-xs font-bold text-gray-400">
                            <i class="fa-regular fa-comment-dots text-2xl mb-2 block"></i>
                            لا توجد محادثات سابقة
                        </div>
                    `;
                    return;
                }

                let html = '';
                conversations.forEach(c => {
                    const activeClass = c.uuid === currentConversationUuid ? 'bg-white/80 border-blue-200 shadow-sm text-blue-700 font-bold' : 'hover:bg-white/40 text-gray-700 border-transparent';
                    html += `
                        <div class="group flex items-center justify-between p-3 rounded-xl border transition-all duration-200 cursor-pointer ${activeClass}" onclick="loadConversation('${c.uuid}')">
                            <div class="flex items-center gap-2.5 overflow-hidden flex-1">
                                <i class="fa-regular fa-message text-sm text-gray-400 shrink-0"></i>
                                <span class="text-xs truncate text-right flex-1">${c.title}</span>
                            </div>
                            <button onclick="deleteConversation('${c.uuid}', event)" class="opacity-0 group-hover:opacity-100 w-6 h-6 rounded-md hover:bg-red-50 hover:text-red-600 text-gray-400 flex items-center justify-center transition-all">
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

        // بدء محادثة جديدة وتصفير الشاشة
        function startNewChat() {
            currentConversationUuid = null;
            document.getElementById('chat-messages').innerHTML = '';
            document.getElementById('welcome-visuals').style.display = 'flex';
            document.getElementById('suggested-queries').style.display = 'flex';
            loadConversations();
            
            // إغلاق في الموبايل
            if (window.innerWidth < 768) {
                document.getElementById('chat-sidebar').classList.add('translate-x-full');
                document.getElementById('chat-sidebar').classList.remove('translate-x-0');
                document.getElementById('main-content').classList.remove('md:mr-80');
            }
        }

        // تحميل رسائل محادثة معينة عند النقر عليها
        async function loadConversation(uuid) {
            currentConversationUuid = uuid;
            const chatMessages = document.getElementById('chat-messages');
            const mainContainer = document.getElementById('chat-container');
            
            // إخفاء الواجهة الترحيبية
            document.getElementById('welcome-visuals').style.display = 'none';
            document.getElementById('suggested-queries').style.display = 'none';

            // عرض تأثير التحميل (Skeleton)
            chatMessages.innerHTML = `
                <div class="animate-pulse flex flex-col gap-6 w-full max-w-4xl mx-auto p-4">
                    <div class="h-10 bg-white/80 rounded-2xl w-2/3 self-start shadow-sm"></div>
                    <div class="h-28 bg-white/80 rounded-2xl w-full self-end shadow-sm"></div>
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
                                <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-3xl rounded-tr-none px-6 py-4 shadow-xl shadow-blue-900/20 max-w-[85%] border border-blue-500/30">
                                    <p class="text-base font-bold leading-relaxed">${m.message}</p>
                                </div>
                            </div>
                        `;
                        chatMessages.insertAdjacentHTML('beforeend', userHtml);
                    } else {
                        // إعداد المراجع
                        let citationsContainer = '';
                        if (m.citations && m.citations.length > 0) {
                            const msgId = 'msg-' + Math.random().toString(36).substr(2, 9);
                            citationsContainer = renderCitations(m.citations, msgId);
                        }

                        const formattedAnswer = m.message ? m.message.replace(/\n/g, '<br>') : '';
                        const aiHtml = `
                            <div class="flex justify-end mb-8">
                                <div class="bg-white/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/5 px-8 py-7 w-full md:max-w-[95%] rounded-3xl rounded-tl-none relative overflow-hidden">
                                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-teal-400/10 rounded-full blur-3xl"></div>
                                    
                                    <div class="flex items-center justify-between mb-6 border-b border-gray-100/80 pb-4 relative z-10">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-50 to-teal-100/50 flex items-center justify-center text-teal-600 shadow-sm border border-teal-100">
                                            <i class="fa-solid fa-scale-balanced text-lg"></i>
                                        </div>
                                        <span class="text-sm font-black text-transparent bg-clip-text bg-gradient-to-r from-teal-700 to-blue-700">المستشار القانوني الذكي</span>
                                    </div>
                                    
                                    <div class="prose prose-teal max-w-none text-gray-800 leading-loose font-medium text-right text-sm relative z-10">
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
                }

            } catch (error) {
                console.error("Failed to load messages:", error);
                chatMessages.innerHTML = `<span class="text-sm text-red-500 font-bold text-center">فشل تحميل الرسائل.</span>`;
            }
        }

        // حذف محادثة
        async function deleteConversation(uuid, event) {
            event.stopPropagation(); // منع تحميل المحادثة عند الضغط على الحذف
            
            if(!confirm('هل تريد حذف هذه المحادثة بالكامل؟')) return;

            try {
                const response = await fetch(`/legal-assistant/conversations/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                if(data.success) {
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
                console.error("Failed to delete conversation:", error);
            }
        }

        // إرسال السؤال الجديد
        async function submitQuestion() {
            const input = document.getElementById('question-input');
            const chatMessages = document.getElementById('chat-messages');
            const mainContainer = document.getElementById('chat-container');
            const question = input.value.trim();
            if(!question) return;

            // إخفاء المحتويات الترحيبية
            const visuals = document.getElementById('welcome-visuals');
            if(visuals) visuals.style.display = 'none';
            const suggestions = document.getElementById('suggested-queries');
            if(suggestions) suggestions.style.display = 'none';

            // إضافة رسالة المستخدم للشاشة
            const userMsgHtml = `
                <div class="flex justify-start mb-8">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-3xl rounded-tr-none px-6 py-4 shadow-xl shadow-blue-900/20 max-w-[85%] border border-blue-500/30">
                        <p class="text-base font-bold leading-relaxed">${question}</p>
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', userMsgHtml);
            
            const loadingId = 'loading-' + Date.now();
            const loadingHtml = `
                <div id="${loadingId}" class="flex justify-end mb-8">
                    <div class="bg-white/95 backdrop-blur-2xl shadow-xl ring-1 ring-black/5 rounded-3xl rounded-tl-none px-6 py-4 flex items-center gap-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <span class="text-xs font-bold text-gray-500 ml-2">جاري استخراج السوابق والأنظمة القانونية...</span>
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
                    throw new Error("HTTP status " + response.status);
                }

                const data = await response.json();
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
                if(data.citations && data.citations.length > 0) {
                    const msgId = 'msg-' + Math.random().toString(36).substr(2, 9);
                    citationsContainer = renderCitations(data.citations, msgId);
                }

                const formattedAnswer = data.answer ? data.answer.replace(/\n/g, '<br>') : '';
                const aiMsgHtml = `
                    <div class="flex justify-end mb-8">
                        <div class="bg-white/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/5 px-8 py-7 w-full md:max-w-[95%] rounded-3xl rounded-tl-none relative overflow-hidden">
                            <div class="absolute -top-10 -left-10 w-40 h-40 bg-teal-400/10 rounded-full blur-3xl"></div>
                            
                            <div class="flex items-center justify-between mb-6 border-b border-gray-100/80 pb-4 relative z-10">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-50 to-teal-100/50 flex items-center justify-center text-teal-600 shadow-sm border border-teal-100">
                                    <i class="fa-solid fa-scale-balanced text-lg"></i>
                                </div>
                                <span class="text-sm font-black text-transparent bg-clip-text bg-gradient-to-r from-teal-700 to-blue-700">المستشار القانوني الذكي</span>
                            </div>
                            
                            <div class="prose prose-teal max-w-none text-gray-800 leading-loose font-medium text-right text-sm relative z-10">
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
                        <div class="bg-white/95 backdrop-blur-2xl px-6 py-4 rounded-3xl rounded-tl-none border border-red-100 shadow-sm">
                            <span class="text-rose-500 font-bold">حدث خطأ تقني، يرجى المحاولة مرة أخرى.</span>
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
</body>
</html>
