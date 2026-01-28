<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>منصة التحقق | Radiif</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #006C35;
            --primary-dark: #004d26;
            --accent: #10b981;
            --bg-color: #f0f2f5;
            --text-dark: #1f2937;
            --gold: #f59e0b;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            margin: 0;
            font-family: 'Tajawal', sans-serif;
            background: var(--bg-color);
            background-image: radial-gradient(#d1d5db 1px, transparent 1px);
            background-size: 24px 24px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-dark);
            overflow: hidden;
        }

        /* --- Header & Stats --- */
        .top-nav {
            background: #fff;
            padding: 0 20px;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            z-index: 20;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 6px 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .stat-badge.earnings {
            background: #fffbeb;
            border-color: #fcd34d;
            color: #b45309;
        }

        .back-btn {
            color: #64748b;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 10px;
            transition: 0.2s;
        }

        .back-btn:hover {
            color: var(--primary);
            transform: scale(1.1);
        }

        /* --- Main Layout --- */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
        }

        .wb-card {
            background: #fff;
             width: 100%;
            max-width: 700px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow-y: auto;          /* 👈 هنا */
            max-height: 100%;          /* 👈 مهم */
            display: flex;
            flex-direction: column;
        }


        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Content Areas --- */
        .card-header {
            padding: 15px 25px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .task-id {
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .question-box {
            padding: 40px 30px;
            text-align: center;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            border-bottom: 1px dashed #cbd5e1;
        }

        .q-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .q-text {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.6;
            color: var(--text-dark);
        }

        /* --- Buttons --- */
        .decision-area {
            padding: 30px;
            background: #fff;
        }

        .buttons-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .decision-btn {
            border: none;
            padding: 30px 20px;
            border-radius: 18px;
            cursor: pointer;
            font-family: 'Tajawal';
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .btn-correct {
            background: #f0fdf4;
            color: #15803d;
            border: 2px solid #bbf7d0;
        }

        .btn-correct:active {
            transform: scale(0.96);
        }

        .btn-wrong {
            background: #fef2f2;
            color: #b91c1c;
            border: 2px solid #fecaca;
        }

        .btn-wrong:active {
            transform: scale(0.96);
        }

        .icon-lg {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .skip-btn {
            display: inline-block;
            padding: 15px 20px;
            text-align: center;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-family: 'Tajawal';
            font-weight: 600;
            cursor: pointer;
            border-radius: 12px;
            transition: 0.2s;
        }

        .skip-btn:hover {
            background: #f1f5f9;
            color: #64748b;
        }

        .nav-buttons-row {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        .nav-btn {
            padding: 12px 20px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            font-family: 'Tajawal';
            transition: 0.2s;
            font-size: 0.95rem;
        }

        .nav-btn:hover {
            background: #e2e8f0;
            color: #475569;
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* --- Editor --- */
        .editor-area {
            display: none;
            padding: 25px;
            background: #fff;
            animation: fadeUp 0.3s;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .editor-form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #475569;
            font-size: 0.95rem;
        }

        textarea {
            width: 100%;
            padding: 20px;
            font-size: 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-family: 'Tajawal';
            outline: none;
            background: #fff;
            min-height: 160px;
            color: var(--text-dark);
            resize: none;
            transition: 0.3s;
        }

        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 108, 53, 0.1);
        }

        .editor-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-main {
            flex: 2;
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 108, 53, 0.3);
            font-family: 'Tajawal';
            transition: 0.2s;
        }

        .btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(0, 108, 53, 0.4);
        }

        .btn-main:active {
            transform: scale(0.98);
        }

        .btn-sec {
            flex: 1;
            background: #fff;
            border: 2px solid #e2e8f0;
            color: #64748b;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-family: 'Tajawal';
            transition: 0.2s;
        }

        .btn-sec:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* --- Empty State & Refresh --- */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
        }

        .empty-illustration {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 20px;
            display: block;
        }

        .btn-refresh {
            margin-top: 30px;
            padding: 18px 40px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: 'Tajawal';
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(0, 108, 53, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-refresh:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -5px rgba(0, 108, 53, 0.5);
        }

        .btn-refresh:active {
            transform: scale(0.95);
        }

        .btn-refresh i {
            font-size: 1.2rem;
        }

        /* --- Loader --- */
        .loader-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
            flex-direction: column;
        }

        .spinner {
            width: 45px;
            height: 45px;
            border: 5px solid #e2e8f0;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 600px) {

            body {
                background: #fff;
            }

           .wb-card {
                height: 100dvh;        /* 👈 ارتفاع الشاشة الحقيقي */
                overflow-y: auto;      /* 👈 scrolling */
                padding-bottom: 140px; /* 👈 مساحة للأزرار تحت */
            }




            .main-container {
                padding: 0;
                align-items: stretch;
            }

            .wb-card {
                height: 100%;
                border-radius: 0;
                box-shadow: none;
                border: none;
                display: flex;
                flex-direction: column;
            }

            .question-box {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 32px 20px;
                background: #fff;
                border-bottom: none;
            }

            .q-label {
                font-size: 1rem;
                margin-bottom: 20px;
                opacity: 0.75;
            }

            .q-text {
                font-size: 2.1rem;
                line-height: 1.5;
                text-align: center;
            }

            .card-header {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                gap: 8px;
                padding: 10px 16px;
                font-size: 0.9rem;
                background: #fff;
                border-bottom: none;
            }

            .stat-badge.earnings {
                background: #fffbeb;
                color: #b45309;
                padding: 4px 8px;
                font-size: 0.8rem;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .timer-mobile {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                color: #94a3b8;
                font-size: 0.9rem;
                font-weight: 700;
                margin: 0 8px;
            }

            .user-info .stat-badge:first-child {
                display: none;
            }

            .decision-area {
                position: fixed;
                bottom: 70px;
                left: 0;
                width: 100%;
                padding: 2px 12px calc(env(safe-area-inset-bottom) + 6px);
                z-index: 50;
                background: #ffffff;
                border-top: none;
                box-shadow: none;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .nav-buttons-row {
                flex-direction: column;
                gap: 8px;
            }

            .nav-btn {
                width: 100%;
            }

            .skip-btn {
                display: block !important;
                width: 100%;
                text-align: center;
                padding: 12px;
                background: #f1f5f9;
                border-radius: 12px;
                font-weight: 600;
                cursor: pointer;
                color: #64748b;
                transition: 0.2s;
            }

            .skip-btn:hover {
                background: #e5e7eb;
                color: #374151;
            }

            .buttons-grid {
                gap: 12px;
                margin-bottom: 0;
            }

            .decision-btn {
                min-height: 80px;
                padding: 16px;
                font-size: 1.05rem;
                border-radius: 16px;
            }

            .icon-lg {
                font-size: 1.6rem;
            }

            .task-id {
                display: none !important;
            }
        }
    </style>
</head>

<body oncontextmenu="return false;">

    <nav class="top-nav">
        <a href="{{ route('dashboard.expert') }}" class="back-btn" title="خروج"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>

        <div class="user-info">
            <div class="stat-badge">
                <i class="fa-solid fa-list-check" style="color:#64748b"></i>
                <span>اليوم:</span>
                <span class="value-text">{{ $tasks_today ?? 0 }}</span>
            </div>

            <span class="timer-mobile">
                <i class="fa-regular fa-clock"></i>
                <span id="timer">00:00</span>
            </span>

            <div class="stat-badge earnings">
                <i class="fa-solid fa-coins" style="color:#b45309"></i>
                <span>الرصيد:</span>
                <span class="value-text">{{ $earnings_today ?? 0 }} ريال</span>
            </div>
        </div>

    </nav>

    <div class="main-container">
        <div class="wb-card">
            @if ($currentTask)

            <div class="card-header">
                <span class="task-id">TASK #{{ $currentTask->id }}</span>
            </div>

            <div class="question-box">
                <div class="q-label">تحقق من صحة العبارة</div>
                <div class="q-text" id="originalText">{!! nl2br(htmlspecialchars($currentTask->original_data)) !!}</div>
            </div>

            <div class="decision-area" id="decisionStep">
                <div class="buttons-grid">
                    <button class="decision-btn btn-wrong" onclick="showEditor()">
                        <i class="fa-solid fa-pen-to-square icon-lg"></i>
                        <span>تعديل</span>
                        <span style="font-size:0.75rem; opacity:0.8; font-weight:500;">بها أخطاء</span>
                    </button>

                    <button class="decision-btn btn-correct" onclick="markAsCorrect()">
                        <i class="fa-solid fa-circle-check icon-lg"></i>
                        <span>صحيحة</span>
                        <span style="font-size:0.75rem; opacity:0.8; font-weight:500;">اعتماد ومتابعة</span>
                    </button>
                </div>

                <div class="nav-buttons-row">
                    <button class="nav-btn" id="prevBtn" onclick="loadPreviousTask()" title="المهمة السابقة">
                        <i class="fa-solid fa-chevron-right"></i> السابقة
                    </button>
                    <button class="skip-btn" onclick="skipTask()" style="flex:1;">
                        غير متأكد؟ <b>تخطي</b>
                    </button>
                </div>
            </div>

            <div class="editor-area" id="editorStep">
                <div class="editor-form-group">
                    <label class="form-label">النص الأصلي:</label>
                    <div style="padding:15px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; color:#475569; line-height:1.6;">
                        <span id="originalPreview"></span>
                    </div>
                </div>

                <form id="taskForm">
                    <input type="hidden" name="action" value="submit_task">
                    <input type="hidden" name="task_id" value="{{ $currentTask->id }}">

                    <div class="editor-form-group">
                        <label class="form-label">النص المصحح:</label>
                        <textarea id="editor" name="corrected_data" placeholder="اكتب النص الصحيح هنا..." required></textarea>
                    </div>

                    <div class="editor-form-group">
                        <label class="form-label">ملاحظات التصحيح (اختياري):</label>
                        <textarea id="correction_notes" name="correction_notes" placeholder="أضف ملاحظاتك حول التصحيح..." style="min-height:100px;"></textarea>
                    </div>

                    <div class="editor-form-group">
                        <label class="form-label">درجة الثقة (1-10):</label>
                        <input type="range" id="confidence_level" name="confidence_level" min="1" max="10" value="7" style="width:100%; cursor:pointer;">
                        <div style="text-align:center; color:#64748b; font-weight:600; margin-top:8px;">
                            <span id="confidenceDisplay">7</span>/10
                        </div>
                    </div>

                    <div class="editor-actions">
                        <button type="button" class="btn-sec" onclick="cancelEdit()">إلغاء</button>
                        <button type="button" class="btn-main" onclick="submitCorrection()">
                            حفظ وإنهاء <i class="fa-solid fa-check"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div id="loader" class="loader-overlay">
                <div class="spinner"></div>
                <h3 style="color:var(--primary);">جاري المعالجة...</h3>
            </div>

            @else
            <div class="empty-state">
                <span class="empty-illustration">🎉</span>
                <h2 style="color:var(--text-dark); margin-bottom:10px;">عظيم! لا توجد مهام جديدة.</h2>
                <p style="color:#64748b; line-height:1.6;">لقد قمت بتنقيح كل البيانات المتاحة في القائمة.<br>يمكنك العودة لاحقاً للمزيد.</p>

                <button id="refreshBtn" onclick="checkNewTasks()" class="btn-refresh">
                    <i class="fa-solid fa-arrows-rotate"></i> التحقق من مهام جديدة
                </button>
            </div>
            @endif
        </div>
    </div>

    <input type="hidden" id="rawOriginal" value="{{ $currentTask->original_data ?? '' }}">
    <input type="hidden" id="hasPreviousTask" value="{{ $hasPreviousTask ? 'true' : 'false' }}">

    <script>
        // Timer
        let sec = 0;
        setInterval(() => {
            sec++;
            document.getElementById('timer').innerText = new Date(sec * 1000).toISOString().substr(14, 5);
        }, 1000);

        // Confidence Level Slider
        const confidenceSlider = document.getElementById('confidence_level');
        if (confidenceSlider) {
            confidenceSlider.addEventListener('input', (e) => {
                document.getElementById('confidenceDisplay').innerText = e.target.value;
            });
        }

        // API Handler
        function postAction(action, payload = {}) {
            const loader = document.getElementById('loader');
            loader.style.display = 'flex';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('task_id', document.querySelector('input[name="task_id"]').value);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Add payload fields
            Object.keys(payload).forEach(key => {
                formData.append(key, payload[key]);
            });

            fetch('{{ route("dashboard.expert.workbench.action") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        if (d.redirect) {
                            window.location.href = d.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert(d.message || 'حدث خطأ');
                        loader.style.display = 'none';
                    }
                })
                .catch(() => {
                    alert('فشل الاتصال بالخادم');
                    loader.style.display = 'none';
                });
        }

        // Actions
        function markAsCorrect() {
            postAction('mark_correct', {});
        }

        function submitCorrection() {
            const correctedText = document.getElementById('editor').value.trim();
            if (!correctedText) {
                document.getElementById('editor').style.borderColor = '#dc2626';
                alert('يجب إدخال النص المصحح');
                return;
            }

            const payload = {
                corrected_data: correctedText,
                correction_notes: document.getElementById('correction_notes').value.trim(),
                confidence_level: document.getElementById('confidence_level').value
            };

            postAction('submit_correction', payload);
        }

        function skipTask() {
            postAction('skip_task', {});
        }

        function loadPreviousTask() {
            postAction('load_previous', {});
        }

        // UI Toggles
        function showEditor() {
            document.getElementById('decisionStep').style.display = 'none';
            document.getElementById('editorStep').style.display = 'block';

            const originalText = document.getElementById('rawOriginal').value;
            document.getElementById('originalPreview').innerText = originalText;
            document.getElementById('editor').value = originalText;
            document.getElementById('editor').focus();
        }

        function cancelEdit() {
            document.getElementById('editorStep').style.display = 'none';
            document.getElementById('decisionStep').style.display = 'block';
            document.getElementById('editor').style.borderColor = '#e2e8f0';
        }

        // Initialize previous button state
        window.addEventListener('DOMContentLoaded', () => {
            const hasPrev = document.getElementById('hasPreviousTask').value === 'true';
            const prevBtn = document.getElementById('prevBtn');
            if (prevBtn) {
                prevBtn.disabled = !hasPrev;
            }
        });

        // Refresh Button Logic
        function checkNewTasks() {
            const btn = document.getElementById('refreshBtn');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري البحث...';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            setTimeout(() => {
                window.location.reload();
            }, 800);
        }

        // Security
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && ['c', 'x', 'u'].includes(e.key)) e.preventDefault();
        });
    </script>
</body>

</html>