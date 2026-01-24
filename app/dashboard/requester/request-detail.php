<?php
// dashboard/requester/request-detail.php - تفاصيل الطلب والعروض

// إظهار الأخطاء (يمكنك إيقافها عند الإطلاق)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// معالجة مشكلة التحذيرات عند استدعاء الهيدر (Session Warnings Fix)
// نقوم بإخفاء التنبيهات مؤقتاً أثناء التضمين لأن الجلسة قد تكون بدأت بالفعل
$original_error_level = error_reporting();
error_reporting($original_error_level & ~E_WARNING & ~E_NOTICE);

include '../../layout/dashboard_header.php';

// إعادة مستوى الإبلاغ عن الأخطاء لوضعه الطبيعي
error_reporting($original_error_level);

// 1. التحقق من المعرف
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='p-8 text-center text-red-500'>خطأ: لم يتم تحديد الطلب.</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}

$request_id = intval($_GET['id']);
$company_id = $_SESSION['company_id'] ?? 0;

// 2. ضمان وجود معرف الشركة (بدون إعادة تضمين db_connect إذا كان الاتصال موجوداً)
if ($company_id == 0 && isset($_SESSION['user_id'])) {
    // نستخدم $conn الموجود مسبقاً من dashboard_header.php
    if (isset($conn)) {
        $u_id = $_SESSION['user_id'];
        $c_stmt = $conn->prepare("SELECT company_id FROM users WHERE user_id = ?");
        $c_stmt->bind_param("i", $u_id);
        $c_stmt->execute();
        $c_res = $c_stmt->get_result();
        if ($c_row = $c_res->fetch_assoc()) {
            $company_id = $c_row['company_id'];
            $_SESSION['company_id'] = $company_id; // تحديث الجلسة
        }
    }
}

// 3. جلب تفاصيل المشروع
// هذه الصفحة مخصصة لصاحب الطلب فقط (requester_company_id = company_id)
$sql = "SELECT *, 
        COALESCE(description, 'No description') as final_description,
        COALESCE(duration_days, 0) as final_duration
        FROM projects 
        WHERE project_id = ? AND requester_company_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='p-8 text-center text-gray-500'>عذراً، هذا الطلب غير موجود أو لا تملك صلاحية الوصول إليه.</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}

$project = $result->fetch_assoc();

// 4. جلب عدد العروض
$bids_count = 0;
// التحقق من وجود جدول bids لتجنب الأخطاء
$check_table = $conn->query("SHOW TABLES LIKE 'bids'");
if ($check_table && $check_table->num_rows > 0) {
    $stmt_bids = $conn->prepare("SELECT COUNT(*) as count FROM bids WHERE project_id = ?");
    $stmt_bids->bind_param("i", $request_id);
    $stmt_bids->execute();
    $bids_count = $stmt_bids->get_result()->fetch_assoc()['count'];
}
?>

<div class="max-w-5xl mx-auto py-8 px-4">
    
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($project['title']); ?></h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold 
                    <?php echo ($project['status'] == 'open') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                    <?php echo ucfirst($project['status']); ?>
                </span>
            </div>
            <p class="text-sm text-gray-500">تم النشر في: <?php echo date('d F Y', strtotime($project['created_at'])); ?></p>
        </div>
        
        <div class="flex gap-3">
            <!-- زر التعديل (يوجه لصفحة edit_project.php في المجلد الرئيسي للداشبورد) -->
            <a href="../edit_project.php?id=<?php echo $project['project_id']; ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                تعديل الطلب
            </a>
            <a href="../projects.php" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-bold">
                &larr; العودة للقائمة
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Description Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">تفاصيل المشروع</h3>
                <div class="prose max-w-none text-gray-600 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($project['final_description'])); ?>
                </div>
            </div>

            <!-- Bids Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-800">العروض المقدمة</h3>
                    <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full"><?php echo $bids_count; ?></span>
                </div>
                
                <?php if ($bids_count > 0): ?>
                    <div class="text-center py-4">
                        <!-- رابط لعرض التفاصيل الكاملة والعروض -->
                        <a href="../project_details.php?id=<?php echo $request_id; ?>" class="text-indigo-600 font-bold hover:underline">عرض العروض والتفاصيل الكاملة</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        <p>لا توجد عروض حتى الآن.</p>
                        <p class="text-xs mt-1">سيتم إشعارك فور وصول عروض جديدة.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">معلومات المشروع</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">الميزانية (سعر الساعة)</span>
                        <span class="text-xl font-bold text-indigo-900"><?php echo number_format($project['budget'], 2); ?> <small>ر.س</small></span>
                    </div>
                    
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">المدة المتوقعة</span>
                        <span class="font-medium text-gray-800"><?php echo $project['final_duration']; ?> يوم</span>
                    </div>

                    <?php if (!empty($project['required_skills'])): ?>
                    <div>
                        <span class="block text-xs text-gray-500 mb-2">المهارات المطلوبة</span>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $skills = explode(',', $project['required_skills']);
                            foreach($skills as $skill): 
                            ?>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-md border border-blue-100 font-medium">
                                    <?php echo htmlspecialchars(trim($skill)); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>