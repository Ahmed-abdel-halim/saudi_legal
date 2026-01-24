<?php
// dashboard/provider/create-service.php
include '../../layout/dashboard_header.php';

// التحقق من الصلاحيات
if (!$is_supplier) {
    echo "<div class='p-6 text-red-500'>عذراً، هذه الميزة متاحة للموردين فقط. قم بتفعيلها من الإعدادات.</div>";
    include '../../layout/dashboard_footer.php';
    exit;
}

// جلب الموظفين لتعيينهم للخدمة
$stmt_emp = $conn->prepare("SELECT user_id, full_name FROM users WHERE company_id = ? AND role != 'owner'");
$stmt_emp->bind_param("i", $company_id);
$stmt_emp->execute();
$employees = $stmt_emp->get_result();
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $L['SUPPLIER_CREATE_TITLE']; ?></h1>
        <a href="my-services.php" class="text-sm text-gray-500 hover:text-gray-700">&larr; <?php echo $L['BTN_CANCEL']; ?></a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
        <form action="../../config/create_service_handler.php" method="POST" class="space-y-6">
            
            <!-- Service Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['SUPPLIER_LABEL_TITLE']; ?></label>
                <input type="text" name="title" required placeholder="<?php echo $L['SUPPLIER_PLACEHOLDER_TITLE']; ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-transparent">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['SUPPLIER_LABEL_DESC']; ?></label>
                <textarea name="description" rows="4" required placeholder="<?php echo $L['SUPPLIER_PLACEHOLDER_DESC']; ?>" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-transparent"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Hourly Rate -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['SUPPLIER_LABEL_RATE']; ?></label>
                    <div class="relative">
                        <input type="number" name="rate" min="1" step="0.01" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-transparent ltr:pr-12 rtl:pl-12">
                        <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 pr-3 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">SAR</span>
                        </div>
                    </div>
                </div>

                <!-- Assign Expert -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['SUPPLIER_LABEL_EXPERT']; ?></label>
                    <select name="user_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-teal focus:border-transparent bg-white">
                        <option value=""><?php echo $L['SUPPLIER_SELECT_EXPERT']; ?></option>
                        <!-- المالك يمكنه تقديم نفسه كخبير أيضاً -->
                        <option value="<?php echo $_SESSION['user_id']; ?>"><?php echo $_SESSION['full_name']; ?> (أنا - المالك)</option>
                        <?php while($emp = $employees->fetch_assoc()): ?>
                            <option value="<?php echo $emp['user_id']; ?>"><?php echo htmlspecialchars($emp['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">لا تجد الموظف؟ <a href="../team/index.php" class="text-brand-teal hover:underline">قم بإضافته للفريق أولاً</a>.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-brand-teal hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                    <?php echo $L['SUPPLIER_BTN_SAVE']; ?>
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>