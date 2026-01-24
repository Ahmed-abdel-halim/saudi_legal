<?php // dashboard/profile/index.php (صفحة الملف الشخصي - مترجمة) ?>
<?php 
// الهيدر سيتحقق من المصادقة ويحدد اللغة ($L) والاتجاه ($dir)
include '../../layout/dashboard_header.php'; 

// جلب بيانات المستخدم الحالية
$user_data = $conn->prepare("SELECT full_name, email, job_title, profile_image_url FROM users WHERE user_id = ?");
$user_data->bind_param("i", $user_id);
$user_data->execute();
$user = $user_data->get_result()->fetch_assoc();

// تعيين صورة افتراضية إذا لم تكن موجودة
$profile_image = !empty($user['profile_image_url']) ? $user['profile_image_url'] : 'https://placehold.co/150x150/E2E8F0/334155?text=' . strtoupper(substr($user['full_name'], 0, 1));
?>

<h1 class="text-3xl font-bold text-white mb-8"><?php echo $L['PROFILE_TITLE']; ?></h1>

<div class="max-w-4xl mx-auto">
    
    <!-- منطقة الرسائل -->
    <div id="message" class="text-center text-sm mb-6">
        <?php
            if (isset($_GET['profile_success'])) {
                echo '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg border border-green-500/30">' . $L['PROFILE_MSG_UPDATE_SUCCESS'] . '</div>';
            }
            if (isset($_GET['profile_error'])) {
                echo '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg border border-red-500/30">' . htmlspecialchars($_GET['profile_error']) . '</div>';
            }
            if (isset($_GET['password_success'])) {
                echo '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg border border-green-500/30">' . $L['PROFILE_MSG_PASS_SUCCESS'] . '</div>';
            }
            if (isset($_GET['password_error'])) {
                echo '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg border border-red-500/30">' . htmlspecialchars($_GET['password_error']) . '</div>';
            }
        ?>
    </div>

    <!-- نموذج تحديث الملف الشخصي -->
    <div class="bg-slate-card p-8 rounded-lg shadow-lg border border-slate-700">
        <form action="../../config/update_profile_handler.php" method="POST" enctype="multipart/form-data">
            
            <!-- معلومات الملف الشخصي -->
            <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 <?php echo ($dir=='rtl')?'md:space-x-reverse md:space-x-6':'md:space-x-6'; ?> border-b border-slate-700 pb-8 mb-8">
                <div class="relative group">
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="w-24 h-24 rounded-full bg-slate-600 object-cover shadow-lg border-2 border-slate-500">
                    <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <i class="fas fa-camera text-white"></i>
                    </div>
                </div>
                
                <div class="flex-1 w-full text-center md:text-<?php echo ($dir=='rtl')?'right':'left'; ?>">
                    <label for="profile_image" class="block text-sm font-medium text-gray-300 mb-2"><?php echo $L['PROFILE_CHANGE_IMAGE']; ?></label>
                    <input type="file" name="profile_image" id="profile_image" class="block w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-semibold
                        file:bg-brand-teal file:text-brand-navy
                        hover:file:bg-opacity-80
                        cursor-pointer file:cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF. Max 2MB.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['PROFILE_LABEL_FULL_NAME']; ?></label>
                    <input type="text" name="full_name" id="full_name" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['PROFILE_LABEL_EMAIL']; ?></label>
                    <input type="email" name="email" id="email" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-gray-400 cursor-not-allowed" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                </div>
                <div class="md:col-span-2">
                    <label for="job_title" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['PROFILE_LABEL_JOB_TITLE']; ?></label>
                    <input type="text" name="job_title" id="job_title" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" value="<?php echo htmlspecialchars($user['job_title']); ?>">
                </div>
            </div>
            
            <div class="flex justify-end mt-8 pt-6 border-t border-slate-700">
                <button type="submit" name="update_profile" class="bg-brand-magenta text-white py-3 px-8 rounded-lg font-bold shadow-lg hover:bg-opacity-90 transition-all transform hover:-translate-y-1">
                    <?php echo $L['PROFILE_BTN_SAVE_CHANGES']; ?>
                </button>
            </div>
        </form>
    </div>

    <!-- قسم تغيير كلمة المرور -->
    <div class="bg-slate-card p-8 rounded-lg shadow-lg mt-10 border border-slate-700">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-lock text-brand-teal <?php echo ($dir=='rtl')?'ml-3':'mr-3'; ?>"></i>
            <?php echo $L['PROFILE_CHANGE_PASSWORD_TITLE']; ?>
        </h3>
        
        <form action="../../config/update_password_handler.php" method="POST" class="space-y-5 max-w-2xl">
             <div>
                <label for="current_password" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['PROFILE_LABEL_CURRENT_PASS']; ?></label>
                <input type="password" name="current_password" id="current_password" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" required>
            </div>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['PROFILE_LABEL_NEW_PASS']; ?></label>
                    <input type="password" name="new_password" id="new_password" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" required>
                </div>
                 <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['PROFILE_LABEL_CONFIRM_PASS']; ?></label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-2 focus:ring-brand-teal transition-all" required>
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                <button type="submit" name="update_password" class="bg-slate-700 hover:bg-slate-600 text-white py-3 px-6 rounded-lg font-semibold transition-all">
                    <?php echo $L['PROFILE_BTN_CHANGE_PASS']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
include '../../layout/dashboard_footer.php'; 
?>