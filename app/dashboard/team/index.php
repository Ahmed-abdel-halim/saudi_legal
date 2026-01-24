<?php
// dashboard/team/index.php
// صفحة إدارة فريق العمل (محدثة ومصححة)

// 1. إعدادات عرض الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. تضمين ملف الاتصال (استخدم include_once لتجنب التكرار)
include_once '../../config/db_connect.php';

// 3. بدء الجلسة بأمان
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$lang = $_SESSION['lang'] ?? 'ar';

// 5. تعريف النصوص محلياً (لتجنب الاعتماد الكلي على ملفات خارجية قد تكون مفقودة)
$L_LOCAL = [
    'ar' => [
        'TEAM_TITLE' => 'إدارة الفريق',
        'TEAM_BTN_ADD_MEMBER' => 'إضافة عضو جديد',
        'TEAM_MSG_INVITE_SUCCESS' => 'تم إرسال الدعوة بنجاح.',
        'TEAM_TABLE_NAME' => 'الاسم',
        'TEAM_TABLE_ROLE' => 'الدور',
        'TEAM_TABLE_STATUS' => 'الحالة',
        'TEAM_TABLE_ACTION' => 'إجراء',
        'TEAM_STATUS_ACTIVE' => 'نشط',
        'TEAM_NO_MEMBERS' => 'لا يوجد أعضاء في الفريق حتى الآن.',
        'BTN_EDIT' => 'تعديل',
        'TEAM_MODAL_ADD_TITLE' => 'دعوة عضو جديد',
        'TEAM_MODAL_LABEL_EMAIL' => 'البريد الإلكتروني',
        'TEAM_MODAL_LABEL_NAME' => 'الاسم',
        'TEAM_MODAL_BTN_SEND' => 'إرسال الدعوة',
        'PENDING_INVITE' => 'دعوة معلقة'
    ],
    'en' => [
        'TEAM_TITLE' => 'Team Management',
        'TEAM_BTN_ADD_MEMBER' => 'Add New Member',
        'TEAM_MSG_INVITE_SUCCESS' => 'Invitation sent successfully.',
        'TEAM_TABLE_NAME' => 'Name',
        'TEAM_TABLE_ROLE' => 'Role',
        'TEAM_TABLE_STATUS' => 'Status',
        'TEAM_TABLE_ACTION' => 'Action',
        'TEAM_STATUS_ACTIVE' => 'Active',
        'TEAM_NO_MEMBERS' => 'No team members yet.',
        'BTN_EDIT' => 'Edit',
        'TEAM_MODAL_ADD_TITLE' => 'Invite New Member',
        'TEAM_MODAL_LABEL_EMAIL' => 'Email',
        'TEAM_MODAL_LABEL_NAME' => 'Name',
        'TEAM_MODAL_BTN_SEND' => 'Send Invite',
        'PENDING_INVITE' => 'Pending Invite'
    ]
];
// دمج المصفوفة المحلية مع العامة إن وجدت، أو استخدام المحلية
$L = isset($L) ? array_merge($L_LOCAL[$lang], $L) : $L_LOCAL[$lang];

// 6. تضمين الهيدر (مع إخفاء تحذيرات الجلسة مؤقتاً)
$original_level = error_reporting();
error_reporting($original_level & ~E_WARNING & ~E_NOTICE);
include '../../layout/dashboard_header.php';
error_reporting($original_level);

// 7. الحصول على معرف الشركة (Company ID) بشكل مضمون
$company_id = $_SESSION['company_id'] ?? 0;
if ($company_id == 0) {
    $stmt = $conn->prepare("SELECT company_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $company_id = $row['company_id'];
        $_SESSION['company_id'] = $company_id;
    }
}

if ($company_id == 0) {
    echo "<div class='p-10 text-center text-red-600 font-bold'>خطأ: لا يوجد شركة مرتبطة بهذا الحساب.</div>";
    include '../../layout/dashboard_footer.php';
    exit();
}

// 8. جلب أعضاء الفريق
$stmt = $conn->prepare("SELECT user_id, full_name, email, role, is_active, created_at FROM users WHERE company_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $L['TEAM_TITLE']; ?></h1>
        <button onclick="document.getElementById('inviteModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <?php echo $L['TEAM_BTN_ADD_MEMBER']; ?>
        </button>
    </div>

    <!-- عرض رسائل النجاح أو الخطأ -->
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="block sm:inline font-bold"><?php echo $L['TEAM_MSG_INVITE_SUCCESS']; ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
            <span class="block sm:inline font-bold">حدث خطأ: <?php echo htmlspecialchars($_GET['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Team Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['TEAM_TABLE_NAME']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['TEAM_TABLE_ROLE']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['TEAM_TABLE_STATUS']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['TEAM_TABLE_ACTION']; ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm mr-3 rtl:ml-3 border border-indigo-100">
                                        <?php 
                                            $initial = !empty($row['full_name']) ? mb_substr($row['full_name'], 0, 1, 'UTF-8') : substr($row['email'], 0, 1);
                                            echo strtoupper($initial); 
                                        ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900"><?php echo htmlspecialchars($row['full_name'] ?: '---'); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($row['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold uppercase">
                                    <?php echo htmlspecialchars($row['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($row['is_active'] == 1): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <?php echo $L['TEAM_STATUS_ACTIVE']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        <?php echo $L['PENDING_INVITE']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <a href="view_employee.php?id=<?php echo $row['user_id']; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold hover:underline">
                                    <?php echo $L['BTN_EDIT']; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p><?php echo $L['TEAM_NO_MEMBERS']; ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Invite Modal -->
<div id="inviteModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative transform scale-100 transition-transform">
        <button onclick="document.getElementById('inviteModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        
        <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo $L['TEAM_MODAL_ADD_TITLE']; ?></h3>
        
        <!-- النموذج يرسل البيانات إلى invite_handler.php -->
        <form action="../../config/invite_handler.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"><?php echo $L['TEAM_MODAL_LABEL_EMAIL']; ?></label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"><?php echo $L['TEAM_MODAL_LABEL_NAME']; ?></label>
                <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md">
                <?php echo $L['TEAM_MODAL_BTN_SEND']; ?>
            </button>
        </form>
    </div>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>