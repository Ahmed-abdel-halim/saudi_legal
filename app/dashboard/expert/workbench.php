<?php
// dashboard/expert/workbench.php

// 1. إعداد النظام
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. الاتصال بقاعدة البيانات
$possible_paths = [
    __DIR__ . '/../../config/db_connect.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/db_connect.php',
    __DIR__ . '/../config/db_connect.php'
];

$conn_file = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $conn_file = $path;
        break;
    }
}

if (!$conn_file) die("ملف الاتصال مفقود.");
require_once $conn_file;

if (session_status() === PHP_SESSION_NONE) session_start();

// 3. بيانات المستخدم
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'خبير معتمد';
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// --- حساب الأرباح والإنجاز (Gamification) ---
$today_date = date('Y-m-d');
// نحسب عدد المهام التي أنجزها هذا الخبير اليوم
$stats_stmt = $conn->prepare("SELECT COUNT(*) as count FROM ai_responses_v2 WHERE expert_id = ? AND DATE(created_at) = ?");
$stats_stmt->bind_param("is", $user_id, $today_date);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

$tasks_today = $stats['count'];
$price_per_task = 5; // ريال (سعر افتراضي للمهمة)
$earnings_today = $tasks_today * $price_per_task;

// 4. معالج الحفظ (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');

    $task_id = intval($_POST['task_id']);

    // تخطي
    if ($_POST['action'] === 'skip_task') {
        $conn->query("UPDATE ai_tasks_v2 SET status = 'skipped' WHERE id = $task_id");
        echo json_encode(['success' => true]);
        exit;
    }

    // حفظ
    if ($_POST['action'] === 'submit_task') {
        $correction = trim($_POST['correction']);
        
        if ($task_id <= 0 || empty($correction)) {
            echo json_encode(['success' => false, 'message' => 'البيانات فارغة']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO ai_responses_v2 (task_id, expert_id, correction) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iis", $task_id, $user_id, $correction);
            $stmt->execute();
            $stmt->close();
            
            $conn->query("UPDATE ai_tasks_v2 SET status = 'completed' WHERE id = $task_id");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ النظام']);
        }
        exit;
    }
}

// 5. جلب مهمة (تجاهل المكتملة والمستبعدة)
// ملاحظة: الجدول يجب أن يكون موجوداً من الخطوات السابقة
$currentTask = null;
// نتأكد أولاً أن الجدول موجود لتجنب الأخطاء
$conn->query("CREATE TABLE IF NOT EXISTS ai_tasks_v2 (id INT AUTO_INCREMENT PRIMARY KEY, original_text TEXT NOT NULL, status ENUM('pending', 'completed', 'skipped') DEFAULT 'pending')");
$conn->query("CREATE TABLE IF NOT EXISTS ai_responses_v2 (id INT AUTO_INCREMENT PRIMARY KEY, task_id INT, expert_id INT, correction TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

$res = $conn->query("SELECT * FROM ai_tasks_v2 WHERE status = 'pending' ORDER BY id ASC LIMIT 1");
if ($res && $res->num_rows > 0) $currentTask = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

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
            overflow: hidden; /* لمنع السكرول في وضع التطبيق */
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

        .user-info { display: flex; align-items: center; gap: 15px; }
        
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
        .back-btn:hover { color: var(--primary); transform: scale(1.1); }

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
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Content Areas --- */
        .card-header {
            padding: 15px 25px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }
        .task-id { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; letter-spacing: 1px; }

        .question-box {
            padding: 40px 30px;
            text-align: center;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            border-bottom: 1px dashed #cbd5e1;
        }
        .q-label { font-size: 0.85rem; color: #64748b; font-weight: 700; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .q-text { font-size: 1.6rem; font-weight: 800; line-height: 1.6; color: var(--text-dark); }

        /* --- Buttons --- */
        .decision-area { padding: 30px; background: #fff; }
        .buttons-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }

        .decision-btn {
            border: none; padding: 30px 20px; border-radius: 18px; cursor: pointer;
            font-family: 'Tajawal'; font-size: 1.1rem; font-weight: 700;
            display: flex; flex-direction: column; align-items: center; gap: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        
        .btn-correct { background: #f0fdf4; color: #15803d; border: 2px solid #bbf7d0; }
        .btn-correct:active { transform: scale(0.96); }
        
        .btn-wrong { background: #fef2f2; color: #b91c1c; border: 2px solid #fecaca; }
        .btn-wrong:active { transform: scale(0.96); }

        .icon-lg { font-size: 2rem; margin-bottom: 5px; }

        .skip-btn {
            display: block; width: 100%; padding: 15px; text-align: center;
            background: transparent; border: none; color: #94a3b8;
            font-family: 'Tajawal'; font-weight: 600; cursor: pointer;
            border-radius: 12px; transition: 0.2s;
        }
        .skip-btn:hover { background: #f1f5f9; color: #64748b; }

        /* --- Editor --- */
        .editor-area { display: none; padding: 25px; background: #fff; animation: fadeUp 0.3s; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        textarea {
            width: 100%; padding: 20px; font-size: 1.2rem;
            border: 2px solid #e2e8f0; border-radius: 16px;
            font-family: 'Tajawal'; outline: none; background: #fff;
            min-height: 160px; color: var(--text-dark); resize: none;
            transition: 0.3s;
        }
        textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(0, 108, 53, 0.1); }

        .editor-actions { display: flex; gap: 15px; margin-top: 20px; }
        .btn-main { flex: 2; background: var(--primary); color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 108, 53, 0.3); }
        .btn-sec { flex: 1; background: #fff; border: 2px solid #e2e8f0; color: #64748b; border-radius: 12px; cursor: pointer; font-weight: 700; }

        /* --- Empty State & Refresh --- */
        .empty-state { text-align: center; padding: 60px 30px; }
        .empty-illustration { font-size: 5rem; color: #cbd5e1; margin-bottom: 20px; display: block; }
        
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
        .btn-refresh:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(0, 108, 53, 0.5); }
        .btn-refresh:active { transform: scale(0.95); }
        .btn-refresh i { font-size: 1.2rem; }

        /* --- Loader --- */
        .loader-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.95); display: none; align-items: center; justify-content: center; z-index: 50; flex-direction: column; }
        .spinner { width: 45px; height: 45px; border: 5px solid #e2e8f0; border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 15px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 600px) {
            .q-text { font-size: 1.3rem; }
            .main-container { padding: 15px; align-items: start; }
            .wb-card { min-height: calc(100vh - 100px); border: none; box-shadow: none; }
            .stat-badge span { display: none; } /* إخفاء النص في الجوال والاكتفاء بالرقم */
            .stat-badge span.value-text { display: inline; }
        }
    </style>
</head>
<body oncontextmenu="return false;">

    <nav class="top-nav">
        <a href="../expert/index.php" class="back-btn" title="خروج"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        
        <div class="user-info">
            <div class="stat-badge">
                <i class="fa-solid fa-list-check" style="color:#64748b"></i>
                <span>اليوم:</span>
                <span class="value-text"><?php echo $tasks_today; ?></span>
            </div>
            
            <div class="stat-badge earnings">
                <i class="fa-solid fa-coins" style="color:#b45309"></i>
                <span>الرصيد:</span>
                <span class="value-text"><?php echo $earnings_today; ?> ريال</span>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="wb-card">
            <?php if ($currentTask): ?>
                
                <div class="card-header">
                    <span class="task-id">TASK #<?php echo $currentTask['id']; ?></span>
                    <span style="color:#94a3b8; font-size:0.9rem; font-weight:700;"><i class="fa-regular fa-clock"></i> <span id="timer">00:00</span></span>
                </div>

                <div class="question-box">
                    <div class="q-label">تحقق من صحة العبارة</div>
                    <div class="q-text" id="originalText"><?php echo nl2br(htmlspecialchars($currentTask['original_text'])); ?></div>
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
                    
                    <button class="skip-btn" onclick="skipTask()">
                        غير متأكد؟ <b>تخطي المهمة</b>
                    </button>
                </div>

                <div class="editor-area" id="editorStep">
                    <div style="margin-bottom:15px; font-weight:700; color:#475569;">التصحيح المقترح:</div>
                    <form id="taskForm">
                        <input type="hidden" name="action" value="submit_task">
                        <input type="hidden" name="task_id" value="<?php echo $currentTask['id']; ?>">
                        <textarea id="editor" name="correction" placeholder="اكتب النص الصحيح هنا..."></textarea>
                        
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

            <?php else: ?>
                <div class="empty-state">
                    <span class="empty-illustration">🎉</span>
                    <h2 style="color:var(--text-dark); margin-bottom:10px;">عظيم! لا توجد مهام جديدة.</h2>
                    <p style="color:#64748b; line-height:1.6;">لقد قمت بتنقيح كل البيانات المتاحة في القائمة.<br>يمكنك العودة لاحقاً للمزيد.</p>
                    
                    <button id="refreshBtn" onclick="checkNewTasks()" class="btn-refresh">
                        <i class="fa-solid fa-arrows-rotate"></i> التحقق من مهام جديدة
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <input type="hidden" id="rawOriginal" value="<?php echo htmlspecialchars($currentTask['original_text'] ?? ''); ?>">

    <script>
        // Timer
        let sec = 0; setInterval(() => { sec++; document.getElementById('timer').innerText = new Date(sec * 1000).toISOString().substr(14, 5); }, 1000);

        // API Handler
        function postAction(action, text) {
            const loader = document.getElementById('loader');
            loader.style.display = 'flex';
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('task_id', document.querySelector('input[name="task_id"]').value);
            if(text) formData.append('correction', text);

            fetch('workbench.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if (d.success) window.location.reload();
                else { alert(d.message); loader.style.display = 'none'; }
            })
            .catch(() => { alert('فشل الاتصال'); loader.style.display = 'none'; });
        }

        // Actions
        function markAsCorrect() { postAction('submit_task', document.getElementById('rawOriginal').value); }
        
        function submitCorrection() { 
            const val = document.getElementById('editor').value.trim();
            if(!val) {
                document.getElementById('editor').style.borderColor = 'red';
                return;
            }
            postAction('submit_task', val); 
        }
        
        function skipTask() { 
            if(confirm('هل أنت متأكد من تخطي هذه المهمة؟')) postAction('skip_task', null); 
        }

        // UI Toggles
        function showEditor() {
            document.getElementById('decisionStep').style.display = 'none';
            document.getElementById('editorStep').style.display = 'block';
            const ed = document.getElementById('editor');
            ed.value = document.getElementById('rawOriginal').value;
            ed.focus();
        }
        
        function cancelEdit() {
            document.getElementById('editorStep').style.display = 'none';
            document.getElementById('decisionStep').style.display = 'block';
        }

        // Refresh Button Logic
        function checkNewTasks() {
            const btn = document.getElementById('refreshBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري البحث...';
            btn.style.opacity = '0.8';
            btn.disabled = true;
            
            setTimeout(() => {
                window.location.reload();
            }, 800); // تأخير بسيط لجمالية التفاعل
        }

        // Security
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && ['c','x','u'].includes(e.key)) e.preventDefault();
        });
    </script>
</body>
</html>