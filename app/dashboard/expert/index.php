<?php
// dashboard/expert/index.php

include '../../config/db_connect.php';
session_start();

// 1. التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 3. حساب الإحصائيات والأرباح (محاكاة لنظام المستويات)
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_tasks, 
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as tasks_today,
    MAX(created_at) as last_activity
    FROM ai_responses_v2 
    WHERE expert_id = $user_id");
    
$stats = $stats_query->fetch_assoc();

$total_tasks = $stats['total_tasks'] ?? 0;
$tasks_today = $stats['tasks_today'] ?? 0;

// إعدادات الأسعار والمستويات
$price_per_task = 5; // ريال لكل مهمة
$total_balance = $total_tasks * $price_per_task;
$today_balance = $tasks_today * $price_per_task;

// منطق تحديد المستوى والشارة
$expert_level = 'مساهم جديد';
$badge_color = 'bg-gray-100 text-gray-600';
$badge_icon = 'fa-user';

if ($total_tasks > 500) {
    $expert_level = 'خبير سيادي (Elite)';
    $badge_color = 'bg-purple-100 text-purple-700 border-purple-200';
    $badge_icon = 'fa-crown';
} elseif ($total_tasks > 100) {
    $expert_level = 'مدقق معتمد (Certified)';
    $badge_color = 'bg-blue-100 text-blue-700 border-blue-200';
    $badge_icon = 'fa-certificate';
} elseif ($total_tasks > 20) {
    $expert_level = 'مساهم نشط';
    $badge_color = 'bg-green-100 text-green-700 border-green-200';
    $badge_icon = 'fa-star';
}

// 4. جلب المهام المتاحة (للزر الأحمر)
$pending_query = $conn->query("SELECT count(*) as c FROM ai_tasks_v2 WHERE status = 'pending'");
$pending_count = $pending_query->fetch_assoc()['c'];

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الخبير | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-green-200 shadow-lg">R</div>
                <div>
                    <h1 class="font-bold text-lg leading-none text-slate-800">Radiif</h1>
                    <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT DASHBOARD</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($user['full_name']); ?></span>
                    <span class="text-xs text-green-600 font-medium"><?php echo $expert_level; ?></span>
                </div>
                <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm">
                    <?php $avatar = !empty($user['profile_picture']) ? "../../".$user['profile_picture'] : "https://ui-avatars.com/api/?name=".urlencode($user['full_name'])."&background=random&color=fff&background=006C35"; ?>
                    <img src="<?php echo $avatar; ?>" class="w-full h-full object-cover">
                </div>
                <a href="../../auth/logout.php" class="text-slate-400 hover:text-red-500 transition"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">الرصيد الكلي</p>
                    <h2 class="text-3xl font-bold text-slate-800"><?php echo number_format($total_balance, 2); ?> <span class="text-sm text-slate-400 font-normal">ريال</span></h2>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">أرباح اليوم</p>
                    <h2 class="text-3xl font-bold text-slate-800"><?php echo number_format($today_balance, 2); ?> <span class="text-sm text-slate-400 font-normal">ريال</span></h2>
                </div>
                <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">المهام المنجزة</p>
                    <h2 class="text-3xl font-bold text-slate-800"><?php echo $total_tasks; ?> <span class="text-sm text-slate-400 font-normal">مهمة</span></h2>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded">LIVE</span>
                                <?php if($pending_count > 0): ?>
                                    <span class="text-green-300 text-sm font-bold animate-pulse">● يوجد <?php echo $pending_count; ?> مهام في الانتظار</span>
                                <?php else: ?>
                                    <span class="text-slate-400 text-sm">لا توجد مهام حالياً</span>
                                <?php endif; ?>
                            </div>
                            <h2 class="text-3xl font-bold mb-2">منصة التدقيق السيادية</h2>
                            <p class="text-slate-300 text-sm max-w-md">قم بمراجعة وتصحيح البيانات لرفع جودة النماذج الوطنية.</p>
                        </div>
                        
                        <a href="workbench.php" class="bg-green-600 hover:bg-green-500 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-green-900/50 transition transform hover:-translate-y-1 flex items-center gap-3">
                            <i class="fa-solid fa-play"></i> ابدأ التدقيق
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-slate-700">سجل الإنجاز الأخير</h3>
                        <span class="text-xs text-slate-400">آخر 5 عمليات</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="p-4 font-semibold">رقم المهمة</th>
                                    <th class="p-4 font-semibold">الإجراء</th>
                                    <th class="p-4 font-semibold">التوقيت</th>
                                    <th class="p-4 font-semibold">القيمة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php
                                $history = $conn->query("SELECT * FROM ai_responses_v2 WHERE expert_id = $user_id ORDER BY id DESC LIMIT 5");
                                if ($history->num_rows > 0):
                                    while($row = $history->fetch_assoc()):
                                ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-mono text-slate-600">#<?php echo $row['task_id']; ?></td>
                                    <td class="p-4">
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">تم التصحيح</span>
                                    </td>
                                    <td class="p-4 text-slate-500"><?php echo date('H:i A', strtotime($row['created_at'])); ?></td>
                                    <td class="p-4 font-bold text-slate-700">+<?php echo $price_per_task; ?> ريال</td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">لا يوجد سجل نشاط بعد. ابدأ العمل الآن!</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="space-y-6">
                
                <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden relative">
                    <div class="h-24 bg-gradient-to-r from-green-700 to-green-600"></div>
                    
                    <div class="px-6 pb-6 relative">
                        <div class="w-24 h-24 bg-white rounded-full p-1 shadow-lg absolute -top-12 right-1/2 translate-x-1/2">
                            <img src="<?php echo $avatar; ?>" class="w-full h-full rounded-full object-cover">
                        </div>
                        
                        <div class="mt-14 text-center">
                            <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p class="text-sm text-slate-500 mt-1"><?php echo !empty($user['job_title']) ? $user['job_title'] : 'خبير بيانات'; ?></p>
                            
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mt-4 <?php echo $badge_color; ?>">
                                <i class="fa-solid <?php echo $badge_icon; ?>"></i>
                                <span class="text-xs font-bold"><?php echo $expert_level; ?></span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3 border-t border-slate-100 pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">رقم الخبير</span>
                                <span class="font-mono font-bold text-slate-700">EXP-<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">تاريخ الانضمام</span>
                                <span class="font-bold text-slate-700"><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">حالة الحساب</span>
                                <span class="text-green-600 font-bold flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> نشط</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="cv-builder.php" class="block w-full py-2 bg-slate-50 text-slate-600 text-center rounded-lg text-sm font-bold hover:bg-slate-100 transition">تحديث الملف الشخصي</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
                    <h4 class="font-bold text-sm text-slate-700 mb-3">إجراءات سريعة</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="services.php" class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-lg hover:bg-green-50 hover:text-green-700 transition cursor-pointer">
                            <i class="fa-solid fa-box-open mb-2 text-lg"></i>
                            <span class="text-xs font-bold">الخدمات</span>
                        </a>
                        <a href="availability.php" class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition cursor-pointer">
                            <i class="fa-regular fa-clock mb-2 text-lg"></i>
                            <span class="text-xs font-bold">التوفر</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="mt-12 text-center text-slate-400 text-xs">
            &copy; <?php echo date('Y'); ?> Radiif. جميع الحقوق محفوظة لخبراء البيانات الوطنية.
        </div>

    </div>
</body>
</html>