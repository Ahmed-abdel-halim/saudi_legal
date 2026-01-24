<?php // dashboard/provider/sales.php (المبيعات - مترجم) ?>
<?php 
include '../../layout/dashboard_header.php'; 

// --- 🔒 الحماية ---
if (!($role == 'owner' && $company_type == 'supplier')) {
    header("Location: /dashboard/index.php?error=unauthorized");
    exit();
}

// --- جلب الإحصائيات ---
// 1. مؤشرات الأداء الرئيسية (KPIs)
$stmt_kpi = $conn->prepare("SELECT 
                                SUM(total_amount) AS total_revenue,
                                SUM(CASE WHEN status = 'active' THEN total_amount ELSE 0 END) AS pending_revenue
                            FROM projects 
                            WHERE supplier_company_id = ? AND (status = 'active' OR status = 'completed')");
$stmt_kpi->bind_param("i", $company_id);
$stmt_kpi->execute();
$kpi_data = $stmt_kpi->get_result()->fetch_assoc();
$total_revenue = $kpi_data['total_revenue'] ?? 0;
$pending_revenue = $kpi_data['pending_revenue'] ?? 0;
$platform_fee = $total_revenue * 0.15; // افتراض 15%
$net_earnings = $total_revenue - $platform_fee;
$stmt_kpi->close();

// 2. بيانات الرسم البياني (Revenue by Month)
$chart_labels = [];
$chart_data = [];
$stmt_chart = $conn->prepare("SELECT 
                                MONTHNAME(created_at) AS month_name,
                                SUM(total_amount) AS monthly_revenue
                            FROM projects
                            WHERE supplier_company_id = ? AND status = 'completed' AND YEAR(created_at) = YEAR(CURDATE())
                            GROUP BY MONTH(created_at), month_name
                            ORDER BY MONTH(created_at)");
$stmt_chart->bind_param("i", $company_id);
$stmt_chart->execute();
$chart_result = $stmt_chart->get_result();
while($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = $row['month_name'];
    $chart_data[] = $row['monthly_revenue'];
}
$stmt_chart->close();

?>
<!-- تحميل مكتبة Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h1 class="text-3xl font-bold text-white mb-8"><?php echo $L['SUPPLIER_SALES_TITLE']; ?></h1>

<!-- مؤشرات الأداء الرئيسية -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-slate-card p-6 rounded-lg">
        <h4 class="text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_KPI_TOTAL_REV']; ?></h4>
        <p class="text-4xl font-extrabold text-white"><?php echo number_format($total_revenue, 2); ?> <span class="text-lg font-normal">ر.س</span></p>
    </div>
    <div class="bg-slate-card p-6 rounded-lg">
        <h4 class="text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_KPI_NET_PROFIT']; ?></h4>
        <p class="text-4xl font-extrabold text-brand-cyan"><?php echo number_format($net_earnings, 2); ?> <span class="text-lg font-normal">ر.س</span></p>
    </div>
    <div class="bg-slate-card p-6 rounded-lg">
        <h4 class="text-sm font-medium text-gray-400 mb-2"><?php echo $L['SUPPLIER_KPI_PENDING']; ?></h4>
        <p class="text-4xl font-extrabold text-yellow-400"><?php echo number_format($pending_revenue, 2); ?> <span class="text-lg font-normal">ر.س</span></p>
    </div>
</div>

<!-- الرسم البياني -->
<div class="bg-slate-card p-6 rounded-lg shadow-lg mb-8">
    <h2 class="text-2xl font-bold text-white mb-6"><?php echo $L['SUPPLIER_CHART_TITLE']; ?></h2>
    <canvas id="revenueChart"></canvas>
</div>

<!-- جدول العقود/المشاريع المكتملة -->
<div class="bg-slate-card p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-white mb-6"><?php echo $L['SUPPLIER_TABLE_CONTRACTS_TITLE']; ?></h2>
    <div class="overflow-x-auto">
        <table class="w-full <?php echo ($direction == 'rtl') ? 'text-right' : 'text-left'; ?>">
            <thead>
                <tr class="border-b border-slate-700 text-sm text-gray-400">
                    <th class="py-3 pr-4"><?php echo $L['SUPPLIER_TABLE_PROJECT']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['SUPPLIER_TABLE_REQUESTER']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['SUPPLIER_TABLE_EXPERT']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['SUPPLIER_TABLE_TOTAL']; ?></th>
                    <th class="py-3 px-4"><?php echo $L['SUPPLIER_TABLE_NET']; ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                <?php
                $stmt_table = $conn->prepare("SELECT p.title, p.total_amount, c.name AS requester_name, u.full_name AS expert_name 
                                              FROM projects p
                                              JOIN companies c ON p.requester_company_id = c.company_id
                                              JOIN users u ON p.expert_user_id = u.user_id
                                              WHERE p.supplier_company_id = ? AND p.status = 'completed'");
                $stmt_table->bind_param("i", $company_id);
                $stmt_table->execute();
                $projects = $stmt_table->get_result();
                if ($projects->num_rows > 0): while($proj = $projects->fetch_assoc()):
                ?>
                <tr>
                    <td class="py-4 pr-4 text-white"><?php echo htmlspecialchars($proj['title']); ?></td>
                    <td class="py-4 px-4 text-gray-400"><?php echo htmlspecialchars($proj['requester_name']); ?></td>
                    <td class="py-4 px-4 text-gray-400"><?php echo htmlspecialchars($proj['expert_name']); ?></td>
                    <td class="py-4 px-4 text-white"><?php echo number_format($proj['total_amount'], 2); ?> ر.س</td>
                    <td class="py-4 px-4 text-brand-cyan"><?php echo number_format($proj['total_amount'] * 0.85, 2); ?> ر.س</td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="py-6 text-center text-gray-500"><?php echo $L['SUPPLIER_NO_SALES']; ?></td></tr>
                <?php endif; $stmt_table->close(); ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // إعدادات التدرج اللوني
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(27, 122, 126, 0.8)'); // Teal
    gradient.addColorStop(1, 'rgba(95, 211, 211, 0.1)'); // Cyan (شفاف)

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: '<?php echo $L['SUPPLIER_CHART_LABEL']; ?>',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#1B7A7E', // Teal
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#94a3b8' }, // لون خطوط الشبكة
                    grid: { color: '#334155' } // لون أرقام المحور
                },
                x: {
                    ticks: { color: '#94a3b8' },
                    grid: { color: '#334155' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#e2e8f0' } // لون العنوان
                }
            },
            maintainAspectRatio: false
        }
    });
});
</script>

<?php 
include '../../layout/dashboard_footer.php'; 
?>