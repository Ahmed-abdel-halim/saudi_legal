<?php
// dashboard/provider/my-services.php
// صفحة عرض وإدارة خدمات الشركة الموردة (مجمعة من الخبراء)

include '../../config/db_connect.php';
session_start();

// 1. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. جلب معرف الشركة
$stmt = $conn->prepare("SELECT company_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$company_id = $res->fetch_assoc()['company_id'] ?? 0;

if ($company_id == 0) {
    die("خطأ: هذا الحساب غير مرتبط بشركة.");
}

// 3. جلب جميع الخدمات التابعة لخبراء هذه الشركة
// نربط جدول الخدمات (expert_services) مع جدول المستخدمين (users) للتحقق من company_id
$sql = "
    SELECT es.*, u.full_name as expert_name, u.profile_picture, u.job_title
    FROM expert_services es
    JOIN users u ON es.expert_id = u.user_id
    WHERE u.company_id = ?
    ORDER BY es.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خدماتي المعروضة | TimeShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-20">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="../index.php" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path></svg>
                </a>
                <span class="font-bold text-xl text-slate-800">خدماتي المعروضة</span>
            </div>
            
            <!-- زر إضافة خدمة جديدة (يوجه لإضافة خدمة لموظف معين - يمكن تطويره لاحقاً) -->
            <!-- حالياً نكتفي بالعرض، أو نوجه لصفحة الفريق ليضيف كل خبير خدماته -->
            <a href="../team/index.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إدارة الفريق والخدمات
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10">
        
        <?php if (count($services) > 0): ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($services as $srv): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition group relative">
                        
                        <!-- شارة التصنيف -->
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-xs font-bold text-indigo-600 border border-indigo-100 shadow-sm">
                            <?php echo htmlspecialchars($srv['category']); ?>
                        </div>

                        <!-- المحتوى -->
                        <div class="p-6">
                            <!-- تفاصيل الخبير -->
                            <div class="flex items-center gap-3 mb-4 border-b border-slate-50 pb-4">
                                <?php $avatar = !empty($srv['profile_picture']) ? "../../".$srv['profile_picture'] : "https://ui-avatars.com/api/?name=".urlencode($srv['expert_name'])."&background=random"; ?>
                                <img src="<?php echo $avatar; ?>" class="w-10 h-10 rounded-full border border-slate-100">
                                <div>
                                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($srv['expert_name']); ?></p>
                                    <p class="text-xs text-slate-400"><?php echo htmlspecialchars($srv['job_title'] ?? 'خبير'); ?></p>
                                </div>
                            </div>

                            <h3 class="font-bold text-lg text-slate-800 mb-2 line-clamp-1" title="<?php echo htmlspecialchars($srv['title']); ?>">
                                <?php echo htmlspecialchars($srv['title']); ?>
                            </h3>
                            
                            <p class="text-sm text-slate-500 mb-4 line-clamp-2 h-10">
                                <?php echo htmlspecialchars(mb_substr($srv['description'], 0, 100)) . '...'; ?>
                            </p>

                            <div class="flex justify-between items-center mt-4">
                                <div class="flex flex-col">
                                    <span class="text-xs text-slate-400 font-bold uppercase">السعر</span>
                                    <span class="text-xl font-black text-emerald-600"><?php echo number_format($srv['price']); ?> <small class="text-xs text-slate-500 font-normal">ر.س</small></span>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-xs text-slate-400 font-bold uppercase">التسليم</span>
                                    <span class="text-sm font-bold text-slate-700"><?php echo $srv['delivery_days']; ?> أيام</span>
                                </div>
                            </div>
                        </div>

                        <!-- الإجراءات (حذف أو تعديل - يحتاج برمجة إضافية للتعديل من قبل المدير) -->
                        <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-bold <?php echo $srv['is_active'] ? 'text-green-600 bg-green-100 px-2 py-0.5 rounded' : 'text-red-500 bg-red-100 px-2 py-0.5 rounded'; ?>">
                                <?php echo $srv['is_active'] ? 'نشط' : 'غير نشط'; ?>
                            </span>
                            <!-- يمكن إضافة زر حذف هنا لاحقاً -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <!-- الحالة الفارغة -->
            <div class="max-w-md mx-auto text-center py-16">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">لا توجد خدمات معروضة</h2>
                <p class="text-slate-500 mb-8">لم يقم أي من موظفيك بإضافة باقات أو خدمات حتى الآن. وجههم لإضافة خدماتهم من لوحة تحكم الخبير.</p>
                <a href="../team/index.php" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg">
                    الذهاب لإدارة الفريق
                </a>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>