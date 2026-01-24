<?php
// dashboard/post_project.php
// صفحة نشر طلب مشروع جديد (نسخة مصححة)

// 1. إعدادات عرض الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db_connect.php';

// 2. إصلاح مشكلة الجلسة (التحقق قبل البدء)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 4. إعدادات اللغة
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar');
$_SESSION['lang'] = $lang;
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

// قاموس الترجمة
$trans_data = [
    'ar' => [
        'title' => 'نشر طلب جديد',
        'back' => 'إلغاء وعودة',
        'page_title' => 'ما الذي تحتاج لإنجازه؟',
        'page_desc' => 'صف احتياجاتك بدقة لتصل إلى أفضل الخبراء والشركات الموردة.',
        'lbl_title' => 'عنوان المشروع / الطلب',
        'ph_title' => 'مثال: مطلوب خبير React لتطوير واجهة مستخدم',
        'lbl_desc' => 'وصف التفاصيل (Scope of Work)',
        'ph_desc' => 'اشرح المهام المطلوبة، التوقعات، وأي تفاصيل تقنية...',
        'lbl_skills' => 'المهارات المطلوبة',
        'ph_skills' => 'مثال: PHP, Laravel, MySQL (افصل بفاصلة)',
        'lbl_budget' => 'الحد الأقصى لسعر الساعة (ر.س)',
        'lbl_duration' => 'المدة المتوقعة (أيام)',
        'btn_submit' => 'نشر الطلب الآن',
        'msg_success' => 'تم نشر طلبك بنجاح! بانتظار العروض.',
        'msg_error' => 'حدث خطأ أثناء النشر.',
        'tips_title' => 'نصائح لطلب ناجح',
        'tips_list' => [
            'كن محدداً في العنوان.',
            'وضح المهارات التقنية المطلوبة بدقة.',
            'حدد ميزانية واقعية لجذب أفضل الكفاءات.'
        ]
    ],
    'en' => [
        'title' => 'Post New Request',
        'back' => 'Cancel & Return',
        'page_title' => 'What needs to be done?',
        'page_desc' => 'Describe your needs clearly to reach the best experts and suppliers.',
        'lbl_title' => 'Project Title',
        'ph_title' => 'E.g. Need React Expert for UI Development',
        'lbl_desc' => 'Scope of Work',
        'ph_desc' => 'Explain the tasks, expectations, and technical details...',
        'lbl_skills' => 'Required Skills',
        'ph_skills' => 'E.g. PHP, Laravel, MySQL (comma separated)',
        'lbl_budget' => 'Max Hourly Rate (SAR)',
        'lbl_duration' => 'Expected Duration (Days)',
        'btn_submit' => 'Post Request Now',
        'msg_success' => 'Request posted successfully! Awaiting bids.',
        'msg_error' => 'Error posting request.',
        'tips_title' => 'Tips for a great request',
        'tips_list' => [
            'Be specific in your title.',
            'Clearly define technical skills.',
            'Set a realistic budget to attract top talent.'
        ]
    ]
];
$t = $trans_data[$lang];

// 5. جلب معرف الشركة
if (!isset($conn) || $conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات.");
}

$stmt = $conn->prepare("SELECT company_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data || empty($user_data['company_id'])) {
    die("<div style='padding:20px; text-align:center;'><h3>خطأ: حسابك غير مرتبط بشركة.</h3><p>يجب عليك <a href='settings.php'>إنشاء ملف شركة</a> قبل نشر الطلبات.</p></div>");
}

$company_id = $user_data['company_id'];

// 6. معالجة النموذج (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $title = htmlspecialchars(trim($_POST['title']));
    $desc = htmlspecialchars(trim($_POST['description']));
    $skills = htmlspecialchars(trim($_POST['skills']));
    $budget = floatval($_POST['budget']);
    $duration = intval($_POST['duration']);
    
    if (!empty($title) && !empty($desc)) {
        
        // حساب الساعات التقريبية (8 ساعات عمل لكل يوم) لتلبية متطلبات قاعدة البيانات
        $duration_hours = $duration * 8;

        // تم إضافة requested_duration_hours للاستعلام
        $sql = "INSERT INTO projects (requester_company_id, title, description, required_skills, budget, duration_days, requested_duration_hours, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'open')";
        
        // محاولة تنفيذ الاستعلام مع التعامل مع الأخطاء المحتملة
        try {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // isssdii -> integer, string, string, string, double, integer, integer
                $stmt->bind_param("isssdii", $company_id, $title, $desc, $skills, $budget, $duration, $duration_hours);
                if ($stmt->execute()) {
                    $msg = $t['msg_success'];
                    $msg_type = "success";
                    header("refresh:2;url=projects.php");
                } else {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
            } else {
                // إذا فشل التحضير (غالباً بسبب عمود ناقص)
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            // التعامل مع الأخطاء وإصلاح الجداول تلقائياً إذا لزم الأمر
            $error_message = $e->getMessage();

            // التحقق إذا كان الخطأ بسبب عمود duration_days الناقص
            if (strpos($error_message, "Unknown column 'duration_days'") !== false) {
                $conn->query("ALTER TABLE projects ADD COLUMN duration_days INT DEFAULT NULL");
                // إعادة المحاولة...
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssdii", $company_id, $title, $desc, $skills, $budget, $duration, $duration_hours);
                if ($stmt->execute()) {
                    $msg = $t['msg_success']; $msg_type = "success"; header("refresh:2;url=projects.php");
                } else {
                    $msg = "فشلت العملية بعد الإصلاح التلقائي: " . $stmt->error; $msg_type = "error";
                }
            } 
            // التحقق إذا كان الخطأ بسبب عمود requested_duration_hours الناقص
            elseif (strpos($error_message, "Unknown column 'requested_duration_hours'") !== false) {
                $conn->query("ALTER TABLE projects ADD COLUMN requested_duration_hours INT DEFAULT NULL");
                // إعادة المحاولة...
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssdii", $company_id, $title, $desc, $skills, $budget, $duration, $duration_hours);
                if ($stmt->execute()) {
                    $msg = $t['msg_success']; $msg_type = "success"; header("refresh:2;url=projects.php");
                } else {
                    $msg = "فشلت العملية بعد الإصلاح التلقائي: " . $stmt->error; $msg_type = "error";
                }
            }
            else {
                $msg = $t['msg_error'] . " " . $error_message;
                $msg_type = "error";
            }
        }

    } else {
        $msg = ($lang == 'ar') ? "يرجى ملء الحقول الإجبارية" : "Please fill required fields";
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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: '<?php echo ($lang == 'ar') ? 'Cairo' : 'Inter'; ?>', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-20">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center max-w-4xl">
            <div class="flex items-center gap-3">
                <span class="font-black text-xl text-indigo-900 tracking-tighter">TimeShare</span>
                <span class="text-slate-300">|</span>
                <span class="font-bold text-lg text-slate-700"><?php echo $t['title']; ?></span>
            </div>
            <a href="projects.php" class="text-slate-500 hover:text-slate-800 font-bold text-sm flex items-center gap-1 transition">
                <?php if($lang=='ar'): ?>&larr;<?php else: ?>&rarr;<?php endif; ?> 
                <?php echo $t['back']; ?>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-4xl">

        <?php if($msg): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 shadow-sm <?php echo ($msg_type=='success')?'bg-green-50 text-green-700 border border-green-200':'bg-red-50 text-red-700 border border-red-200'; ?>">
                <?php if ($msg_type == 'success'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php endif; ?>
                <span class="font-bold"><?php echo $msg; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Form Column -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-slate-800 mb-2"><?php echo $t['page_title']; ?></h1>
                        <p class="text-slate-500"><?php echo $t['page_desc']; ?></p>
                    </div>

                    <form method="POST" action="" class="space-y-6">
                        
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                <?php echo $t['lbl_title']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" required placeholder="<?php echo $t['ph_title']; ?>" 
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition text-lg font-medium">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                <?php echo $t['lbl_desc']; ?> <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" required rows="5" placeholder="<?php echo $t['ph_desc']; ?>"
                                      class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition resize-none leading-relaxed"></textarea>
                        </div>

                        <!-- Skills -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                <?php echo $t['lbl_skills']; ?>
                            </label>
                            <div class="relative">
                                <span class="absolute top-3.5 <?php echo ($lang=='ar')?'left-4':'right-4'; ?> text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                </span>
                                <input type="text" name="skills" placeholder="<?php echo $t['ph_skills']; ?>" 
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                            </div>
                        </div>

                        <!-- Budget & Duration -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    <?php echo $t['lbl_budget']; ?>
                                </label>
                                <div class="relative">
                                    <input type="number" name="budget" min="1" step="0.01" placeholder="0.00" 
                                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition font-bold text-slate-700">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    <?php echo $t['lbl_duration']; ?>
                                </label>
                                <input type="number" name="duration" min="1" placeholder="1" 
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition font-bold text-slate-700">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1 flex items-center justify-center gap-2 text-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                <?php echo $t['btn_submit']; ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Tips Column -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Tips Card -->
                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                    <h3 class="font-bold text-emerald-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php echo $t['tips_title']; ?>
                    </h3>
                    <ul class="space-y-3">
                        <?php foreach($t['tips_list'] as $tip): ?>
                            <li class="flex items-start gap-2 text-sm text-emerald-700 leading-relaxed">
                                <span class="mt-1.5 w-1.5 h-1.5 bg-emerald-500 rounded-full flex-shrink-0"></span>
                                <?php echo $tip; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Banner -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10"></div>
                    <p class="font-bold text-lg mb-2 relative z-10">تبحث عن خبير محدد؟</p>
                    <p class="text-indigo-100 text-sm mb-4 relative z-10">يمكنك تصفح قائمة الخبراء المتاحين فوراً بدلاً من انتظار العروض.</p>
                    <a href="../search.php" class="inline-block bg-white text-indigo-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-50 transition relative z-10">تصفح الخبراء</a>
                </div>

            </div>

        </div>
    </div>

</body>
</html>