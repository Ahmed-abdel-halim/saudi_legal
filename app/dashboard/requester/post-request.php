<?php
// dashboard/requester/post-request.php
include '../../layout/dashboard_header.php';

// التحقق من الصلاحيات
if (!$is_requester) {
    echo "<div class='p-6 text-red-500'>عذراً، هذه الميزة متاحة للشركات الطالبة فقط. قم بتفعيلها من الإعدادات.</div>";
    include '../../layout/dashboard_footer.php';
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $L['POST_REQ_TITLE']; ?></h1>
        <a href="my-requests.php" class="text-sm text-gray-500 hover:text-gray-700">&larr; <?php echo $L['BTN_CANCEL']; ?></a>
    </div>

    <!-- Steps Indicator (Static) -->
    <div class="flex justify-between items-center mb-8 px-4 max-w-2xl mx-auto">
        <div class="flex items-center text-brand-magenta font-bold">
            <span class="w-8 h-8 rounded-full bg-brand-magenta text-white flex items-center justify-center mr-2">1</span>
            <?php echo $L['POST_REQ_STEP_1']; ?>
        </div>
        <div class="h-1 flex-1 bg-gray-200 mx-4"></div>
        <div class="flex items-center text-gray-400">
            <span class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">2</span>
            <?php echo $L['POST_REQ_STEP_2']; ?>
        </div>
        <div class="h-1 flex-1 bg-gray-200 mx-4"></div>
        <div class="flex items-center text-gray-400">
            <span class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">3</span>
            <?php echo $L['POST_REQ_STEP_3']; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
        <form action="../../config/post_request_handler.php" method="POST" class="space-y-6">
            
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['POST_REQ_LABEL_TITLE']; ?></label>
                <input type="text" name="title" required placeholder="<?php echo $L['POST_REQ_PLACEHOLDER_TITLE']; ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent">
            </div>

            <!-- Scope / Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['POST_REQ_LABEL_SCOPE']; ?></label>
                <textarea name="description" rows="5" required placeholder="<?php echo $L['POST_REQ_PLACEHOLDER_SCOPE']; ?>" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent"></textarea>
            </div>

            <!-- Skills -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['POST_REQ_LABEL_SKILLS']; ?></label>
                <input type="text" name="skills" required placeholder="<?php echo $L['POST_REQ_PLACEHOLDER_SKILLS']; ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['POST_REQ_LABEL_DURATION']; ?></label>
                    <input type="number" name="duration" min="1" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent">
                </div>

                <!-- Budget -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $L['POST_REQ_LABEL_MAX_RATE']; ?></label>
                    <div class="relative">
                        <input type="number" name="budget" min="1" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-magenta focus:border-transparent ltr:pr-12 rtl:pl-12">
                        <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 pr-3 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">SAR/hr</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-brand-magenta hover:bg-fuchsia-700 text-white font-bold py-3 px-8 rounded-lg shadow transition transform hover:-translate-y-0.5">
                    <?php echo $L['POST_REQ_BTN_SUBMIT']; ?>
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>