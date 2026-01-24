<?php // dashboard/projects/raise-dispute.php (رفع نزاع - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// التحقق من معرف المشروع
if (!isset($_GET['project_id']) || !is_numeric($_GET['project_id'])) {
    header("Location: /dashboard/index.php");
    exit();
}
$project_id = $_GET['project_id'];
?>

<div class="max-w-2xl mx-auto mt-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-500/10 mb-4">
            <i class="fas fa-gavel text-3xl text-red-500"></i>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2"><?php echo $L['DISPUTE_TITLE']; ?></h1>
        <p class="text-gray-400"><?php echo $L['DISPUTE_SUBTITLE']; ?></p>
    </div>

    <!-- رسالة نجاح -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 'submitted'): ?>
        <div class="bg-green-500/20 text-green-400 p-6 rounded-xl text-center border border-green-500/30 mb-6 shadow-lg">
            <i class="fas fa-check-circle text-4xl mb-3"></i>
            <p class="font-bold"><?php echo $L['DISPUTE_MSG_SUBMITTED']; ?></p>
            <a href="workspace.php?id=<?php echo $project_id; ?>" class="inline-block mt-4 text-sm text-white underline hover:text-green-300">
                <?php echo $L['BTN_BACK_HOME']; // أو "العودة للمشروع" ?>
            </a>
        </div>
    <?php else: ?>

    <div class="bg-slate-card p-8 rounded-xl shadow-2xl border border-slate-700">
        <form action="../../config/dispute_handler.php" method="POST" class="space-y-6">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-3"><?php echo $L['DISPUTE_LABEL_REASON']; ?></label>
                <div class="space-y-3">
                    <label class="flex items-center p-3 border border-slate-600 rounded-lg hover:bg-slate-700 cursor-pointer transition-colors">
                        <input type="radio" name="reason" value="quality" class="text-red-500 focus:ring-red-500 bg-slate-800 border-gray-500" required>
                        <span class="ml-3 mr-3 text-white"><?php echo $L['DISPUTE_REASON_QUALITY']; ?></span>
                    </label>
                    <label class="flex items-center p-3 border border-slate-600 rounded-lg hover:bg-slate-700 cursor-pointer transition-colors">
                        <input type="radio" name="reason" value="delay" class="text-red-500 focus:ring-red-500 bg-slate-800 border-gray-500">
                        <span class="ml-3 mr-3 text-white"><?php echo $L['DISPUTE_REASON_DELAY']; ?></span>
                    </label>
                    <label class="flex items-center p-3 border border-slate-600 rounded-lg hover:bg-slate-700 cursor-pointer transition-colors">
                        <input type="radio" name="reason" value="communication" class="text-red-500 focus:ring-red-500 bg-slate-800 border-gray-500">
                        <span class="ml-3 mr-3 text-white"><?php echo $L['DISPUTE_REASON_COMMUNICATION']; ?></span>
                    </label>
                    <label class="flex items-center p-3 border border-slate-600 rounded-lg hover:bg-slate-700 cursor-pointer transition-colors">
                        <input type="radio" name="reason" value="other" class="text-red-500 focus:ring-red-500 bg-slate-800 border-gray-500">
                        <span class="ml-3 mr-3 text-white"><?php echo $L['DISPUTE_REASON_OTHER']; ?></span>
                    </label>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2"><?php echo $L['DISPUTE_LABEL_DESC']; ?></label>
                <textarea name="description" id="description" rows="5" 
                          class="w-full bg-dark-navy border border-slate-600 rounded-lg p-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all" 
                          placeholder="<?php echo $L['DISPUTE_PLACEHOLDER_DESC']; ?>" required></textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-700">
                <a href="workspace.php?id=<?php echo $project_id; ?>" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">
                    <?php echo $L['BTN_CANCEL']; ?>
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition-all transform hover:-translate-y-1">
                    <?php echo $L['DISPUTE_BTN_SUBMIT']; ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php 
include '../../layout/dashboard_footer.php'; 
?>