<?php
// المسار: dashboard/client/index.php

// 1. الاتصال بقاعدة البيانات
$possible_paths = [
    __DIR__ . '/../../config/db_connect.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/db_connect.php',
    __DIR__ . '/../config/db_connect.php'
];
$conn_file = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) { $conn_file = $path; break; }
}
if (!$conn_file) die("ملف الاتصال مفقود.");
require_once $conn_file;

session_start();

// 2. التحقق من الصلاحيات (حماية الصفحة)
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن مسجلاً، نرسله لصفحة الدخول
    header("Location: ../../auth/login.php");
    exit();
}
// إذا كان المستخدم مسجلاً ولكنه ليس عميلاً (مثلاً خبير)
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'client') {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
        <h1 style='color:red'>⛔ وصول مرفوض</h1>
        <p>أنت مسجل دخول بصلاحية: <b>" . htmlspecialchars($_SESSION['role']) . "</b></p>
        <p>هذه الصفحة مخصصة لحسابات <b>العملاء (Clients)</b> فقط.</p>
        <a href='../../auth/logout.php' style='background:#333; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;'>تسجيل خروج</a>
    </div>");
}

$user_name = $_SESSION['username'] ?? 'عميل مميز';

// 3. معالجة الرفع (CSV / Manual)
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_data') {
    
    // إنشاء الجدول إذا لم يكن موجوداً
    $conn->query("CREATE TABLE IF NOT EXISTS ai_tasks_v2 (id INT AUTO_INCREMENT PRIMARY KEY, original_text TEXT NOT NULL, status ENUM('pending', 'completed', 'skipped') DEFAULT 'pending')");

    // أ) نص يدوي
    if (!empty($_POST['manual_text'])) {
        $text = trim($_POST['manual_text']);
        $stmt = $conn->prepare("INSERT INTO ai_tasks_v2 (original_text, status) VALUES (?, 'pending')");
        $stmt->bind_param("s", $text);
        $stmt->execute();
        $msg = "تمت إضافة النص بنجاح!";
        $msg_type = "success";
    }
    
    // ب) ملف CSV
    elseif (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        $count = 0;
        
        // قراءة الملف (يفترض أن النص في العمود الأول)
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $text = trim($data[0]);
            // تجاهل الصفوف الفارغة أو الترويسة إذا كانت كلمة "Text"
            if (!empty($text) && strtolower($text) !== 'text') {
                $stmt = $conn->prepare("INSERT INTO ai_tasks_v2 (original_text, status) VALUES (?, 'pending')");
                $stmt->bind_param("s", $text);
                $stmt->execute();
                $count++;
            }
        }
        fclose($handle);
        $msg = "تم استيراد $count صف بنجاح!";
        $msg_type = "success";
    } else {
        $msg = "الرجاء إدخال نص أو اختيار ملف.";
        $msg_type = "error";
    }
}

// 4. تصدير البيانات (CSV Export)
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cleaned_data_radiif.csv');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // دعم العربية
    fputcsv($out, ['ID', 'Original Text', 'Corrected Text', 'Expert', 'Date']);
    
    $rows = $conn->query("SELECT t.id, t.original_text, r.correction, u.full_name, r.created_at FROM ai_responses_v2 r JOIN ai_tasks_v2 t ON r.task_id = t.id JOIN users u ON r.expert_id = u.user_id ORDER BY r.id DESC");
    while ($r = $rows->fetch_assoc()) fputcsv($out, $r);
    fclose($out);
    exit;
}

// 5. الإحصائيات
$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM ai_tasks_v2) as total,
    (SELECT COUNT(*) FROM ai_tasks_v2 WHERE status='completed') as completed,
    (SELECT COUNT(*) FROM ai_tasks_v2 WHERE status='pending') as pending
")->fetch_assoc();
$percent = ($stats['total'] > 0) ? round(($stats['completed'] / $stats['total']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة العميل | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-700 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-green-200">R</div>
                <div>
                    <div class="font-bold text-xl tracking-tight text-slate-900">Radiif</div>
                    <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-bold border border-slate-200">CLIENT PORTAL</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden md:block text-left">
                    <div class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="text-xs text-green-600 font-bold">حساب أعمال</div>
                </div>
                <div class="h-10 w-10 bg-slate-200 rounded-full overflow-hidden border border-slate-300">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=random" class="w-full h-full">
                </div>
                <a href="../../auth/logout.php" class="bg-red-50 text-red-500 p-2 rounded-lg hover:bg-red-100 transition" title="خروج"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10">

        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-10">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">إدارة الحملات</h1>
                <p class="text-slate-500 mt-2">مراقبة جودة البيانات وعمليات التنقيح.</p>
            </div>
            <div class="flex gap-3">
                <a href="?export=csv" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-5 py-3 rounded-xl font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-download"></i> تصدير (CSV)
                </a>
                <button onclick="document.getElementById('uploadModal').showModal()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-green-200 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> رفع بيانات
                </button>
            </div>
        </div>

        <?php if($msg): ?>
        <div class="<?php echo ($msg_type=='success') ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?> border px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="fa-solid <?php echo ($msg_type=='success') ? 'fa-check-circle' : 'fa-triangle-exclamation'; ?>"></i> <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-slate-400 text-xs font-bold uppercase mb-1">الإجمالي</div>
                <div class="text-3xl font-bold text-slate-800"><?php echo $stats['total']; ?></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-slate-400 text-xs font-bold uppercase mb-1">تم التنقيح</div>
                <div class="text-3xl font-bold text-green-600"><?php echo $stats['completed']; ?></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-slate-400 text-xs font-bold uppercase mb-1">قيد الانتظار</div>
                <div class="text-3xl font-bold text-orange-500"><?php echo $stats['pending']; ?></div>
            </div>
            <div class="bg-slate-900 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-slate-400 text-xs font-bold uppercase mb-1">نسبة الإنجاز</div>
                    <div class="text-3xl font-bold text-white"><?php echo $percent; ?>%</div>
                    <div class="w-full bg-slate-700 rounded-full h-1.5 mt-3"><div class="bg-green-400 h-1.5 rounded-full" style="width: <?php echo $percent; ?>%"></div></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700">آخر النتائج المعتمدة</h3>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-bold">Live</span>
            </div>
            <div class="divide-y divide-slate-100">
                <?php
                $feed = $conn->query("SELECT t.original_text, r.correction, u.full_name FROM ai_responses_v2 r JOIN ai_tasks_v2 t ON r.task_id = t.id JOIN users u ON r.expert_id = u.user_id ORDER BY r.id DESC LIMIT 5");
                if ($feed && $feed->num_rows > 0):
                    while($row = $feed->fetch_assoc()):
                ?>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 hover:bg-slate-50 transition">
                    <div>
                        <div class="text-xs font-bold text-red-400 uppercase mb-1">النص الأصلي</div>
                        <p class="text-slate-500 text-sm line-through decoration-red-300"><?php echo htmlspecialchars($row['original_text']); ?></p>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <div class="text-xs font-bold text-green-600 uppercase">النص المنقح</div>
                            <div class="text-[10px] text-slate-400"><i class="fa-solid fa-user-pen"></i> <?php echo htmlspecialchars($row['full_name']); ?></div>
                        </div>
                        <p class="text-slate-800 font-bold text-sm"><?php echo htmlspecialchars($row['correction']); ?></p>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div class="p-10 text-center text-slate-400">لا توجد بيانات منقحة بعد.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <dialog id="uploadModal" class="modal rounded-2xl shadow-2xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
        <form method="POST" enctype="multipart/form-data" class="bg-white">
            <input type="hidden" name="action" value="upload_data">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-lg text-slate-800">إضافة بيانات جديدة</h3>
                <button type="button" onclick="document.getElementById('uploadModal').close()" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">رفع ملف (CSV)</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 hover:border-green-500 transition relative">
                        <input type="file" name="csv_file" accept=".csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-300 mb-2"></i>
                        <p class="text-sm text-slate-500 font-bold">اضغط لاختيار الملف</p>
                    </div>
                </div>
                <div class="text-center text-xs text-slate-300 font-bold uppercase">- أو -</div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">نص سريع</label>
                    <textarea name="manual_text" rows="3" class="w-full border border-slate-300 rounded-xl p-3 text-sm outline-none focus:border-green-500" placeholder="اكتب النص هنا..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('uploadModal').close()" class="px-4 py-2 rounded-lg text-slate-500 font-bold hover:bg-slate-200">إلغاء</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg">إرسال للحملة</button>
            </div>
        </form>
    </dialog>

</body>
</html>