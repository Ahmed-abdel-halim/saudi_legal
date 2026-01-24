<?php
// dashboard/settings/index.php
include '../../layout/dashboard_header.php';

// معالجة التحديث
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_modes'])) {
    $req_mode = isset($_POST['is_requester']) ? 1 : 0;
    $sup_mode = isset($_POST['is_supplier']) ? 1 : 0;
    
    // يجب أن يبقى واحد على الأقل مفعلاً
    if ($req_mode == 0 && $sup_mode == 0) $req_mode = 1;

    $stmt = $conn->prepare("UPDATE companies SET is_requester = ?, is_supplier = ? WHERE company_id = ?");
    $stmt->bind_param("iii", $req_mode, $sup_mode, $company_id);
    
    if ($stmt->execute()) {
        echo "<script>window.location.href='index.php?success=1';</script>";
        exit;
    }
}
?>

<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $L['SETTINGS_TITLE']; ?></h1>

    <?php if(isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded mb-6 text-sm font-bold">
            <?php echo $L['MSG_UPDATED']; ?>
        </div>
    <?php endif; ?>

    <!-- Business Mode Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><?php echo $L['SETTINGS_MODES_TITLE']; ?></h2>
        
        <form method="POST">
            <input type="hidden" name="update_modes" value="1">
            
            <div class="space-y-4">
                <!-- Requester Toggle -->
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition <?php echo $is_requester ? 'border-brand-magenta ring-1 ring-brand-magenta bg-fuchsia-50' : 'border-gray-200'; ?>">
                    <input type="checkbox" name="is_requester" value="1" <?php echo $is_requester ? 'checked' : ''; ?> class="w-5 h-5 text-brand-magenta rounded focus:ring-brand-magenta">
                    <div class="ml-4 rtl:mr-4">
                        <span class="block font-bold text-gray-900"><?php echo $L['SETTINGS_MODE_REQUESTER']; ?></span>
                        <span class="block text-sm text-gray-500">تمكنك من نشر الطلبات، توظيف الخبراء، ودفع الفواتير.</span>
                    </div>
                </label>

                <!-- Supplier Toggle -->
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition <?php echo $is_supplier ? 'border-brand-teal ring-1 ring-brand-teal bg-teal-50' : 'border-gray-200'; ?>">
                    <input type="checkbox" name="is_supplier" value="1" <?php echo $is_supplier ? 'checked' : ''; ?> class="w-5 h-5 text-brand-teal rounded focus:ring-brand-teal">
                    <div class="ml-4 rtl:mr-4">
                        <span class="block font-bold text-gray-900"><?php echo $L['SETTINGS_MODE_SUPPLIER']; ?></span>
                        <span class="block text-sm text-gray-500">تمكنك من إنشاء الخدمات، تقديم العروض، واستلام الأرباح.</span>
                    </div>
                </label>
            </div>

            <div class="mt-6 text-right rtl:text-left">
                <button type="submit" class="bg-brand-dark text-white px-6 py-2 rounded-lg font-bold hover:bg-slate-800 transition">
                    <?php echo $L['SETTINGS_SAVE']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>