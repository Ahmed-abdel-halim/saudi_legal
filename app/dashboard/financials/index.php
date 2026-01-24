<?php // dashboard/financials/index.php (المالية والفواتير - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 🔒 الحماية ---
// السماح للملاك من كلا النوعين (طالب ومورد) بالوصول، لكن سنركز العرض للطالب (Requester) بشكل أساسي
// المورد سيرى "أرباح" بدلاً من "إنفاق" (يمكن تخصيص ذلك لاحقاً)
if ($role != 'owner') {
    header("Location: /dashboard/index.php?error=unauthorized");
    exit();
}

// --- جلب البيانات المالية ---
// 1. إحصائيات سريعة
$stats_query = "SELECT 
                    SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) as total_processed,
                    SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as total_pending,
                    MAX(created_at) as last_payment_date
                FROM payments 
                WHERE company_id = ?"; // نفترض وجود جدول payments يربط الدفع بالشركة

// ملاحظة: إذا لم يكن هناك جدول payments، يمكننا استخدام projects كبديل مؤقت للعرض
$use_projects_table = true; 

if ($use_projects_table) {
    // للطالب: إجمالي ما دفعه
    // للمورد: إجمالي ما كسبه
    $col_name = ($company_type == 'requester') ? 'requester_company_id' : 'supplier_company_id';
    
    $stats_stmt = $conn->prepare("SELECT 
                                    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_processed,
                                    SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as total_pending
                                  FROM projects 
                                  WHERE $col_name = ?");
    $stats_stmt->bind_param("i", $company_id);
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();
    $total_processed = $stats['total_processed'] ?? 0;
    $total_pending = $stats['total_pending'] ?? 0;
    $last_payment_date = date('Y-m-d'); // افتراضي
}
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-white mb-2"><?php echo $L['FIN_TITLE']; ?></h1>
        <p class="text-gray-400"><?php echo $L['FIN_SUBTITLE']; ?></p>
    </div>
    <!-- زر تصدير (مستقبلي) -->
    <button class="mt-4 md:mt-0 bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
        <i class="fas fa-download <?php echo ($dir=='rtl')?'ml-2':'mr-2'; ?>"></i>
        <?php echo $L['FIN_TAB_INVOICES']; ?>
    </button>
</div>

<!-- مؤشرات الأداء المالية -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- البطاقة 1 -->
    <div class="bg-slate-card p-6 rounded-xl border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-400 font-medium text-sm">
                <?php echo ($company_type == 'supplier') ? $L['SUPPLIER_KPI_TOTAL_REV'] : $L['FIN_KPI_TOTAL_SPEND']; ?>
            </h3>
            <div class="p-2 bg-brand-teal/10 rounded-full text-brand-teal">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-white"><?php echo number_format($total_processed, 2); ?> <span class="text-sm font-normal text-gray-500">SAR</span></p>
    </div>

    <!-- البطاقة 2 -->
    <div class="bg-slate-card p-6 rounded-xl border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-400 font-medium text-sm"><?php echo $L['FIN_KPI_PENDING_PAYMENTS']; ?></h3>
            <div class="p-2 bg-yellow-500/10 rounded-full text-yellow-500">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-white"><?php echo number_format($total_pending, 2); ?> <span class="text-sm font-normal text-gray-500">SAR</span></p>
    </div>

    <!-- البطاقة 3 -->
    <div class="bg-slate-card p-6 rounded-xl border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-400 font-medium text-sm"><?php echo $L['FIN_KPI_LAST_PAYMENT']; ?></h3>
            <div class="p-2 bg-brand-magenta/10 rounded-full text-brand-magenta">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <p class="text-xl font-bold text-white"><?php echo $last_payment_date; ?></p>
    </div>
</div>

<!-- جدول المعاملات -->
<div class="bg-slate-card rounded-xl shadow-lg overflow-hidden">
    <div class="p-6 border-b border-slate-700 flex justify-between items-center">
        <h2 class="text-xl font-bold text-white"><?php echo $L['FIN_TAB_TRANSACTIONS']; ?></h2>
        
        <!-- فلاتر بسيطة -->
        <div class="flex space-x-2 space-x-reverse">
            <span class="px-3 py-1 bg-brand-teal/20 text-brand-teal rounded-full text-xs font-bold cursor-pointer"><?php echo $L['SERVICES_FILTER_ALL']; ?></span>
            <span class="px-3 py-1 bg-slate-700 text-gray-400 rounded-full text-xs font-bold hover:bg-slate-600 cursor-pointer transition-colors"><?php echo $L['FIN_STATUS_PAID']; ?></span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full <?php echo ($dir == 'rtl') ? 'text-right' : 'text-left'; ?>">
            <thead class="bg-slate-800 text-gray-400 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-4"><?php echo $L['FIN_TABLE_ID']; ?></th>
                    <th class="px-6 py-4"><?php echo $L['FIN_TABLE_DESC']; ?></th>
                    <th class="px-6 py-4"><?php echo $L['FIN_TABLE_DATE']; ?></th>
                    <th class="px-6 py-4"><?php echo $L['FIN_TABLE_AMOUNT']; ?></th>
                    <th class="px-6 py-4"><?php echo $L['FIN_TABLE_STATUS']; ?></th>
                    <th class="px-6 py-4"><?php echo $L['FIN_TABLE_INVOICE']; ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700 text-sm">
                <?php
                // جلب قائمة المشاريع كمعاملات
                $col_name = ($company_type == 'requester') ? 'requester_company_id' : 'supplier_company_id';
                $stmt_trans = $conn->prepare("SELECT project_id, title, created_at, total_amount, payment_status 
                                              FROM projects 
                                              WHERE $col_name = ? 
                                              ORDER BY created_at DESC LIMIT 10");
                $stmt_trans->bind_param("i", $company_id);
                $stmt_trans->execute();
                $transactions = $stmt_trans->get_result();

                if ($transactions->num_rows > 0):
                    while($row = $transactions->fetch_assoc()):
                        // تحديد لون الحالة
                        $status_color = 'bg-gray-500/20 text-gray-400';
                        $status_text = $L['FIN_STATUS_PENDING'];
                        if ($row['payment_status'] == 'paid') {
                            $status_color = 'bg-green-500/20 text-green-400';
                            $status_text = $L['FIN_STATUS_PAID'];
                        } elseif ($row['payment_status'] == 'failed') {
                            $status_color = 'bg-red-500/20 text-red-400';
                            $status_text = $L['FIN_STATUS_FAILED'];
                        }
                ?>
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4 font-mono text-brand-teal">#INV-<?php echo str_pad($row['project_id'], 5, '0', STR_PAD_LEFT); ?></td>
                    <td class="px-6 py-4 text-white font-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td class="px-6 py-4 text-gray-400"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td class="px-6 py-4 text-white font-bold"><?php echo number_format($row['total_amount'], 2); ?> SAR</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $status_color; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($row['payment_status'] == 'paid'): ?>
                        <button class="text-gray-400 hover:text-white transition-colors" title="<?php echo $L['FIN_BTN_DOWNLOAD']; ?>">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <?php else: ?>
                        <span class="text-gray-600">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <?php echo $L['FIN_NO_TRANSACTIONS']; ?>
                    </td>
                </tr>
                <?php endif; $stmt_trans->close(); ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
include '../../layout/dashboard_footer.php'; 
?>