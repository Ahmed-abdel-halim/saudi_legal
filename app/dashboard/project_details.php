<?php
// dashboard/project_details.php
// صفحة تفاصيل المشروع (للطالب والمورد)

// 1. إعدادات الأخطاء والاتصال
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 3. إعدادات اللغة
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar');
$_SESSION['lang'] = $lang;
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

// قاموس الترجمة
$trans_data = [
    'ar' => [
        'title' => 'تفاصيل المشروع',
        'back' => 'عودة للمشاريع',
        'status_open' => 'مفتوح للعروض',
        'status_in_progress' => 'قيد التنفيذ',
        'status_completed' => 'مكتمل',
        'lbl_budget' => 'الميزانية:',
        'lbl_duration' => 'المدة:',
        'lbl_posted' => 'نشر بتاريخ:',
        'lbl_desc' => 'وصف المشروع',
        'lbl_skills' => 'المهارات المطلوبة',
        'section_bids' => 'العروض المقدمة',
        'section_place_bid' => 'تقديم عرض',
        'no_bids' => 'لم يتم تقديم أي عروض حتى الآن.',
        'bid_expert' => 'الخبير المقترح:',
        'bid_rate' => 'السعر:',
        'bid_msg' => 'رسالة العرض:',
        'btn_accept' => 'قبول العرض',
        'btn_submit_bid' => 'إرسال العرض',
        'btn_edit' => 'تعديل المشروع', // نص جديد
        'lbl_choose_expert' => 'اختر خبيراً من فريقك',
        'lbl_offer_rate' => 'سعر العرض (ساعة)',
        'lbl_offer_msg' => 'رسالة مرافقة',
        'msg_bid_success' => 'تم إرسال عرضك بنجاح!',
        'msg_accept_success' => 'تم قبول العرض! المشروع قيد التحضير.',
        'err_own_project' => 'لا يمكنك تقديم عرض على مشروعك الخاص.',
        'err_already_bid' => 'لقد قدمت عرضاً مسبقاً لهذا المشروع.',
        'login_as_supplier' => 'يجب أن تكون شركة موردة لتقديم عرض.'
    ],
    'en' => [
        'title' => 'Project Details',
        'back' => 'Back to Projects',
        'status_open' => 'Open for Bids',
        'status_in_progress' => 'In Progress',
        'status_completed' => 'Completed',
        'lbl_budget' => 'Budget:',
        'lbl_duration' => 'Duration:',
        'lbl_posted' => 'Posted on:',
        'lbl_desc' => 'Project Description',
        'lbl_skills' => 'Required Skills',
        'section_bids' => 'Received Bids',
        'section_place_bid' => 'Place a Bid',
        'no_bids' => 'No bids received yet.',
        'bid_expert' => 'Proposed Expert:',
        'bid_rate' => 'Rate:',
        'bid_msg' => 'Cover Letter:',
        'btn_accept' => 'Accept Bid',
        'btn_submit_bid' => 'Submit Bid',
        'btn_edit' => 'Edit Project', // New text
        'lbl_choose_expert' => 'Choose Expert from Team',
        'lbl_offer_rate' => 'Offer Rate (Hourly)',
        'lbl_offer_msg' => 'Cover Letter',
        'msg_bid_success' => 'Bid placed successfully!',
        'msg_accept_success' => 'Bid accepted! Project is starting.',
        'err_own_project' => 'You cannot bid on your own project.',
        'err_already_bid' => 'You have already placed a bid on this project.',
        'login_as_supplier' => 'You must be a supplier to place a bid.'
    ]
];
$t = $trans_data[$lang];

// 4. التحقق من معرف المشروع
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("رابط غير صالح.");
}
$project_id = intval($_GET['id']);

// 5. جلب بيانات الشركة الحالية
$stmt = $conn->prepare("SELECT company_id, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_data = $stmt->get_result()->fetch_assoc();
$my_company_id = $my_data['company_id'];

if (!$my_company_id) {
    die("خطأ: حسابك غير مرتبط بشركة.");
}

// 6. جلب تفاصيل المشروع
$sql_proj = "
    SELECT p.*, c.name as requester_name, c.company_logo 
    FROM projects p 
    JOIN companies c ON p.requester_company_id = c.company_id 
    WHERE p.project_id = ?
";
$stmt = $conn->prepare($sql_proj);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    die("المشروع غير موجود.");
}

// تحديد الدور: هل أنا صاحب الطلب؟
$is_owner = ($project['requester_company_id'] == $my_company_id);

// 7. معالجة الإجراءات (POST)

// أ) تقديم عرض (للموردين فقط)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_bid') {
    if ($is_owner) {
        $msg = $t['err_own_project']; $msg_type = "error";
    } else {
        $expert_id = intval($_POST['expert_id']);
        $rate = floatval($_POST['rate']);
        $message = htmlspecialchars(trim($_POST['message']));

        // التحقق من عدم التكرار
        $check = $conn->query("SELECT bid_id FROM bids WHERE project_id = $project_id AND supplier_company_id = $my_company_id");
        if ($check->num_rows > 0) {
            $msg = $t['err_already_bid']; $msg_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO bids (project_id, supplier_company_id, expert_user_id, proposed_rate, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiids", $project_id, $my_company_id, $expert_id, $rate, $message);
            if ($stmt->execute()) {
                $msg = $t['msg_bid_success']; $msg_type = "success";
            } else {
                $msg = "Error: " . $stmt->error; $msg_type = "error";
            }
        }
    }
}

// ب) قبول عرض (لصاحب الطلب فقط)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'accept_bid') {
    if ($is_owner) {
        $bid_id = intval($_POST['bid_id']);
        
        // جلب تفاصيل العرض الفائز
        $bid_q = $conn->query("SELECT * FROM bids WHERE bid_id = $bid_id");
        $winning_bid = $bid_q->fetch_assoc();

        if ($winning_bid) {
            // تحديث المشروع (إسناده للمورد)
            $stmt = $conn->prepare("UPDATE projects SET status = 'in_progress', supplier_company_id = ? WHERE project_id = ?");
            $stmt->bind_param("ii", $winning_bid['supplier_company_id'], $project_id);
            $stmt->execute();

            // تحديث حالة العروض (قبول الفائز)
            $conn->query("UPDATE bids SET status = 'accepted' WHERE bid_id = $bid_id");
            // يمكن رفض الباقي أو تركهم معلقين
            
            $msg = $t['msg_accept_success']; $msg_type = "success";
            // تحديث الصفحة
            header("Refresh:2");
        }
    }
}

// 8. جلب العروض (للعرض)
$bids = [];
if ($is_owner) {
    // صاحب الطلب يرى كل العروض
    $bids_sql = "
        SELECT b.*, u.full_name as expert_name, c.name as supplier_name, c.company_logo 
        FROM bids b 
        JOIN users u ON b.expert_user_id = u.user_id 
        JOIN companies c ON b.supplier_company_id = c.company_id 
        WHERE b.project_id = ? 
        ORDER BY b.created_at DESC
    ";
    $stmt = $conn->prepare($bids_sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) $bids[] = $row;
} else {
    // المورد يرى عرضه فقط (إن وجد)
    $bids_sql = "
        SELECT b.*, u.full_name as expert_name 
        FROM bids b 
        JOIN users u ON b.expert_user_id = u.user_id 
        WHERE b.project_id = ? AND b.supplier_company_id = ?
    ";
    $stmt = $conn->prepare($bids_sql);
    $stmt->bind_param("ii", $project_id, $my_company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) $bids[] = $row;
}

// 9. جلب موظفي المورد (للقائمة المنسدلة في نموذج العرض)
$my_experts = [];
if (!$is_owner && empty($bids)) { // فقط إذا لم يكن قد قدم عرضاً بعد
    $exp_sql = "SELECT user_id, full_name, job_title_ar, hourly_rate FROM users WHERE company_id = ? AND role = 'expert' AND is_active = 1";
    $stmt = $conn->prepare($exp_sql);
    $stmt->bind_param("i", $my_company_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $my_experts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> | TimeShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: '<?php echo ($lang == 'ar') ? 'Cairo' : 'Inter'; ?>', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-20">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center max-w-5xl">
            <div class="flex items-center gap-3">
                <span class="font-black text-xl text-indigo-900 tracking-tighter">TimeShare</span>
                <span class="text-slate-300">|</span>
                <span class="font-bold text-lg text-slate-700"><?php echo $t['title']; ?></span>
            </div>
            <a href="projects.php" class="text-slate-500 hover:text-slate-800 font-bold text-sm flex items-center gap-1 transition">
                <?php if($lang=='ar'): ?>&larr;<?php else: ?>&rarr;<?php endif; ?> 
                <?php echo $t['back']; ?>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-5xl">

        <?php if($msg): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 shadow-sm <?php echo ($msg_type=='success')?'bg-green-50 text-green-700 border border-green-200':'bg-red-50 text-red-700 border border-red-200'; ?>">
                <?php if ($msg_type == 'success'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php endif; ?>
                <span class="font-bold"><?php echo $msg; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- العمود الأيمن: تفاصيل المشروع -->
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">#PROJECT-<?php echo $project['project_id']; ?></span>
                            <h1 class="text-2xl font-bold text-slate-800 leading-snug"><?php echo htmlspecialchars($project['title']); ?></h1>
                        </div>
                        <div class="flex gap-2 items-center">
                            <!-- زر التعديل (جديد) -->
                            <?php if($is_owner && $project['status'] == 'open'): ?>
                                <a href="edit_project.php?id=<?php echo $project['project_id']; ?>" class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold hover:bg-slate-200 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <?php echo $t['btn_edit']; ?>
                                </a>
                            <?php endif; ?>

                            <?php 
                                $status_class = 'bg-slate-100 text-slate-600';
                                $status_text = $project['status'];
                                if($project['status']=='open') { $status_class='bg-green-100 text-green-700'; $status_text = $t['status_open']; }
                                if($project['status']=='in_progress') { $status_class='bg-indigo-100 text-indigo-700'; $status_text = $t['status_in_progress']; }
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mb-6 text-sm text-slate-500">
                        <?php $logo = !empty($project['company_logo']) ? "../".$project['company_logo'] : "https://ui-avatars.com/api/?name=".urlencode($project['requester_name']); ?>
                        <img src="<?php echo $logo; ?>" class="w-6 h-6 rounded-full">
                        <span class="font-bold text-slate-700"><?php echo htmlspecialchars($project['requester_name']); ?></span>
                        <span class="text-slate-300">•</span>
                        <span><?php echo $t['lbl_posted']; ?> <?php echo date('Y-m-d', strtotime($project['created_at'])); ?></span>
                    </div>

                    <div class="flex gap-4 mb-8 border-y border-slate-100 py-4">
                        <div class="flex-1">
                            <span class="block text-xs text-slate-400 uppercase font-bold mb-1"><?php echo $t['lbl_budget']; ?></span>
                            <span class="text-lg font-black text-slate-800"><?php echo number_format($project['budget']); ?> ر.س</span>
                            <span class="text-xs text-slate-400">/ ساعة</span>
                        </div>
                        <div class="flex-1 border-r border-slate-100 pr-4 mr-4">
                            <span class="block text-xs text-slate-400 uppercase font-bold mb-1"><?php echo $t['lbl_duration']; ?></span>
                            <span class="text-lg font-black text-slate-800"><?php echo $project['duration_days']; ?> أيام</span>
                        </div>
                    </div>

                    <h3 class="font-bold text-slate-800 mb-2"><?php echo $t['lbl_desc']; ?></h3>
                    <div class="text-slate-600 leading-relaxed text-sm mb-6">
                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                    </div>

                    <?php if(!empty($project['required_skills'])): ?>
                        <h3 class="font-bold text-slate-800 mb-2"><?php echo $t['lbl_skills']; ?></h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach(explode(',', $project['required_skills']) as $skill): ?>
                                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold">
                                    <?php echo trim($skill); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- قسم العروض (Bids) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4"><?php echo $t['section_bids']; ?></h3>
                    
                    <?php if (count($bids) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($bids as $bid): ?>
                                <div class="border border-slate-100 rounded-xl p-5 hover:border-indigo-100 transition <?php echo ($bid['status']=='accepted')?'bg-green-50 border-green-200 ring-2 ring-green-500':'bg-white'; ?>">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex gap-3">
                                            <?php if($is_owner): ?>
                                                <?php $sup_logo = !empty($bid['company_logo']) ? "../".$bid['company_logo'] : "https://ui-avatars.com/api/?name=S"; ?>
                                                <img src="<?php echo $sup_logo; ?>" class="w-10 h-10 rounded-lg bg-slate-50">
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($bid['supplier_name']); ?></h4>
                                                    <p class="text-xs text-slate-500"><?php echo $t['bid_expert']; ?> <?php echo htmlspecialchars($bid['expert_name']); ?></p>
                                                </div>
                                            <?php else: ?>
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-sm">عرضك المقدم</h4>
                                                    <p class="text-xs text-slate-500"><?php echo $t['bid_expert']; ?> <?php echo htmlspecialchars($bid['expert_name']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-left">
                                            <span class="block font-black text-lg text-emerald-600"><?php echo $bid['proposed_rate']; ?> ر.س</span>
                                            
                                            <?php if($bid['status'] == 'accepted'): ?>
                                                <span class="inline-block bg-green-500 text-white text-[10px] px-2 py-0.5 rounded font-bold">تم القبول</span>
                                            <?php elseif($bid['status'] == 'pending'): ?>
                                                <span class="inline-block bg-yellow-100 text-yellow-700 text-[10px] px-2 py-0.5 rounded font-bold">قيد الانتظار</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg mb-4">
                                        <?php echo nl2br(htmlspecialchars($bid['message'])); ?>
                                    </p>

                                    <?php if($is_owner && $project['status'] == 'open'): ?>
                                        <form method="POST" onsubmit="return confirm('هل أنت متأكد من قبول هذا العرض؟ سيتم بدء المشروع فوراً.');">
                                            <input type="hidden" name="action" value="accept_bid">
                                            <input type="hidden" name="bid_id" value="<?php echo $bid['bid_id']; ?>">
                                            <button type="submit" class="w-full bg-slate-900 text-white py-2 rounded-lg text-sm font-bold hover:bg-emerald-600 transition">
                                                <?php echo $t['btn_accept']; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-slate-400 text-sm italic border-2 border-dashed border-slate-100 rounded-xl">
                            <?php echo $t['no_bids']; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- العمود الجانبي: نموذج التقديم (للموردين) -->
            <div class="lg:col-span-1">
                
                <?php if (!$is_owner && empty($bids) && $project['status'] == 'open'): ?>
                    <div class="bg-white rounded-2xl shadow-lg border border-indigo-100 p-6 sticky top-24">
                        <h3 class="font-bold text-lg text-indigo-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <?php echo $t['section_place_bid']; ?>
                        </h3>

                        <?php if(empty($my_experts)): ?>
                            <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4">
                                لا يوجد لديك موظفين (خبراء) نشطين لإسناد المهمة لهم. 
                                <a href="team/index.php" class="underline font-bold">أضف موظفين</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="submit_bid">
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1"><?php echo $t['lbl_choose_expert']; ?></label>
                                    <select name="expert_id" class="w-full p-2.5 rounded-lg border border-slate-200 text-sm bg-white focus:border-indigo-500 outline-none" required>
                                        <?php foreach($my_experts as $exp): ?>
                                            <option value="<?php echo $exp['user_id']; ?>">
                                                <?php echo htmlspecialchars($exp['full_name']); ?> 
                                                (<?php echo $exp['hourly_rate']; ?> ر.س/س)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1"><?php echo $t['lbl_offer_rate']; ?></label>
                                    <input type="number" name="rate" step="0.01" class="w-full p-2.5 rounded-lg border border-slate-200 text-sm font-bold focus:border-indigo-500 outline-none" placeholder="0.00" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1"><?php echo $t['lbl_offer_msg']; ?></label>
                                    <textarea name="message" rows="3" class="w-full p-2.5 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 outline-none resize-none" placeholder="لماذا تختارنا؟" required></textarea>
                                </div>

                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold shadow-md transition transform active:scale-95">
                                    <?php echo $t['btn_submit_bid']; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php elseif ($is_owner): ?>
                    <!-- معلومات لصاحب الطلب -->
                    <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100 text-center">
                        <p class="text-indigo-800 font-bold text-sm mb-2">إدارة الطلب</p>
                        <p class="text-indigo-600 text-xs">ستصلك إشعارات عند تقديم عروض جديدة.</p>
                    </div>
                <?php else: ?>
                    <!-- تم التقديم مسبقاً أو مغلق -->
                    <div class="bg-slate-100 p-5 rounded-2xl border border-slate-200 text-center">
                        <p class="text-slate-500 font-bold text-sm">
                            <?php echo ($project['status'] != 'open') ? 'المشروع مغلق أو قيد التنفيذ' : 'لقد قدمت عرضاً بالفعل'; ?>
                        </p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

</body>
</html>