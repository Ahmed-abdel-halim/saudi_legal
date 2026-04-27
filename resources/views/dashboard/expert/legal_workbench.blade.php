<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saudi Legal Workbench | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f9fafb;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-gray-50 overflow-y-auto custom-scrollbar flex flex-col min-h-screen">

    <!-- Simple Header -->
    <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.expert') }}" class="text-gray-400 hover:text-gray-800 transition">
                <i class="fa-solid fa-arrow-right rtl:rotate-180"></i>
            </a>
            <h1 class="text-lg font-black text-gray-800 tracking-wide">Radiif <span class="text-blue-600">Legal
                    AI</span></h1>
        </div>
        <div class="flex items-center gap-4 text-sm font-bold">
            <span class="text-gray-500">المنجز اليوم: <span
                    class="text-emerald-600">{{ $stats['completed_today'] }}</span></span>
            <span class="text-gray-500">متبقي: <span class="text-blue-600">{{ $stats['pending_tasks'] }}</span></span>
        </div>
    </header>

    @if($task)
        <!-- Main Content Area -->
        <main class="flex-1 w-full max-w-4xl mx-auto py-10 px-4">

            <!-- The Main Card -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden relative">

                <div class="p-8 md:p-12 space-y-8">

                    <!-- Card Header -->
                    <div
                        class="flex items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md">TASK #{{ $task->id }}</span>
                        <span>تحقق من صحة العبارة</span>
                    </div>

                    <!-- The Question -->
                    <div class="text-center">
                        <h2 class="text-2xl md:text-3xl font-black text-gray-800 leading-tight">
                            {{ $task->question }}
                        </h2>
                    </div>

                    <!-- Proposed Answer Section -->
                    <div class="space-y-4 pt-4">
                        <div class="flex justify-between items-center px-2">
                            <span class="text-xs text-gray-400 font-bold flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-gray-300"></i> نظام الإثبات: انقر على أي كلمة
                                لتحديدها كخطأ
                            </span>
                            <span class="text-sm font-black text-emerald-600 flex items-center gap-2">
                                الإجابة المقترحة <i class="fa-regular fa-circle-check"></i>
                            </span>
                        </div>

                        <div class="bg-gray-50 p-6 md:p-8 rounded-2xl border border-gray-100">
                            <p class="text-gray-700 text-lg leading-relaxed text-center font-medium" id="ai-answer-text">
                                {{ $task->proposed_answer }}
                            </p>
                        </div>
                    </div>

                    <!-- Primary Source -->
                    <div
                        class="text-center text-sm font-bold text-gray-500 bg-gray-50/50 py-3 rounded-xl border border-dashed border-gray-200">
                        المصدر الأساسي: {{ $task->case_reference ?? 'حكم قضائي مرتبط بـ ' . $task->law_system_name }}
                    </div>

                    <!-- Tags / Labels (Dynamic) -->
                    <div class="border border-gray-100 rounded-2xl p-5">
                        <div class="flex items-center justify-end gap-2 text-xs font-bold text-gray-400 mb-4">
                            الوسوم المقترحة للربط <i class="fa-solid fa-tags"></i>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3">
                            @php
                                // Generate dynamic tags based on the task
                                $keywords = [$task->law_system_name];
                                $words = explode(' ', $task->question);
                                foreach (array_slice($words, 0, 8) as $word) {
                                    $cleanWord = trim(str_replace(['؟', '،', '.', 'هل', 'كيف', 'ما'], '', $word));
                                    if (mb_strlen($cleanWord) > 3) {
                                        $keywords[] = $cleanWord;
                                    }
                                }
                                // Limit to 4 tags
                                $keywords = array_slice(array_unique(array_filter($keywords)), 0, 4);
                            @endphp
                            @foreach($keywords as $keyword)
                                <label
                                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <span class="text-sm font-bold text-gray-600">{{ $keyword }}</span>
                                    <input type="checkbox" checked
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div id="action-buttons" class="grid grid-cols-2 gap-4 pt-4">
                        <button onclick="toggleCorrection(true)"
                            class="group flex flex-col items-center justify-center p-6 bg-rose-50 border border-rose-100 rounded-3xl hover:bg-rose-100 transition duration-300">
                            <div
                                class="w-12 h-12 bg-rose-500 text-white rounded-full flex items-center justify-center text-xl mb-3 shadow-md shadow-rose-200 group-hover:-translate-y-1 transition transform">
                                <i class="fa-solid fa-pen"></i>
                            </div>
                            <span class="font-black text-rose-700 text-lg">تعديل</span>
                            <span class="text-xs text-rose-500 font-bold mt-1">بها أخطاء، وتحتاج مراجعة</span>
                        </button>

                        <button onclick="submitTask(true)"
                            class="group flex flex-col items-center justify-center p-6 bg-emerald-50 border border-emerald-100 rounded-3xl hover:bg-emerald-100 transition duration-300">
                            <div
                                class="w-12 h-12 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xl mb-3 shadow-md shadow-emerald-200 group-hover:-translate-y-1 transition transform">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <span class="font-black text-emerald-700 text-lg">صحيحة</span>
                            <span class="text-xs text-emerald-500 font-bold mt-1">اعتماد ومتابعة</span>
                        </button>
                    </div>

                    <!-- Hidden Correction Form -->
                    <div id="correction-area"
                        class="hidden space-y-4 pt-4 animate-in fade-in slide-in-from-top-4 duration-300">
                        <div class="p-1 bg-rose-50 rounded-2xl border border-rose-100">
                            <textarea id="correct_answer"
                                class="w-full h-40 p-5 bg-transparent border-none focus:ring-0 outline-none text-lg font-bold text-gray-800 resize-none"
                                placeholder="اكتب التعديل الصحيح هنا..."></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="toggleCorrection(false)"
                                class="px-6 py-4 bg-gray-100 text-gray-600 font-black rounded-xl hover:bg-gray-200 transition">إلغاء</button>
                            <button onclick="submitTask(false)" id="btn-submit-edit"
                                class="flex-1 flex justify-center items-center gap-2 px-6 py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                حفظ التعديل <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Reference Texts (Bottom Cards) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Right: Mada -->
                        <div class="space-y-3 text-right">
                            <div
                                class="flex items-center justify-end gap-2 text-gray-400 font-bold text-xs uppercase tracking-widest">
                                نص المادة <i class="fa-regular fa-file-lines"></i>
                            </div>
                            <div class="max-h-[500px] overflow-y-auto custom-scrollbar pr-1 space-y-6">
                                @if($mentioned_articles && $mentioned_articles->count() > 0)
                                    @foreach($mentioned_articles as $article)
                                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                            <div
                                                class="bg-gray-50 px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                                                <h4 class="font-black text-gray-800 text-xs">
                                                    {{ $article->article_title }} من {{ $article->legislation_title }}
                                                </h4>
                                                <i class="fa-solid fa-bookmark text-blue-500 text-[10px]"></i>
                                            </div>
                                            <div class="p-4">
                                                <p class="text-sm text-gray-600 leading-relaxed font-medium whitespace-pre-wrap">
                                                    {{ strip_tags($article->content) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="bg-gray-50 p-6 rounded-2xl border border-dashed border-gray-200 text-center">
                                        <i class="fa-solid fa-magnifying-glass text-gray-300 text-3xl mb-3"></i>
                                        <p class="text-sm text-gray-500 font-bold">لم يتم العثور على إشارات قانونية صريحة في نص
                                            الحكم.</p>
                                        <p class="text-xs text-gray-400 mt-1">المحرك الذكي لم يجد "المادة X" مذكورة في النص
                                            الحالي.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Left: Hukm -->
                        <div class="space-y-3 text-right">
                            <div
                                class="flex items-center justify-end gap-2 text-gray-400 font-bold text-xs uppercase tracking-widest">
                                نص الحكم <i class="fa-solid fa-gavel"></i>
                            </div>
                            <p
                                class="text-sm text-gray-600 leading-relaxed font-medium max-h-[500px] overflow-y-auto custom-scrollbar pl-2 whitespace-pre-wrap">
                                {{ $task->case_text ?? 'لا يتوفر نص سابقة قضائية لهذه المهمة في قاعدة البيانات.' }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Card Footer Navigation -->
                <div class="bg-gray-50/50 px-8 py-4 border-t border-gray-100 flex justify-between items-center">
                    <button onclick="skipTask()"
                        class="text-gray-400 hover:text-gray-800 font-bold text-sm flex items-center gap-2 transition">
                        تخطي <i class="fa-solid fa-forward-step rtl:rotate-180"></i>
                    </button>
                    <button onclick="previousTask()"
                        class="text-gray-400 hover:text-gray-800 font-bold text-sm flex items-center gap-2 transition">
                        <i class="fa-solid fa-angle-right rtl:rotate-180"></i> السابقة
                    </button>
                </div>

            </div>

        </main>
    @else
        <main class="flex-1 flex items-center justify-center">
            <div class="text-center p-12 bg-white rounded-[3rem] shadow-sm border border-gray-100 max-w-md w-full">
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <i class="fa-solid fa-party-horn text-5xl text-yellow-500 absolute top-0 left-0 animate-bounce"></i>
                    <i class="fa-solid fa-star text-2xl text-blue-400 absolute bottom-0 right-0 animate-pulse"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-800 mb-3">عظيم! لا توجد مهام جديدة.</h2>
                <p class="text-gray-500 text-sm font-medium mb-8 leading-relaxed">لقد قمت بتنقيح كل البيانات المتاحة في
                    القائمة. يمكنك العودة لاحقاً للمزيد.</p>
                <a href="{{ route('dashboard.expert') }}"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-200">
                    <i class="fa-solid fa-rotate"></i> التحقق من مهام جديدة
                </a>
            </div>
        </main>
    @endif

    <script>
        function toggleCorrection(show) {
            const area = document.getElementById('correction-area');
            const buttons = document.getElementById('action-buttons');

            if (show) {
                area.classList.remove('hidden');
                buttons.classList.add('hidden');
                document.getElementById('correct_answer').value = document.getElementById('ai-answer-text').innerText.trim();
                document.getElementById('correct_answer').focus();
            } else {
                area.classList.add('hidden');
                buttons.classList.remove('hidden');
            }
        }

        async function submitTask(isCorrect) {
            const taskId = "{{ $task->id ?? '' }}";
            if (!taskId) return;

            const data = {
                task_id: taskId,
                is_correct: isCorrect,
                correct_answer: isCorrect ? null : document.getElementById('correct_answer').value,
                _token: "{{ csrf_token() }}"
            };

            try {
                const response = await fetch("{{ route('dashboard.expert.legal_workbench.submit') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    animateAndReload();
                } else {
                    alert('خطأ: ' + result.message);
                }
            } catch (error) { console.error('Error:', error); alert('حدث خطأ تقني أثناء الحفظ.'); }
        }

        async function skipTask() {
            const taskId = "{{ $task->id ?? '' }}";
            if (!taskId) return;
            try {
                await fetch("{{ route('dashboard.expert.legal_workbench.skip') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ task_id: taskId, _token: "{{ csrf_token() }}" })
                });
                animateAndReload();
            } catch (error) { console.error('Error:', error); }
        }

        async function previousTask() {
            try {
                await fetch("{{ route('dashboard.expert.legal_workbench.previous') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ _token: "{{ csrf_token() }}" })
                });
                animateAndReload(true);
            } catch (error) { console.error('Error:', error); }
        }

        function animateAndReload(isPrevious = false) {
            const card = document.querySelector('.max-w-4xl');
            card.style.opacity = '0';
            card.style.transform = isPrevious ? 'translateX(20px)' : 'translateX(-20px)';
            card.style.transition = 'all 0.3s ease';
            setTimeout(() => window.location.reload(), 300);
        }
    </script>

</body>

</html>