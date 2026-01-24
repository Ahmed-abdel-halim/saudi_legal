<?php // dashboard/employee/availability.php (جدول التفرغ - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 🔒 الحماية ---
if ($role != 'employee') {
    header("Location: /dashboard/index.php?error=unauthorized");
    exit();
}

// --- جلب البيانات الحالية (محاكاة) ---
// في التطبيق الحقيقي، ستجلب هذه البيانات من جدول `availability` أو حقل JSON في `users`
// هنا سنفترض وجود مصفوفة افتراضية إذا لم تكن موجودة في قاعدة البيانات
$days = [
    'sunday'    => ['label' => $L['EMP_DAY_SUNDAY'], 'status' => 1],
    'monday'    => ['label' => $L['EMP_DAY_MONDAY'], 'status' => 1],
    'tuesday'   => ['label' => $L['EMP_DAY_TUESDAY'], 'status' => 1],
    'wednesday' => ['label' => $L['EMP_DAY_WEDNESDAY'], 'status' => 1],
    'thursday'  => ['label' => $L['EMP_DAY_THURSDAY'], 'status' => 1],
    'friday'    => ['label' => $L['EMP_DAY_FRIDAY'], 'status' => 0], // عطلة افتراضية
    'saturday'  => ['label' => $L['EMP_DAY_SATURDAY'], 'status' => 0]  // عطلة افتراضية
];
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8 text-center md:text-left <?php echo ($dir=='rtl')?'md:text-right':''; ?>">
        <h1 class="text-3xl font-bold text-white mb-2"><?php echo $L['EMP_AVAILABILITY_TITLE']; ?></h1>
        <p class="text-gray-400"><?php echo $L['EMP_AVAILABILITY_DESC']; ?></p>
        <p class="text-sm text-gray-500 mt-1"><?php echo $L['EMP_AVAILABILITY_SUB_DESC']; ?></p>
    </div>

    <!-- رسالة نجاح -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
        <div class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle text-xl <?php echo ($dir=='rtl')?'ml-3':'mr-3'; ?>"></i>
            <?php echo $L['EMP_MSG_AVAILABILITY_UPDATED']; ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-card p-8 rounded-xl shadow-lg border border-slate-700">
        <form action="../../config/availability_handler.php" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <?php foreach ($days as $key => $day): ?>
                <div class="flex items-center justify-between p-4 bg-dark-navy rounded-lg border border-slate-700 hover:border-brand-teal/50 transition-colors">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-slate-700 flex items-center justify-center text-gray-300 font-bold <?php echo ($dir=='rtl')?'ml-4':'mr-4'; ?>">
                            <?php echo mb_substr($day['label'], 0, 1, "UTF-8"); ?>
                        </div>
                        <span class="text-lg font-medium text-white"><?php echo $day['label']; ?></span>
                    </div>
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="days[<?php echo $key; ?>]" value="1" class="sr-only peer" <?php echo ($day['status'] == 1) ? 'checked' : ''; ?>>
                        <div class="w-14 h-7 bg-slate-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-brand-teal"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400 peer-checked:text-brand-teal absolute <?php echo ($dir=='rtl')?'right-16':'left-16'; ?> w-24">
                            <?php // النص يتغير بناءً على CSS أو JS، هنا سنبسطه ?>
                        </span>
                    </label>
                </div>
                <?php endforeach; ?>

            </div>

            <div class="mt-10 flex justify-end border-t border-slate-700 pt-6">
                <button type="submit" class="bg-brand-magenta text-white py-3 px-8 rounded-lg font-bold shadow-lg hover:bg-opacity-90 transition-all transform hover:-translate-y-1">
                    <?php echo $L['EMP_BTN_UPDATE_AVAILABILITY']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
include '../../layout/dashboard_footer.php'; 
?>