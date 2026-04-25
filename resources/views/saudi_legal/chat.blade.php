<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Legal Assistant | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        
        /* Premium Background Image */
        /* Premium Background Image */
        .premium-bg {
            background-image: url('/images/backgroundchat.png');
            background-size: 100% 100%; /* تمنع الزوم وتجعلها بحجمها الطبيعي */
            background-position: top center; /* تبدأ الصورة من الأعلى */
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

        .glass-bubble {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border-radius: 2rem 2rem 0 2rem; /* Chat bubble tail */
        }

        /* Glowing Live RAG Circle (Hidden as it is in the bg image) */
        /*
        .glowing-circle { ... }
        */

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
    </style>
</head>
<body class="bg-[#f0f7f9] h-screen overflow-hidden flex flex-col font-sans antialiased text-gray-800">

    <!-- Navbar -->
    <nav class="relative z-10 glass-panel border-b border-white/60 px-6 py-4 flex items-center justify-between">
        <!-- Right: Logo & Title -->
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard.expert.legal_workbench') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-robot text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-gray-800 tracking-tight">المساعد القانوني السعودي الذكي</h1>
                    <p class="text-xs font-bold text-teal-600 tracking-widest uppercase flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                        LIVE RAG ENGINE | 15,954 ARTICLES
                    </p>
                </div>
            </div>
        </div>

        <!-- Center: Main Title -->
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 hidden md:block">
            <h2 class="text-3xl font-black text-gradient tracking-tight">المنقذ القانوني السعودي الذكي</h2>
        </div>

        <!-- Left: Badge -->
        <div class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-900 to-indigo-900 rounded-full shadow-lg shadow-indigo-900/20">
            <i class="fa-regular fa-gem text-blue-300"></i>
            <span class="text-xs font-bold text-white tracking-widest">PREMIUM EXPERT ACCESS</span>
        </div>
    </nav>

    <!-- Background Wrapper -->
    <div class="premium-bg flex-1 w-full flex flex-col relative overflow-hidden">

        <!-- Main Content Area (Scrollable) -->
        <main id="chat-container" class="flex-1 relative z-10 flex flex-col max-w-7xl mx-auto w-full px-4 pt-10 pb-4 overflow-y-auto" style="scrollbar-width: none;">
        
            <!-- Top Section: Visuals & Welcome -->
            <div class="flex-1 flex items-center justify-between relative mb-12" id="welcome-visuals">
                <!-- Left: Robot Image Placeholder -->
                <div class="w-1/3 flex justify-start pl-10 relative">
                     <div class="w-64 h-64 rounded-full flex items-center justify-center relative"></div>
                </div>

                <!-- Center: Live RAG Circle -->
                <div class="w-1/3 flex justify-center relative z-20"></div>

                <!-- Right: Welcome Bubble & Gavel -->
                <div class="w-1/3 flex flex-col items-end pr-10 relative">
                    <div class="glass-bubble p-6 max-w-sm mb-6 relative z-20" style="margin-top: -60px;">
                        <p class="text-sm text-gray-700 leading-relaxed font-medium">
                            مرحباً بك في المساعد القانوني السعودي، أنا مدعوم بكافة الأنظمة والتشريعات الصادرة عن هيئة الخبراء <span class="font-bold text-teal-600">(15,954 مادة قانونية)</span>.
                            <br><br>
                            كيف يمكنني مساعدتك اليوم؟ يمكنك سؤالي عن أي مادة قانونية أو استشارة تجارية.
                        </p>
                        <div class="absolute -right-4 -top-4 w-10 h-10 rounded-xl bg-blue-100 border-2 border-white flex items-center justify-center text-blue-600 shadow-md">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                    </div>
                    <div class="w-32 h-32 flex items-center justify-center -mr-4 relative"></div>
                </div>
            </div>

            <!-- Suggested Queries -->
            <div class="flex flex-col gap-3 w-full max-w-4xl mx-auto" id="suggested-queries">
                <h3 class="text-sm font-bold text-gray-500 text-right px-2">أسئلة مقترحة:</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" dir="rtl">
                    <button onclick="document.getElementById('question-input').value='ما هي شروط تملك الأجانب للعقار في السعودية؟'; submitQuestion();" class="glass-panel rounded-2xl p-4 flex items-center gap-4 hover:bg-white/80 transition group text-right cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-house-user"></i></div>
                        <div class="flex-1">
                            <h4 class="font-black text-gray-800 text-sm group-hover:text-teal-600 transition">تملك العقارات</h4>
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">شروط تملك الأجانب للعقار</p>
                        </div>
                    </button>
                    <button onclick="document.getElementById('question-input').value='ما هي عقوبة الموظف العام في حال ثبوت جريمة الرشوة؟'; submitQuestion();" class="glass-panel rounded-2xl p-4 flex items-center gap-4 hover:bg-white/80 transition group text-right cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-gavel"></i></div>
                        <div class="flex-1">
                            <h4 class="font-black text-gray-800 text-sm group-hover:text-blue-600 transition">الجرائم الجنائية</h4>
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">عقوبة جريمة الرشوة للموظف</p>
                        </div>
                    </button>
                    <button onclick="document.getElementById('question-input').value='متى يسقط حق الزوجة في المطالبة بالنفقة؟'; submitQuestion();" class="glass-panel rounded-2xl p-4 flex items-center gap-4 hover:bg-white/80 transition group text-right cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-scale-balanced"></i></div>
                        <div class="flex-1">
                            <h4 class="font-black text-gray-800 text-sm group-hover:text-emerald-600 transition">الأحوال الشخصية</h4>
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">حالات سقوط النفقة عن الزوجة</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Chat Display Area -->
            <div id="chat-messages" class="flex flex-col w-full max-w-4xl mx-auto mt-6 px-2 pb-4">
                <!-- Messages will be appended here dynamically -->
            </div>
            
        </main>

        <!-- Fixed Input Bar Area -->
        <div class="w-full relative z-20 pb-6 pt-2">
            <div class="max-w-4xl mx-auto px-4">
                <div class="relative w-full input-glow transition-all duration-300 rounded-full bg-white/80 backdrop-blur-xl border border-white shadow-xl shadow-blue-900/5">
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

    <script>
        async function submitQuestion() {
            const input = document.getElementById('question-input');
            const chatMessages = document.getElementById('chat-messages');
            const mainContainer = document.getElementById('chat-container');
            const question = input.value.trim();
            if(!question) return;

            // Hide Suggestions & Visuals once chat starts
            const visuals = document.getElementById('welcome-visuals');
            if(visuals) visuals.style.display = 'none';
            const suggestions = document.getElementById('suggested-queries');
            if(suggestions) suggestions.style.display = 'none';

            // 1. Append User Message
            const userMsgHtml = `
                <div class="flex justify-start mb-8">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-3xl rounded-tr-none px-6 py-4 shadow-xl shadow-blue-900/20 max-w-[85%] border border-blue-500/30">
                        <p class="text-base font-bold leading-relaxed">${question}</p>
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', userMsgHtml);
            
            // 2. Append Loading Indicator
            const loadingId = 'loading-' + Date.now();
            const loadingHtml = `
                <div id="${loadingId}" class="flex justify-end mb-8">
                    <div class="bg-white/95 backdrop-blur-2xl shadow-xl ring-1 ring-black/5 rounded-3xl rounded-tl-none px-6 py-4 flex items-center gap-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <span class="text-xs font-bold text-gray-500 ml-2">جاري استخراج المواد القانونية...</span>
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', loadingHtml);

            // Reset input & scroll to bottom
            input.value = '';
            document.getElementById('btn-send').disabled = true;
            document.getElementById('btn-send').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            mainContainer.scrollTop = mainContainer.scrollHeight;

            // 3. Make API Call
            try {
                const response = await fetch("{{ route('dashboard.expert.legal_assistant.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ question: question })
                });

                const data = await response.json();
                
                // Remove loading
                document.getElementById(loadingId).remove();

                // Format Citations
                let citationsContainer = '';
                if(data.citations && data.citations.length > 0) {
                    const citationsHtml = data.citations.map((c, index) => `
                        <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200/60 rounded-2xl p-4 hover:shadow-md transition-all cursor-pointer group">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs font-black group-hover:bg-teal-500 group-hover:text-white transition-colors shrink-0">${index + 1}</div>
                                <h5 class="text-xs font-black text-gray-800 line-clamp-1 flex-1">${c.title}</h5>
                                <span class="text-[10px] font-bold text-teal-700 bg-teal-50 border border-teal-100 px-2 py-1 rounded-md shrink-0">${c.article}</span>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed font-medium line-clamp-3 pr-11">${c.text}</p>
                        </div>
                    `).join('');
                    
                    citationsContainer = `
                        <div class="mt-8 pt-5 border-t border-gray-100 relative z-10">
                            <div class="flex items-center gap-2 mb-4 justify-end">
                                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">المصادر القانونية المؤكدة</span>
                                <i class="fa-solid fa-book-open text-gray-300"></i>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                ${citationsHtml}
                            </div>
                        </div>
                    `;
                }

                // 4. Append AI Answer
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
