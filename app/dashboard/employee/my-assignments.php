<?php // dashboard/employee/my-assignments.php (مهماتي - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 🔒 الحماية ---
// التحقق من أن المستخدم مسجل دخول وأنه موظف
if ($role != 'employee') {
    header("Location: /dashboard/index.php?error=unauthorized");
    exit();
}
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-white"><?php echo $L['EMP_ASSIGNMENTS_TITLE']; ?></h1>
    <!-- زر مستقبلي يمكن إضافته هنا -->
</div>

<!-- منطقة الرسائل -->
<?php if (isset($_GET['success']) && $_GET['success'] == 'time_logged'): ?>
    <div class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6 shadow-sm border border-green-500/30">
        <i class="fas fa-check-circle <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
        <?php echo $L['EMP_MSG_LOGGED']; ?>
    </div>
<?php endif; ?>

<!-- جدول المهام -->
<div class="bg-slate-card p-6 rounded-lg shadow-lg">
    <div class="overflow-x-auto">
        <table class="w-full <?php echo ($dir == 'rtl') ? 'text-right' : 'text-left'; ?>">
            <thead>
                <tr class="border-b border-slate-700 text-sm text-gray-400">
                    <th class="py-3 <?php echo ($dir=='rtl')?'pl-4':'pr-4'; ?>"><?php echo $L['EMP_TABLE_PROJECT']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['EMP_TABLE_ROLE']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['EMP_TABLE_START_DATE']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['EMP_TABLE_STATUS']; ?></th>
                    <th class="py-3 <?php echo ($dir=='rtl')?'pr-4':'pl-4'; ?>"><?php echo $L['EMP_TABLE_ACTION']; ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                <?php
                // جلب المشاريع المسندة لهذا الموظف
                $stmt = $conn->prepare("SELECT p.project_id, p.title, p.start_date, p.status 
                                        FROM projects p 
                                        WHERE p.expert_user_id = ? AND p.status = 'active'");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td class="py-4 <?php echo ($dir=='rtl')?'pl-4':'pr-4'; ?> font-medium text-white">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </td>
                    <td class="py-4 px-4 text-gray-400">Expert</td>
                    <td class="py-4 px-4 text-gray-400"><?php echo htmlspecialchars($row['start_date']); ?></td>
                    <td class="py-4 px-4">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">
                            <?php echo $L['ADMIN_STATUS_ACTIVE']; ?>
                        </span>
                    </td>
                    <td class="py-4 <?php echo ($dir=='rtl')?'pr-4':'pl-4'; ?>">
                        <button onclick="openLogModal(<?php echo $row['project_id']; ?>, '<?php echo htmlspecialchars($row['title']); ?>')" 
                                class="bg-brand-teal/10 text-brand-teal hover:bg-brand-teal hover:text-brand-navy px-4 py-2 rounded-lg text-sm font-semibold transition-all">
                            <i class="fas fa-clock <?php echo ($dir=='rtl')?'ml-1':'mr-1'; ?>"></i>
                            <?php echo $L['EMP_BTN_LOG_TIME']; ?>
                        </button>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-clipboard-list text-4xl mb-3 opacity-50"></i>
                            <p><?php echo $L['EMP_NO_ASSIGNMENTS']; ?></p>
                        </div>
                    </td>
                </tr>
                <?php 
                endif; 
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- نافذة تسجيل الوقت (Modal) -->
<div id="logTimeModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-slate-card p-8 rounded-xl shadow-2xl w-full max-w-md border border-slate-600 transform transition-all scale-95 opacity-0" id="modalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white"><?php echo $L['EMP_LOG_TIME_MODAL_TITLE']; ?></h3>
            <button onclick="closeLogModal()" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="../../config/timesheet_handler.php" method="POST" class="space-y-5">
            <input type="hidden" name="project_id" id="modal_project_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_TABLE_PROJECT']; ?></label>
                <input type="text" id="modal_project_title" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-gray-500 cursor-not-allowed" readonly>
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_LABEL_DATE']; ?></label>
                <input type="date" name="date" id="date" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal" required value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div>
                <label for="hours" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_LABEL_HOURS']; ?></label>
                <input type="number" name="hours" id="hours" step="0.5" min="0.5" max="24" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal" required placeholder="8.0">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_LABEL_NOTES']; ?></label>
                <textarea name="description" id="description" rows="3" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal" placeholder="<?php echo $L['EMP_PLACEHOLDER_NOTES']; ?>"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-brand-teal text-brand-navy py-3 rounded-lg font-bold hover:bg-opacity-90 transition-all shadow-lg shadow-brand-teal/20">
                    <?php echo $L['EMP_BTN_SUBMIT_TIMESHEET']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openLogModal(projectId, projectTitle) {
    document.getElementById('modal_project_id').value = projectId;
    document.getElementById('modal_project_title').value = projectTitle;
    
    const modal = document.getElementById('logTimeModal');
    const content = document.getElementById('modalContent');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Animation
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeLogModal() {
    const modal = document.getElementById('logTimeModal');
    const content = document.getElementById('modalContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

// Close modal when clicking outside
document.getElementById('logTimeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogModal();
    }
});
</script>

<?php 
include '../../layout/dashboard_footer.php'; 
?>