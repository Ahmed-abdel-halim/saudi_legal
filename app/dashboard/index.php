<?php
// dashboard/index.php
// لوحة التحكم الرئيسية (للشركات الموردة والطالبة)

include '../config/db_connect.php';
session_start();

// 1. التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. التوجيه الذكي (إذا كان خبيراً، نرسله لمساحته الخاصة)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'expert') {
    header("Location: expert/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. جلب بيانات الشركة
$stmt = $conn->prepare("
    SELECT u.*, c.name as company_name, c.company_logo, c.is_verified_provider 
    FROM users u 
    LEFT JOIN companies c ON u.company_id = c.company_id 
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// إحصائيات سريعة (للوحة التحكم)
// عدد الموظفين
$team_count_query = $conn->query("SELECT count(*) as count FROM users WHERE company_id = '{$user['company_id']}' AND role='expert'");
$team_count = $team_count_query->fetch_assoc()['count'];

// عدد الخدمات
$services_count_query = $conn->query("SELECT count(*) as count FROM expert_services WHERE expert_id IN (SELECT user_id FROM users WHERE company_id = '{$user['company_id']}')");
$services_count = $services_count_query->fetch_assoc()['count'];

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة القيادة | <?php echo htmlspecialchars($user['company_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="font-black text-2xl tracking-tighter text-indigo-900">TimeShare</span>
                <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded border border-slate-200">Business</span>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:block text-left">
                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['company_name']); ?></p>
                    <p class="text-xs text-slate-400">حساب الشركة</p>
                </div>
                <?php $logo = !empty($user['company_logo']) ? "../".$user['company_logo'] : "https://ui-avatars.com/api/?name=".urlencode($user['company_name'])."&background=random"; ?>
                <img src="<?php echo $logo; ?>" class="w-10 h-10 rounded-lg border border-slate-200 object-contain p-0.5 bg-white">
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10">
        
        <!-- Welcome Banner -->
        <div class="bg-indigo-900 rounded-3xl p-8 text-white mb-10 relative overflow-hidden shadow-xl">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-10 -translate-y-10"></div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-3xl font-bold mb-2">مرحباً بك، <?php echo explode(' ', $user['full_name'])[0]; ?> 👋</h1>
                    <p class="text-indigo-200">إليك نظرة عامة على أداء شركتك وفريقك اليوم.</p>
                </div>
                <div class="flex gap-3">
                    <a href="post_project.php" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        طلب خبير جديد
                    </a>
                </div>
            </div>
        </div>

        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <!-- كارت الفريق -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">👥</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">فريق العمل</p>
                    <p class="text-2xl font-black text-slate-800"><?php echo $team_count; ?></p>
                </div>
            </div>
            <!-- كارت الخدمات -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-2xl">📦</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">الخدمات المعروضة</p>
                    <p class="text-2xl font-black text-slate-800"><?php echo $services_count; ?></p>
                </div>
            </div>
            <!-- كارت المبيعات (مثال) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-2xl">💰</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">الإيرادات</p>
                    <p class="text-2xl font-black text-slate-800">0 <span class="text-xs font-normal">ريال</span></p>
                </div>
            </div>
            <!-- كارت التقييم -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-2xl">⭐</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">تقييم الشركة</p>
                    <p class="text-2xl font-black text-slate-800">4.9</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions (Main Navigation) -->
        <h2 class="text-xl font-bold text-slate-800 mb-6">إدارة الشركة</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- 1. إدارة الفريق (الرابط المطلوب) -->
            <a href="team/index.php" class="group block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-indigo-500 hover:shadow-md transition cursor-pointer">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">إدارة الفريق والموظفين</h3>
                <p class="text-sm text-slate-500">إضافة موظفين، تعديل بياناتهم، ومتابعة أدائهم.</p>
                <div class="mt-4 text-indigo-600 text-sm font-bold flex items-center gap-1 group-hover:gap-2 transition-all">
                    الذهاب للفريق <span class="text-lg">&larr;</span>
                </div>
            </a>

            <!-- 2. المشاريع والطلبات -->
            <a href="projects.php" class="group block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-emerald-500 hover:shadow-md transition cursor-pointer">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">المشاريع والعقود</h3>
                <p class="text-sm text-slate-500">متابعة العقود النشطة، الفواتير، وحالة المشاريع.</p>
            </a>

            <!-- 3. الإعدادات -->
            <a href="settings.php" class="group block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-slate-400 hover:shadow-md transition cursor-pointer">
                <div class="w-14 h-14 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-slate-800 group-hover:text-white transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">إعدادات الشركة</h3>
                <p class="text-sm text-slate-500">تحديث السجل التجاري، الشعار، والبيانات المالية.</p>
            </a>

        </div>

    </div>
</body>
</html>