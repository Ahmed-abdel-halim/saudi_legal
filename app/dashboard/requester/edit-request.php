<?php
// dashboard/requester/edit-request.php
// صفحة تعديل طلب موجود

// 1. تضمين الهيدر والتحقق من الجلسة
include '../../layout/dashboard_header.php';

// 2. التحقق من وجود ID في الرابط
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='p-8 text-center text-red-500 font-bold'>خطأ: لم يتم تحديد الطلب المطلوب تعديله.</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}

$request_id = intval($_GET['id']);
$company_id = $_SESSION['company_id'];

// 3. جلب بيانات الطلب من قاعدة البيانات
// نستخدم COALESCE لضمان جلب البيانات الصحيحة بغض النظر عن أسماء الأعمدة (القديمة أو الجديدة)
$sql = "SELECT *, 
        COALESCE(description, scope_description) as final_description,
        COALESCE(duration_hours, requested_duration_hours) as final_duration,
        COALESCE(required_skills, skills_required) as final_skills
        FROM projects 
        WHERE project_id = ? AND requester_company_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("خطأ في قاعدة البيانات: " . $conn->error);
}
$stmt->bind_param("ii", $request_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='p-8 text-center text-gray-500'>عذراً، هذا الطلب غير موجود أو ليس لديك صلاحية لتعديله.</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}

$project = $result->fetch_assoc();

// التحقق من حالة الطلب (لا يمكن تعديل الطلبات المكتملة أو الملغاة)
if ($project['status'] == 'completed' || $project['status'] == 'cancelled') {
    echo "<div class='p-8 text-center text-yellow-600 font-bold'>لا يمكن تعديل هذا الطلب لأنه في حالة (" . ucfirst($project['status']) . ").</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}
?>

<div class="max-w-4xl mx-auto py-8 px-4">
    
    <!-- رأس الصفحة -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            تعديل الطلب: <span class="text-brand-magenta"><?php echo htmlspecialchars($project['title']); ?></span>
        </h1>
        <a href="request-detail.php?id=<?php echo $request_id; ?>" class="text-gray-500 hover:text-gray-700 font-bold text-sm flex items-center gap-1 transition">
            &larr; إلغاء والعودة
        </a>
    </div>

    <!-- عرض رسائل الخطأ القادمة من المعالج -->
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <p class="font-bold">حدث خطأ:</p>
            <p><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
        </div>
    <?php endif; ?>

    <!-- نموذج التعديل -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
        <form action="../../config/update_request_handler.php" method="POST" class="space-y-6">
            
            <!-- حقول مخفية -->
            <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
            
            <!-- العنوان -->
            <div>
                <label for="title" class="block text-sm font-bold text-gray-700 mb-1">عنوان الطلب <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo htmlspecialchars($project['title']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent transition">
            </div>

            <!-- الوصف -->
            <div>
                <label for="description" class="block text-sm font-bold text-gray-700 mb-1">وصف المشروع (Scope) <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="6" required 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent transition"><?php echo htmlspecialchars($project['final_description']); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">صف التعديلات المطلوبة بوضوح.</p>
            </div>

            <!-- المهارات -->
            <div>
                <label for="skills" class="block text-sm font-bold text-gray-700 mb-1">المهارات المطلوبة <span class="text-red-500">*</span></label>
                <input type="text" id="skills" name="skills" required 
                       value="<?php echo htmlspecialchars($project['final_skills']); ?>"
                       placeholder="مثال: PHP, React, Design"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- المدة -->
                <div>
                    <label for="duration" class="block text-sm font-bold text-gray-700 mb-1">المدة المتوقعة (ساعات) <span class="text-red-500">*</span></label>
                    <input type="number" id="duration" name="duration" min="1" required 
                           value="<?php echo intval($project['final_duration']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent transition">
                </div>

                <!-- الميزانية -->
                <div>
                    <label for="budget" class="block text-sm font-bold text-gray-700 mb-1">الميزانية (SAR/ساعة) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" id="budget" name="budget" min="1" step="0.01" required 
                               value="<?php echo floatval($project['budget']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent ltr:pr-12 rtl:pl-16 transition">
                        <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 pr-4 pl-4 flex items-center pointer-events-none text-gray-500 bg-gray-50 rounded-l-none rtl:rounded-r-none rounded-r-lg rtl:rounded-l-lg border-l rtl:border-l-0 rtl:border-r border-gray-300 font-bold text-sm">
                            SAR/hr
                        </div>
                    </div>
                </div>
            </div>

            <!-- خيار إغلاق الطلب -->
            <?php if($project['status'] == 'open'): ?>
            <div class="pt-4 border-t border-gray-100">
                <label class="flex items-center cursor-pointer p-3 rounded hover:bg-gray-50 transition border border-transparent hover:border-gray-200">
                    <input type="checkbox" name="close_request" value="1" class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <div class="mr-3 rtl:mr-3 rtl:ml-0 ml-3">
                        <span class="block text-sm font-bold text-gray-800">إغلاق هذا الطلب</span>
                        <span class="block text-xs text-gray-500">لن يستقبل الطلب أي عروض جديدة وسيتحول إلى حالة "ملغي".</span>
                    </div>
                </label>
            </div>
            <?php endif; ?>

            <!-- أزرار التحكم -->
            <div class="pt-6 border-t border-gray-100 flex justify-end items-center gap-4">
                <a href="request-detail.php?id=<?php echo $request_id; ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition">إلغاء</a>
                <button type="submit" class="bg-brand-magenta hover:bg-fuchsia-700 text-white font-bold py-2 px-8 rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                    حفظ التغييرات
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>