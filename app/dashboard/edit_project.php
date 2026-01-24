<?php
// dashboard/edit_project.php
// صفحة تعديل المشروع (للطالب فقط)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// ---------------------------------------------------------
// منطق زر العودة الذكي (Smart Back Button Logic)
// ---------------------------------------------------------
$back_url = 'index.php'; // الافتراضي: العودة للداشبورد

// إذا كان هناك رابط سابق (Referer) وليس هو نفس الصفحة الحالية (لتجنب التكرار عند الحفظ)
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    if (strpos($_SERVER['HTTP_REFERER'], 'edit_project.php') === false) {
        $back_url = $_SERVER['HTTP_REFERER'];
    }
}
// ---------------------------------------------------------

// إعدادات اللغة
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar');
$_SESSION['lang'] = $lang;
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

// نصوص
$t = [
    'ar' => [
        'title' => 'تعديل المشروع',
        'back' => 'إلغاء وعودة',
        'lbl_title' => 'عنوان المشروع',
        'lbl_desc' => 'وصف التفاصيل',
        'lbl_skills' => 'المهارات المطلوبة',
        'lbl_budget' => 'الميزانية (ر.س/ساعة)',
        'lbl_duration' => 'المدة (أيام)',
        'btn_save' => 'حفظ التعديلات',
        'msg_success' => 'تم تحديث المشروع بنجاح!',
        'msg_error' => 'حدث خطأ.',
    ],
    'en' => [
        'title' => 'Edit Project',
        'back' => 'Cancel',
        'lbl_title' => 'Project Title',
        'lbl_desc' => 'Description',
        'lbl_skills' => 'Required Skills',
        'lbl_budget' => 'Budget (SAR/hr)',
        'lbl_duration' => 'Duration (Days)',
        'btn_save' => 'Save Changes',
        'msg_success' => 'Project updated successfully!',
        'msg_error' => 'Error occurred.',
    ]
][$lang];

// التحقق من ID
if (!isset($_GET['id'])) { die("رابط غير صالح"); }
$project_id = intval($_GET['id']);

// جلب الشركة
$stmt = $conn->prepare("SELECT company_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_company_id = $stmt->get_result()->fetch_assoc()['company_id'];

// جلب المشروع والتحقق من الملكية
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ? AND requester_company_id = ?");
$stmt->bind_param("ii", $project_id, $my_company_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    die("المشروع غير موجود أو ليس لديك صلاحية لتعديله.");
}

if ($project['status'] != 'open') {
    die("لا يمكن تعديل المشروع بعد بدء العمل أو إغلاقه.");
}

// معالجة التحديث
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $desc = htmlspecialchars(trim($_POST['description']));
    $skills = htmlspecialchars(trim($_POST['skills']));
    $budget = floatval($_POST['budget']);
    $duration = intval($_POST['duration']);
    $duration_hours = $duration * 8; // إعادة حساب الساعات

    $sql = "UPDATE projects SET title=?, description=?, required_skills=?, budget=?, duration_days=?, requested_duration_hours=? WHERE project_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdiis", $title, $desc, $skills, $budget, $duration, $duration_hours, $project_id);
    
    if ($stmt->execute()) {
        $msg = $t['msg_success'];
        $msg_type = "success";
        // تحديث البيانات المعروضة
        $project['title'] = $title; $project['description'] = $desc; 
        $project['required_skills'] = $skills; $project['budget'] = $budget; $project['duration_days'] = $duration;
        
        // العودة لنفس صفحة التفاصيل بعد الحفظ
        header("refresh:2;url=project_details.php?id=$project_id");
    } else {
        $msg = $t['msg_error'] . " " . $conn->error;
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> | TimeShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-20">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center max-w-4xl">
            <h1 class="font-bold text-lg text-slate-700"><?php echo $t['title']; ?></h1>
            
            <!-- زر العودة الذكي -->
            <a href="<?php echo htmlspecialchars($back_url); ?>" class="text-slate-500 hover:text-slate-800 font-bold text-sm flex items-center gap-1 transition">
                <?php if($lang=='ar'): ?>&larr;<?php else: ?>&rarr;<?php endif; ?> 
                <?php echo $t['back']; ?>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-2xl">
        <?php if($msg): ?>
            <div class="mb-6 p-4 rounded-lg font-bold <?php echo ($msg_type=='success')?'bg-green-100 text-green-700':'bg-red-100 text-red-700'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_title']; ?></label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_desc']; ?></label>
                    <textarea name="description" required rows="5" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none transition resize-none"><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_skills']; ?></label>
                    <input type="text" name="skills" value="<?php echo htmlspecialchars($project['required_skills']); ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_budget']; ?></label>
                        <input type="number" name="budget" value="<?php echo $project['budget']; ?>" step="0.01" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_duration']; ?></label>
                        <input type="number" name="duration" value="<?php echo $project['duration_days']; ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                    <!-- زر إلغاء إضافي في الأسفل -->
                    <a href="<?php echo htmlspecialchars($back_url); ?>" class="text-slate-500 font-bold text-sm hover:text-slate-700">إلغاء</a>
                    
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition">
                        <?php echo $t['btn_save']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>