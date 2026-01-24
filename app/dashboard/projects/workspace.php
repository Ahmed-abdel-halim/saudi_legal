<?php // dashboard/projects/workspace.php (غرفة العمل الكاملة - إصلاح عرض الملفات) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 1. تضمين مكتبة Alpine.js ---
?>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
<?php

// --- 🔒 التحقق من المعرف ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href='/dashboard/index.php';</script>";
    exit();
}
$project_id = (int)$_GET['id'];

// 2. جلب بيانات المشروع
$stmt_project = $conn->prepare("
    SELECT p.*, c.conversation_id, 
           req.name as requester_name, 
           sup.name as supplier_name, 
           exp.full_name as expert_name
    FROM projects p 
    LEFT JOIN conversations c ON p.project_id = c.project_id
    LEFT JOIN companies req ON p.requester_company_id = req.company_id
    LEFT JOIN companies sup ON p.supplier_company_id = sup.company_id
    LEFT JOIN users exp ON p.expert_user_id = exp.user_id
    WHERE p.project_id = ?
");
$stmt_project->bind_param("i", $project_id);
$stmt_project->execute();
$project = $stmt_project->get_result()->fetch_assoc();
$stmt_project->close();

if (!$project) {
    echo "<div class='p-10 text-center text-white'>".$L['ERROR_PROJECT_NOT_FOUND']."</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}

// 3. التحقق من الصلاحية (Security Check)
$is_participant = false;
if ($role == 'owner' && $company_id == $project['requester_company_id']) $is_participant = true;
if ($role == 'owner' && $company_id == $project['supplier_company_id']) $is_participant = true;
if ($role == 'employee' && $user_id == $project['expert_user_id']) $is_participant = true;
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) $is_participant = true;

if (!$is_participant) {
    echo "<script>window.location.href='/dashboard/index.php?error=unauthorized';</script>";
    exit();
}

// 4. جلب سجل الساعات (Timesheets)
$timesheets = [];
$total_hours_approved = 0;
// التحقق من وجود الجدول لتجنب الأخطاء إذا لم يتم إنشاؤه بعد
$check_ts = $conn->query("SHOW TABLES LIKE 'timesheets'");
if ($check_ts && $check_ts->num_rows > 0) {
    $stmt_ts = $conn->prepare("SELECT t.*, u.full_name FROM timesheets t JOIN users u ON t.user_id = u.user_id WHERE t.project_id = ? ORDER BY t.work_date DESC");
    $stmt_ts->bind_param("i", $project_id);
    $stmt_ts->execute();
    $res_ts = $stmt_ts->get_result();
    while($row = $res_ts->fetch_assoc()) {
        $timesheets[] = $row;
        if ($row['status'] == 'approved') $total_hours_approved += $row['hours_logged'];
    }
    $stmt_ts->close();
}

// 5. [إصلاح] جلب الملفات الفعلية من قاعدة البيانات (Project Files)
$files = [];
$check_files = $conn->query("SHOW TABLES LIKE 'project_files'");
if ($check_files && $check_files->num_rows > 0) {
    // جلب الملفات مع اسم الرافع
    $stmt_files = $conn->prepare("
        SELECT f.*, u.full_name as uploader_name 
        FROM project_files f 
        JOIN users u ON f.user_id = u.user_id 
        WHERE f.project_id = ? 
        ORDER BY f.uploaded_at DESC
    ");
    $stmt_files->bind_param("i", $project_id);
    $stmt_files->execute();
    $res_files = $stmt_files->get_result();
    
    while($row = $res_files->fetch_assoc()) {
        // تنسيق الحجم (Bytes -> KB/MB)
        $size = $row['file_size'];
        if ($size >= 1048576) {
            $formatted_size = number_format($size / 1048576, 2) . ' MB';
        } else {
            $formatted_size = number_format($size / 1024, 0) . ' KB';
        }

        // تصحيح مسار الملف للعرض (لأن المسار في القاعدة نسبي لمجلد config)
        // المسار في القاعدة يبدأ بـ ../uploads
        // نحن هنا في dashboard/projects نحتاج للرجوع خطوتين للوصول لـ uploads
        // لذا نستبدل ../ بـ ../../
        $display_path = str_replace('../uploads', '../../uploads', $row['file_path']);

        $files[] = [
            'id' => $row['id'],
            'name' => $row['file_name'],
            'type' => strtoupper(pathinfo($row['file_name'], PATHINFO_EXTENSION)), // استخراج النوع من الاسم
            'size' => $formatted_size,
            'uploaded_by' => $row['uploader_name'],
            'date' => $row['uploaded_at'],
            'path' => $display_path
        ];
    }
    $stmt_files->close();
}

$hours_remaining = $project['requested_duration_hours'] - $total_hours_approved;
$progress_percentage = ($project['requested_duration_hours'] > 0) ? ($total_hours_approved / $project['requested_duration_hours']) * 100 : 0;
?>

<!-- Header & Actions -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($project['title']); ?></h1>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-500/20 text-green-400 border border-green-500/30">
                <?php echo $L['PROJ_STATUS_ACTIVE']; ?>
            </span>
        </div>
        <p class="text-gray-400">#<?php echo str_pad($project['project_id'], 5, '0', STR_PAD_LEFT); ?></p>
    </div>
    
    <div class="flex gap-3">
        <a href="raise-dispute.php?project_id=<?php echo $project_id; ?>" class="px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/20 transition-colors text-sm font-medium flex items-center">
            <i class="fas fa-exclamation-triangle <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
            <?php echo $L['PROJ_BTN_RAISE_DISPUTE']; ?>
        </a>
        <?php if ($role == 'owner' && $company_type == 'requester'): ?>
        <button onclick="confirmComplete()" class="px-4 py-2 bg-brand-teal text-brand-navy rounded-lg hover:bg-opacity-90 transition-colors text-sm font-bold flex items-center">
            <i class="fas fa-check <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
            <?php echo $L['PROJ_BTN_COMPLETE']; ?>
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-slate-card p-5 rounded-xl border border-slate-700">
        <h3 class="text-gray-400 text-xs font-medium uppercase mb-2"><?php echo $L['PROJ_DETAILS_BUDGET']; ?></h3>
        <p class="text-2xl font-bold text-white"><?php echo number_format($project['total_amount'], 2); ?> <span class="text-sm font-normal text-gray-500">SAR</span></p>
    </div>
    <div class="bg-slate-card p-5 rounded-xl border border-slate-700">
        <h3 class="text-gray-400 text-xs font-medium uppercase mb-2"><?php echo $L['PROJ_TS_TOTAL_LOGGED']; ?></h3>
        <p class="text-2xl font-bold text-brand-cyan"><?php echo number_format($total_hours_approved, 1); ?> <span class="text-sm font-normal text-gray-500">Hrs</span></p>
    </div>
    <div class="bg-slate-card p-5 rounded-xl border border-slate-700">
        <h3 class="text-gray-400 text-xs font-medium uppercase mb-2"><?php echo $L['PROJ_DETAILS_START_DATE']; ?></h3>
        <p class="text-xl font-bold text-white"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></p>
    </div>
    <div class="bg-slate-card p-5 rounded-xl border border-slate-700 flex items-center justify-center">
        <div class="text-center w-full">
            <div class="flex justify-between mb-1">
                <span class="text-xs font-medium text-gray-400">Progress</span>
                <span class="text-xs font-medium text-brand-magenta"><?php echo number_format($progress_percentage, 0); ?>%</span>
            </div>
            <div class="w-full bg-dark-navy h-2 rounded-full">
                <div class="bg-brand-magenta h-2 rounded-full" style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs System (Using Alpine.js) -->
<div x-data="{ currentTab: 'overview' }" class="bg-slate-card rounded-xl shadow-lg overflow-hidden border border-slate-700 min-h-[500px]">
    
    <!-- Tabs Header -->
    <div class="flex border-b border-slate-700 bg-slate-800/50 overflow-x-auto">
        <button @click="currentTab = 'overview'" 
                :class="{ 'border-b-2 border-brand-teal text-brand-teal bg-slate-800': currentTab === 'overview', 'text-gray-400 hover:text-white hover:bg-slate-700': currentTab !== 'overview' }"
                class="px-6 py-4 text-sm font-medium transition-colors focus:outline-none flex items-center whitespace-nowrap">
            <i class="fas fa-info-circle <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
            <?php echo $L['PROJ_TAB_OVERVIEW']; ?>
        </button>
        
        <button @click="currentTab = 'timesheets'" 
                :class="{ 'border-b-2 border-brand-teal text-brand-teal bg-slate-800': currentTab === 'timesheets', 'text-gray-400 hover:text-white hover:bg-slate-700': currentTab !== 'timesheets' }"
                class="px-6 py-4 text-sm font-medium transition-colors focus:outline-none flex items-center whitespace-nowrap">
            <i class="fas fa-clock <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
            <?php echo $L['PROJ_TAB_TIMESHEETS']; ?>
        </button>
        
        <button @click="currentTab = 'files'" 
                :class="{ 'border-b-2 border-brand-teal text-brand-teal bg-slate-800': currentTab === 'files', 'text-gray-400 hover:text-white hover:bg-slate-700': currentTab !== 'files' }"
                class="px-6 py-4 text-sm font-medium transition-colors focus:outline-none flex items-center whitespace-nowrap">
            <i class="fas fa-folder-open <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
            <?php echo $L['PROJ_TAB_FILES']; ?>
        </button>
        
        <a href="../messages/index.php?convo_id=<?php echo $project['conversation_id']; ?>" 
           class="px-6 py-4 text-sm font-medium text-gray-400 hover:text-white hover:bg-slate-700 transition-colors focus:outline-none flex items-center whitespace-nowrap">
            <i class="fas fa-comment-alt <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
            <?php echo $L['PROJ_TAB_MESSAGES']; ?>
        </a>
    </div>

    <!-- Tabs Content -->
    <div class="p-8">

        <!-- 1. Overview Tab -->
        <div x-show="currentTab === 'overview'" x-transition.opacity>
            
            <!-- Project Summary Section -->
            <div class="bg-slate-700/20 rounded-xl p-6 border border-slate-700 mb-8">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-align-left text-brand-teal <?php echo ($dir=='rtl')?'ml-3':'mr-3'; ?>"></i>
                    <?php echo $L['PROJ_SUMMARY_HEADER']; ?>
                </h3>
                <div class="text-gray-300 leading-relaxed whitespace-pre-wrap">
                    <?php 
                        // عرض الوصف (مع دعم fallback للحقول المختلفة)
                        $desc = !empty($project['description']) ? $project['description'] : ($project['scope_description'] ?? '');
                        echo nl2br(htmlspecialchars($desc)); 
                    ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Parties Involved -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 border-b border-slate-700 pb-2">الأطراف المعنية</h4>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-center justify-between p-3 bg-slate-700/20 rounded-lg hover:bg-slate-700/40 transition">
                            <span class="text-gray-400 flex items-center"><i class="fas fa-building <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i> <?php echo $L['LEGAL_REQUESTER_TITLE']; ?></span>
                            <span class="text-white font-medium"><?php echo htmlspecialchars($project['requester_name']); ?></span>
                        </li>
                        <li class="flex items-center justify-between p-3 bg-slate-700/20 rounded-lg hover:bg-slate-700/40 transition">
                            <span class="text-gray-400 flex items-center"><i class="fas fa-industry <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i> <?php echo $L['LEGAL_SUPPLIER_TITLE']; ?></span>
                            <span class="text-white font-medium"><?php echo htmlspecialchars($project['supplier_name']); ?></span>
                        </li>
                        <li class="flex items-center justify-between p-3 bg-slate-700/20 rounded-lg hover:bg-slate-700/40 transition">
                            <span class="text-gray-400 flex items-center"><i class="fas fa-user-tie <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i> <?php echo $L['LEGAL_EXPERT_TITLE']; ?></span>
                            <span class="text-brand-cyan font-bold"><?php echo htmlspecialchars($project['expert_name']); ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Timeline -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 border-b border-slate-700 pb-2">سجل النشاطات</h4>
                    <div class="relative border-<?php echo ($dir=='rtl')?'r':'l'; ?> border-slate-700 ml-3 <?php echo ($dir=='rtl')?'mr-3 ml-0':'ml-3 mr-0'; ?> space-y-8">
                        <div class="relative <?php echo ($dir=='rtl')?'mr-6':'ml-6'; ?>">
                            <span class="absolute -<?php echo ($dir=='rtl')?'right-[33px]':'left-[33px]'; ?> flex h-6 w-6 items-center justify-center rounded-full bg-brand-teal ring-4 ring-slate-800">
                                <i class="fas fa-check text-brand-navy text-[10px]"></i>
                            </span>
                            <h3 class="flex items-center mb-1 text-sm font-semibold text-white">بدء المشروع</h3>
                            <time class="block mb-2 text-xs font-normal text-gray-500"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></time>
                            <p class="text-sm text-gray-400">تم إنشاء غرفة العمل وتفعيل العقد.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Timesheets Tab -->
        <div x-show="currentTab === 'timesheets'" style="display: none;" x-transition.opacity>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white"><?php echo $L['PROJ_TAB_TIMESHEETS']; ?></h3>
                <!-- زر تسجيل الوقت يظهر فقط للخبير -->
                <?php if ($role == 'employee' && $user_id == $project['expert_user_id']): ?>
                    <button onclick="openLogModal()" class="text-brand-cyan hover:text-white bg-brand-cyan/10 hover:bg-brand-cyan/20 px-4 py-2 rounded-lg text-sm transition-all border border-brand-cyan/30">
                        <i class="fas fa-plus-circle <?php echo ($dir=='rtl')?'ml-1':'mr-1'; ?>"></i> <?php echo $L['EMP_BTN_LOG_TIME']; ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto border border-slate-700 rounded-lg">
                <table class="w-full text-<?php echo ($dir=='rtl')?'right':'left'; ?>">
                    <thead class="bg-slate-800 text-gray-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TS_DATE']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TS_HOURS']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TS_DESC']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TS_STATUS']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TS_BY']; ?></th>
                            <?php if($role == 'owner'): ?>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TS_ACTIONS']; ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php if (count($timesheets) > 0): foreach($timesheets as $ts): ?>
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="py-4 px-4 text-gray-300 font-mono text-sm"><?php echo $ts['work_date']; ?></td>
                            <td class="py-4 px-4 font-bold text-white text-lg"><?php echo $ts['hours_logged']; ?></td>
                            <td class="py-4 px-4 text-gray-400 max-w-xs truncate" title="<?php echo htmlspecialchars($ts['description']); ?>">
                                <?php echo htmlspecialchars($ts['description']); ?>
                            </td>
                            <td class="py-4 px-4">
                                <?php 
                                    $status_color = 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
                                    $status_text = $L['PROJ_TS_STATUS_PENDING'];
                                    if ($ts['status'] == 'approved') {
                                        $status_color = 'bg-green-500/10 text-green-500 border-green-500/20';
                                        $status_text = $L['PROJ_TS_STATUS_APPROVED'];
                                    } elseif ($ts['status'] == 'rejected') {
                                        $status_color = 'bg-red-500/10 text-red-500 border-red-500/20';
                                        $status_text = $L['PROJ_TS_STATUS_REJECTED'];
                                    }
                                ?>
                                <span class="text-xs px-2.5 py-1 rounded border <?php echo $status_color; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs text-gray-500"><?php echo htmlspecialchars($ts['full_name']); ?></td>
                            
                            <?php if($role == 'owner'): ?>
                            <td class="py-4 px-4 flex gap-2">
                                <?php if($ts['status'] == 'pending'): ?>
                                <form action="../../config/timesheet_action.php" method="POST" class="inline">
                                    <input type="hidden" name="timesheet_id" value="<?php echo $ts['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="text-green-400 hover:bg-green-400/20 p-1.5 rounded transition" title="<?php echo $L['PROJ_TS_BTN_APPROVE']; ?>"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="../../config/timesheet_action.php" method="POST" class="inline">
                                    <input type="hidden" name="timesheet_id" value="<?php echo $ts['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="text-red-400 hover:bg-red-400/20 p-1.5 rounded transition" title="<?php echo $L['PROJ_TS_BTN_REJECT']; ?>"><i class="fas fa-times"></i></button>
                                </form>
                                <?php else: ?>
                                <span class="text-gray-600">-</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="<?php echo ($role == 'owner')?6:5; ?>" class="py-12 text-center text-gray-500"><?php echo $L['PROJ_TS_EMPTY']; ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Files Tab -->
        <div x-show="currentTab === 'files'" style="display: none;" x-transition.opacity>
            
            <!-- منطقة الرسائل للرفع -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4 border border-red-500/30 text-sm">
                    <?php 
                    if($_GET['error']=='invalid_type') echo 'نوع الملف غير مسموح.';
                    elseif($_GET['error']=='file_too_large') echo 'حجم الملف كبير جداً.';
                    else echo 'حدث خطأ أثناء الرفع.';
                    ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['success']) && strpos($_GET['success'], 'file_uploaded') !== false): ?>
                <div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4 border border-green-500/30 text-sm">
                    تم رفع الملف بنجاح.
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white"><?php echo $L['PROJ_FILES_HEADER']; ?></h3>
                <button onclick="openUploadModal()" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 border border-slate-600">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <?php echo $L['PROJ_FILES_UPLOAD_BTN']; ?>
                </button>
            </div>

            <div class="overflow-x-auto border border-slate-700 rounded-lg bg-slate-800/20">
                <table class="w-full text-<?php echo ($dir=='rtl')?'right':'left'; ?>">
                    <thead class="bg-slate-800 text-gray-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="py-4 px-4"><?php echo $L['PROJ_FILES_NAME']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_FILES_TYPE']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_FILES_SIZE']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_FILES_UPLOADED_BY']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_FILES_DATE']; ?></th>
                            <th class="py-4 px-4"><?php echo $L['PROJ_TABLE_ACTION']; ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php if (count($files) > 0): foreach($files as $file): ?>
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="py-4 px-4 font-medium text-white flex items-center gap-2">
                                <?php 
                                    $icon = 'fa-file';
                                    if($file['type']=='PDF') $icon='fa-file-pdf text-red-400';
                                    if(in_array($file['type'], ['DOC','DOCX'])) $icon='fa-file-word text-blue-400';
                                    if(in_array($file['type'], ['PNG','JPG','JPEG'])) $icon='fa-file-image text-purple-400';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                                <a href="<?php echo htmlspecialchars($file['path']); ?>" target="_blank" class="hover:underline hover:text-brand-teal">
                                    <?php echo htmlspecialchars($file['name']); ?>
                                </a>
                            </td>
                            <td class="py-4 px-4 text-gray-400 uppercase text-xs font-bold"><?php echo htmlspecialchars($file['type']); ?></td>
                            <td class="py-4 px-4 text-gray-400 text-xs font-mono"><?php echo htmlspecialchars($file['size']); ?></td>
                            <td class="py-4 px-4 text-gray-300 text-xs"><?php echo htmlspecialchars($file['uploaded_by']); ?></td>
                            <td class="py-4 px-4 text-gray-500 text-xs"><?php echo date('M d, Y', strtotime($file['date'])); ?></td>
                            <td class="py-4 px-4 flex gap-3">
                                <a href="<?php echo htmlspecialchars($file['path']); ?>" download class="text-brand-cyan hover:text-white text-xs font-bold flex items-center gap-1">
                                    <i class="fas fa-download"></i> <?php echo $L['PROJ_FILES_DOWNLOAD']; ?>
                                </a>
                                <?php if($role == 'owner' || $role == 'admin'): ?>
                                <a href="#" class="text-red-400 hover:text-red-300 text-xs flex items-center gap-1">
                                    <i class="fas fa-trash"></i> <?php echo $L['PROJ_FILES_DELETE']; ?>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-folder-open text-4xl mb-3 opacity-30"></i>
                                    <p><?php echo $L['PROJ_FILES_NO_DATA']; ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- 5. Modals -->

<!-- Log Time Modal -->
<div id="logTimeModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-slate-card p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-600 transform transition-all scale-95 opacity-0" id="logModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white"><?php echo $L['EMP_LOG_TIME_MODAL_TITLE']; ?></h3>
            <button onclick="closeLogModal()" class="text-gray-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form action="../../config/timesheet_handler.php" method="POST" class="space-y-4">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_LABEL_DATE']; ?></label>
                <input type="date" name="date" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-1 focus:ring-brand-teal" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_LABEL_HOURS']; ?></label>
                <input type="number" name="hours" step="0.5" min="0.5" max="24" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-1 focus:ring-brand-teal" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo $L['EMP_LABEL_NOTES']; ?></label>
                <textarea name="description" rows="3" class="w-full bg-dark-navy border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:ring-1 focus:ring-brand-teal"></textarea>
            </div>
            <button type="submit" class="w-full bg-brand-teal text-brand-navy py-3 rounded-lg font-bold mt-2 hover:bg-opacity-90 transition"><?php echo $L['EMP_BTN_SUBMIT_TIMESHEET']; ?></button>
        </form>
    </div>
</div>

<!-- Upload File Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-slate-card p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-600 transform transition-all scale-95 opacity-0" id="uploadModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white"><?php echo $L['PROJ_FILE_MODAL_TITLE']; ?></h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form action="../../config/upload_file_handler.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            <div class="border-2 border-dashed border-slate-600 rounded-lg p-8 text-center hover:border-brand-teal transition-colors cursor-pointer relative">
                <input type="file" name="project_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="document.getElementById('fileName').innerText = this.files[0].name">
                <i class="fas fa-cloud-upload-alt text-4xl text-slate-500 mb-3"></i>
                <p class="text-gray-400 font-medium"><?php echo $L['PROJ_FILE_LABEL_SELECT']; ?></p>
                <p class="text-xs text-gray-500 mt-2"><?php echo $L['PROJ_FILE_HINT']; ?></p>
                <p id="fileName" class="text-brand-cyan text-sm mt-4 font-bold"></p>
            </div>
            <button type="submit" class="w-full bg-slate-100 text-slate-900 py-3 rounded-lg font-bold hover:bg-white transition"><?php echo $L['PROJ_FILE_BTN_UPLOAD']; ?></button>
        </form>
    </div>
</div>

<script>
function confirmComplete() {
    if(confirm("<?php echo $L['PROJ_MSG_CONFIRM_COMPLETE']; ?>")) {
        alert("Feature coming soon: Complete Project API");
    }
}

// Generic Modal Functions
function toggleModal(id, contentId, show) {
    const modal = document.getElementById(id);
    const content = document.getElementById(contentId);
    if(show) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    } else {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
}

function openLogModal() { toggleModal('logTimeModal', 'logModalContent', true); }
function closeLogModal() { toggleModal('logTimeModal', 'logModalContent', false); }

function openUploadModal() { toggleModal('uploadModal', 'uploadModalContent', true); }
function closeUploadModal() { toggleModal('uploadModal', 'uploadModalContent', false); }

// Close on click outside
window.onclick = function(event) {
    if (event.target == document.getElementById('logTimeModal')) closeLogModal();
    if (event.target == document.getElementById('uploadModal')) closeUploadModal();
}
</script>

<?php 
include '../../layout/dashboard_footer.php'; 
?>