<?php
// dashboard/requester/my-requests.php
include '../../layout/dashboard_header.php';

// التحقق من وجود الجدول قبل الاستعلام لتجنب الخطأ القاتل
$check_table = $conn->query("SHOW TABLES LIKE 'projects'");
$has_table = $check_table->num_rows > 0;

if ($has_table) {
    // جلب الطلبات الخاصة بالشركة
    $stmt = $conn->prepare("SELECT * FROM projects WHERE requester_company_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800"><?php echo $L['REQ_MY_REQUESTS_TITLE']; ?></h1>
    <a href="post-request.php" class="bg-brand-magenta hover:bg-fuchsia-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition flex items-center gap-2">
        <span>+</span> <?php echo $L['REQ_BTN_POST_NEW']; ?>
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
        <?php echo $L['REQ_MSG_CREATED']; ?>
    </div>
<?php endif; ?>

<!-- Requests List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <?php if ($has_table && $result && $result->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['REQ_TABLE_TITLE']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['REQ_TABLE_DATE']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['REQ_TABLE_BUDGET']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['REQ_TABLE_STATUS']; ?></th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"><?php echo $L['REQ_TABLE_ACTION']; ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900"><?php echo htmlspecialchars($row['title']); ?></p>
                            <p class="text-xs text-gray-500 truncate w-48">
                                <?php 
                                // دعم الأسماء المختلفة للأعمدة
                                $skills = $row['required_skills'] ?? $row['skills'] ?? '';
                                echo htmlspecialchars($skills); 
                                ?>
                            </p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-brand-dark">
                            <?php echo number_format($row['budget']); ?> SAR
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                <?php echo ($row['status'] == 'open') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="request-detail.php?id=<?php echo $row['project_id'] ?? $row['id']; ?>" class="text-brand-magenta hover:text-fuchsia-800 text-sm font-bold">
                                <?php echo $L['BTN_DETAILS']; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="p-12 text-center">
            <div class="inline-flex p-4 bg-gray-50 rounded-full mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1"><?php echo $L['REQ_NO_REQUESTS']; ?></h3>
            <p class="text-gray-500 mb-6 text-sm">ابدأ بنشر أول طلب خدمة لك للحصول على عروض.</p>
            <a href="post-request.php" class="bg-brand-magenta text-white px-6 py-2 rounded-lg font-bold hover:bg-fuchsia-700 transition">
                <?php echo $L['REQ_BTN_POST_NEW']; ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../../layout/dashboard_footer.php'; ?>