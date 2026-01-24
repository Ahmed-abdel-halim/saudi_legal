<?php // dashboard/provider/edit-service.php (تعديل خدمة - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 🔒 الحماية ---
if (!($role == 'owner' && $company_type == 'supplier')) {
    header("Location: /dashboard/index.php?error=unauthorized");
    exit();
}

// --- جلب بيانات الخدمة ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("خطأ: معرف الخدمة غير موجود.");
}
$service_id = $_GET['id'];

// جلب بيانات الخدمة والتأكد من أنها تابعة لهذه الشركة
$stmt_service = $conn->prepare("SELECT * FROM services WHERE service_id = ? AND company_id = ?");
$stmt_service->bind_param("ii", $service_id, $company_id);
$stmt_service->execute();
$service_result = $stmt_service->get_result();

if ($service_result->num_rows == 0) {
    die("خطأ: الخدمة غير موجودة أو لا تملك الصلاحية لتعديلها.");
}
$service = $service_result->fetch_assoc();
$stmt_service->close();
?>

<h1 class="text-3xl font-bold text-white mb-8"><?php echo $L['SUPPLIER_EDIT_TITLE']; ?></h1>

<div class="max-w-3xl mx-auto">
    <div class="bg-slate-card p-8 rounded-lg shadow-lg">
        
        <!-- منطقة الرسائل -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                <?php echo ($_GET['error'] == 'empty_fields') ? $L['ERROR_EMPTY_FIELDS'] : $L['ERROR_GENERIC']; ?>
            </div>
        <?php endif; ?>

        <form action="../../config/update_service_handler.php" method="POST" class="space-y-6">
            
            <!-- !!! إرسال معرف الخدمة بشكل مخفي !!! -->
            <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">

            <div>
                <label for="title" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_LABEL_TITLE']; ?></label>
                <input type="text" name="title" id="title" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal" 
                       value="<?php echo htmlspecialchars($service['title']); ?>" required>
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_LABEL_EXPERT']; ?></label>
                <select name="user_id" id="user_id" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal" required>
                    <option value=""><?php echo $L['SUPPLIER_SELECT_EXPERT']; ?></option>
                    <?php
                    // جلب الموظفين التابعين لهذه الشركة لعرضهم في القائمة
                    $team_stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE company_id = ? AND role = 'employee' AND is_active = 1");
                    $team_stmt->bind_param("i", $company_id);
                    $team_stmt->execute();
                    $team_result = $team_stmt->get_result();
                    while($member = $team_result->fetch_assoc()):
                        // تحديد الموظف المختار حاليًا
                        $selected = ($member['user_id'] == $service['user_id']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $member['user_id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($member['full_name']); ?></option>
                    <?php 
                    endwhile; 
                    $team_stmt->close();
                    ?>
                </select>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_LABEL_DESC']; ?></label>
                <textarea name="description" id="description" rows="5" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal"><?php echo htmlspecialchars($service['description']); ?></textarea>
            </div>

            <div>
                <label for="hourly_rate" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_LABEL_RATE']; ?></label>
                <input type="number" name="hourly_rate" id="hourly_rate" min="1" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal" 
                       value="<?php echo htmlspecialchars($service['hourly_rate']); ?>" required>
            </div>

            <!-- !!! إضافة حقل الحالة !!! -->
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_LABEL_STATUS']; ?></label>
                <div class="flex space-x-6 space-x-reverse">
                    <label class="flex items-center">
                        <input type="radio" name="is_active" value="1" class="ml-2 text-brand-cyan focus:ring-brand-cyan" 
                               <?php echo ($service['is_active'] == 1) ? 'checked' : ''; ?>>
                        <span class="text-white"><?php echo $L['SUPPLIER_STATUS_ACTIVE_DESC']; ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="is_active" value="0" class="ml-2 text-brand-cyan focus:ring-brand-cyan"
                               <?php echo ($service['is_active'] == 0) ? 'checked' : ''; ?>>
                        <span class="text-gray-400"><?php echo $L['SUPPLIER_STATUS_HIDDEN_DESC']; ?></span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4">
                 <a href="my-services.php" class="bg-slate-700 text-white py-3 px-6 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                    <?php echo $L['SUPPLIER_BTN_CANCEL']; ?>
                </a>
                <button type="submit" class="bg-brand-magenta text-white py-3 px-6 rounded-lg font-semibold shadow-lg hover:bg-opacity-90 transition-all">
                    <?php echo $L['SUPPLIER_BTN_UPDATE']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
include '../../layout/dashboard_footer.php'; 
?>