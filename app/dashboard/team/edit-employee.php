<?php // dashboard/team/edit-employee.php (تعديل موظف - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 🔒 الحماية ---
if ($role != 'owner') {
    header("Location: /dashboard/index.php?error=unauthorized");
    exit();
}

// --- جلب بيانات الموظف ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$emp_id = $_GET['id'];

// التأكد من أن الموظف يتبع لنفس الشركة
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND company_id = ? AND role = 'employee'");
$stmt->bind_param("ii", $emp_id, $company_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "<div class='p-10 text-center text-white'>Employee not found.</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}
?>

<h1 class="text-3xl font-bold text-white mb-8"><?php echo $L['TEAM_EDIT_TITLE']; ?></h1>

<div class="max-w-2xl mx-auto">
    <div class="bg-slate-card p-8 rounded-lg shadow-lg border border-slate-700">
        
        <form action="../../config/update_employee_handler.php" method="POST" class="space-y-6">
            <input type="hidden" name="user_id" value="<?php echo $emp_id; ?>">
            
            <div class="flex items-center mb-6">
                <div class="w-16 h-16 rounded-full bg-slate-600 flex items-center justify-center text-2xl font-bold text-white border-2 border-brand-teal">
                    <?php echo mb_substr($employee['full_name'], 0, 1, "UTF-8"); ?>
                </div>
                <div class="<?php echo ($dir=='rtl')?'mr-4':'ml-4'; ?>">
                    <h2 class="text-xl font-bold text-white"><?php echo htmlspecialchars($employee['full_name']); ?></h2>
                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($employee['email']); ?></p>
                </div>
            </div>

            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['TEAM_LABEL_FULL_NAME']; ?></label>
                <input type="text" name="full_name" id="full_name" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" 
                       value="<?php echo htmlspecialchars($employee['full_name']); ?>" required>
            </div>

            <div>
                <label for="job_title" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['TEAM_LABEL_JOB_TITLE']; ?></label>
                <input type="text" name="job_title" id="job_title" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" 
                       value="<?php echo htmlspecialchars($employee['job_title']); ?>">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-3"><?php echo $L['TEAM_LABEL_STATUS']; ?></label>
                <div class="flex space-x-6 <?php echo ($dir=='rtl')?'space-x-reverse':''; ?>">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_active" value="1" class="text-brand-teal focus:ring-brand-teal bg-slate-800 border-gray-600" 
                               <?php echo ($employee['is_active'] == 1) ? 'checked' : ''; ?>>
                        <span class="<?php echo ($dir=='rtl')?'mr-2':'ml-2'; ?> text-white"><?php echo $L['TEAM_STATUS_ACTIVE_DESC']; ?></span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_active" value="0" class="text-red-500 focus:ring-red-500 bg-slate-800 border-gray-600"
                               <?php echo ($employee['is_active'] == 0) ? 'checked' : ''; ?>>
                        <span class="<?php echo ($dir=='rtl')?'mr-2':'ml-2'; ?> text-gray-400"><?php echo $L['TEAM_STATUS_INACTIVE_DESC']; ?></span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-slate-700">
                <a href="index.php" class="bg-slate-700 text-white py-3 px-6 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                    <?php echo $L['BTN_CANCEL']; ?>
                </a>
                <button type="submit" class="bg-brand-magenta text-white py-3 px-6 rounded-lg font-semibold shadow-lg hover:bg-opacity-90 transition-all">
                    <?php echo $L['TEAM_BTN_UPDATE']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
include '../../layout/dashboard_footer.php'; 
?>