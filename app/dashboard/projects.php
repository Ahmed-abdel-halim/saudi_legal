<?php
// dashboard/projects.php
// صفحة إدارة المشاريع والعقود (للشركات الموردة والطالبة)

include '../config/db_connect.php';
session_start();

// 1. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. إعدادات اللغة
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar');
$_SESSION['lang'] = $lang;
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

// قاموس الترجمة المحلي
$t = [
    'ar' => [
        'title' => 'المشاريع والعقود',
        'back' => 'عودة للرئيسية',
        'btn_new_project' => 'نشر طلب مشروع جديد',
        'tab_active' => 'قيد التنفيذ',
        'tab_pending' => 'بانتظار العروض',
        'tab_completed' => 'مكتملة',
        'tbl_project' => 'المشروع',
        'tbl_role' => 'دوري',
        'tbl_partner' => 'الطرف الآخر',
        'tbl_status' => 'الحالة',
        'tbl_budget' => 'الميزانية',
        'tbl_action' => 'إجراء',
        'role_requester' => 'صاحب الطلب',
        'role_supplier' => 'مورد',
        'status_open' => 'مفتوح',
        'status_in_progress' => 'جاري العمل',
        'status_completed' => 'مكتمل',
        'btn_view' => 'عرض التفاصيل',
        'empty_title' => 'لا توجد مشاريع نشطة حالياً',
        'empty_desc' => 'لم تبدأ أي مشاريع بعد. يمكنك البدء بنشر طلب جديد لاستقطاب الخبراء، أو تصفح الخدمات المتاحة.',
        'btn_empty_action' => 'ابحث عن خبير الآن',
        'currency' => 'ر.س'
    ],
    'en' => [
        'title' => 'Projects & Contracts',
        'back' => 'Back to Dashboard',
        'btn_new_project' => 'Post New Project',
        'tab_active' => 'In Progress',
        'tab_pending' => 'Pending Bids',
        'tab_completed' => 'Completed',
        'tbl_project' => 'Project',
        'tbl_role' => 'My Role',
        'tbl_partner' => 'Partner',
        'tbl_status' => 'Status',
        'tbl_budget' => 'Budget',
        'tbl_action' => 'Action',
        'role_requester' => 'Requester',
        'role_supplier' => 'Supplier',
        'status_open' => 'Open',
        'status_in_progress' => 'In Progress',
        'status_completed' => 'Completed',
        'btn_view' => 'View Details',
        'empty_title' => 'No Active Projects Yet',
        'empty_desc' => 'You haven\'t started any projects yet. Start by posting a new request to find experts, or browse available services.',
        'btn_empty_action' => 'Find an Expert Now',
        'currency' => 'SAR'
    ]
][$lang];

// 3. جلب بيانات الشركة الحالية للمستخدم
$stmt = $conn->prepare("SELECT company_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$company_id = $user_data['company_id'];

if (!$company_id) {
    die("خطأ: هذا الحساب غير مرتبط بشركة.");
}

// 4. جلب المشاريع (سواء كنت الطالب أو المورد)
// نفترض وجود جدول projects بالأعمدة: project_id, title, status, budget, requester_company_id, supplier_company_id
$sql = "
    SELECT p.*, 
           c_req.name as requester_name, 
           c_sup.name as supplier_name
    FROM projects p
    LEFT JOIN companies c_req ON p.requester_company_id = c_req.company_id
    LEFT JOIN companies c_sup ON p.supplier_company_id = c_sup.company_id
    WHERE p.requester_company_id = ? OR p.supplier_company_id = ?
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $company_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
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
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="index.php" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M<?php echo ($lang=='ar')?'19 12H5m7 7l-7-7 7-7':'5 12h14M12 5l7 7-7 7'; ?>"></path></svg>
                </a>
                <span class="font-bold text-xl text-slate-800"><?php echo $t['title']; ?></span>
            </div>
            <div class="flex gap-2">
                <a href="post_project.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="hidden md:inline"><?php echo $t['btn_new_project']; ?></span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">

        <?php if (count($projects) > 0): ?>
            
            <!-- 1. حالة وجود مشاريع: عرض القائمة -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-bold"><?php echo $t['tbl_project']; ?></th>
                                <th class="px-6 py-4 font-bold"><?php echo $t['tbl_role']; ?></th>
                                <th class="px-6 py-4 font-bold"><?php echo $t['tbl_partner']; ?></th>
                                <th class="px-6 py-4 font-bold"><?php echo $t['tbl_status']; ?></th>
                                <th class="px-6 py-4 font-bold"><?php echo $t['tbl_budget']; ?></th>
                                <th class="px-6 py-4 font-bold text-center"><?php echo $t['tbl_action']; ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($projects as $proj): ?>
                                <?php 
                                    $is_requester = ($proj['requester_company_id'] == $company_id);
                                    $role_label = $is_requester ? $t['role_requester'] : $t['role_supplier'];
                                    $role_color = $is_requester ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700';
                                    
                                    $partner_name = $is_requester ? ($proj['supplier_name'] ?? '---') : $proj['requester_name'];
                                    
                                    $status_color = 'bg-slate-100 text-slate-600';
                                    $status_text = $proj['status'];
                                    if ($proj['status'] == 'open') { $status_color = 'bg-green-100 text-green-700'; $status_text = $t['status_open']; }
                                    if ($proj['status'] == 'in_progress') { $status_color = 'bg-indigo-100 text-indigo-700'; $status_text = $t['status_in_progress']; }
                                    if ($proj['status'] == 'completed') { $status_color = 'bg-emerald-100 text-emerald-700'; $status_text = $t['status_completed']; }
                                ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        <?php echo htmlspecialchars($proj['title']); ?>
                                        <div class="text-xs text-slate-400 font-normal mt-1"><?php echo date('Y-m-d', strtotime($proj['created_at'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold <?php echo $role_color; ?>">
                                            <?php echo $role_label; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium">
                                        <?php echo htmlspecialchars($partner_name); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $status_color; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-slate-700">
                                        <?php echo number_format($proj['budget'] ?? 0); ?> <?php echo $t['currency']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="project_details.php?id=<?php echo $proj['project_id']; ?>" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline transition">
                                            <?php echo $t['btn_view']; ?> &rarr;
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>

            <!-- 2. الحالة الفارغة (Empty State) -->
            <div class="max-w-2xl mx-auto mt-12 text-center">
                <div class="bg-white rounded-3xl p-10 shadow-sm border border-slate-200">
                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-3"><?php echo $t['empty_title']; ?></h2>
                    <p class="text-slate-500 mb-8 leading-relaxed max-w-md mx-auto">
                        <?php echo $t['empty_desc']; ?>
                    </p>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="post_project.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1">
                            <?php echo $t['btn_new_project']; ?>
                        </a>
                        <a href="../search.php" class="bg-white border border-slate-300 text-slate-700 hover:border-slate-400 px-8 py-3 rounded-xl font-bold transition">
                            <?php echo $t['btn_empty_action']; ?>
                        </a>
                    </div>
                </div>

                <!-- نصائح سريعة -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8 text-right rtl:text-right ltr:text-left">
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <span class="text-2xl block mb-2">⚡</span>
                        <h4 class="font-bold text-sm text-slate-800">نشر سريع</h4>
                        <p class="text-xs text-slate-500 mt-1">انشر طلبك في دقيقتين واحصل على عروض.</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <span class="text-2xl block mb-2">🛡️</span>
                        <h4 class="font-bold text-sm text-slate-800">حماية كاملة</h4>
                        <p class="text-xs text-slate-500 mt-1">دفع آمن عبر المنصة وعقود موثقة.</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <span class="text-2xl block mb-2">🤝</span>
                        <h4 class="font-bold text-sm text-slate-800">خبراء معتمدون</h4>
                        <p class="text-xs text-slate-500 mt-1">تعامل مع شركات موردة موثوقة فقط.</p>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>