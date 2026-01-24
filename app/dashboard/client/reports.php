<?php
// dashboard/client/reports.php

include '../../config/db_connect.php';
session_start();

// تفعيل ميزة التصدير (Export to CSV)
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cleaned_data_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    // الترويسة (BOM للعربية)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['ID', 'Original Text', 'Corrected Text', 'Expert Name', 'Date']);

    $rows = $conn->query("
        SELECT t.id, t.original_text, r.correction, u.full_name, r.created_at 
        FROM ai_responses_v2 r
        JOIN ai_tasks_v2 t ON r.task_id = t.id
        JOIN users u ON r.expert_id = u.user_id
        ORDER BY r.id DESC
    ");
    
    while ($row = $rows->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// إحصائيات سريعة
$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM ai_tasks_v2) as total,
    (SELECT COUNT(*) FROM ai_tasks_v2 WHERE status='completed') as completed,
    (SELECT COUNT(*) FROM ai_tasks_v2 WHERE status='pending') as pending
")->fetch_assoc();

$percent = ($stats['total'] > 0) ? round(($stats['completed'] / $stats['total']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقارير التنقيح | Radiif Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Tajawal', sans-serif; background-color: #f1f5f9; } </style>
</head>
<body class="text-slate-800">

    <nav class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-6 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="text-2xl font-bold tracking-tighter text-green-400">Radiif</div>
                <div class="bg-slate-800 px-3 py-1 rounded-full text-xs font-bold text-slate-300">CLIENT VIEW</div>
            </div>
            <a href="?export=csv" class="bg-green-600 hover:bg-green-500 text-white px-5 py-2 rounded-lg font-bold text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> تحميل البيانات (CSV)
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border-r-4 border-blue-500">
                <div class="text-slate-500 text-sm font-bold mb-1">إجمالي المدخلات</div>
                <div class="text-3xl font-bold"><?php echo $stats['total']; ?> <span class="text-sm font-normal text-slate-400">نص</span></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-r-4 border-green-500">
                <div class="text-slate-500 text-sm font-bold mb-1">تم التنقيح (جاهز)</div>
                <div class="text-3xl font-bold text-green-600"><?php echo $stats['completed']; ?> <span class="text-sm font-normal text-slate-400">نص</span></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-r-4 border-orange-400">
                <div class="text-slate-500 text-sm font-bold mb-1">قيد المعالجة</div>
                <div class="text-3xl font-bold text-orange-500"><?php echo $stats['pending']; ?> <span class="text-sm font-normal text-slate-400">نص</span></div>
            </div>
        </div>

        <div class="mb-10">
            <div class="flex justify-between mb-2">
                <span class="font-bold text-slate-700">نسبة إنجاز المشروع</span>
                <span class="font-bold text-green-600"><?php echo $percent; ?>%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden">
                <div class="bg-green-600 h-4 rounded-full transition-all duration-1000" style="width: <?php echo $percent; ?>%"></div>
            </div>
        </div>

        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass-chart text-green-600"></i> مراجعة الجودة
        </h2>

        <div class="space-y-6">
            <?php
            // جلب البيانات المصححة
            $query = "
                SELECT t.id, t.original_text, r.correction, u.full_name, u.profile_picture, r.created_at 
                FROM ai_responses_v2 r
                JOIN ai_tasks_v2 t ON r.task_id = t.id
                JOIN users u ON r.expert_id = u.user_id
                ORDER BY r.id DESC
            ";
            $result = $conn->query($query);

            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
            
            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-slate-200">
                
                <div class="bg-slate-50 px-6 py-3 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden">
                            <?php $img = !empty($row['profile_picture']) ? "../../".$row['profile_picture'] : "https://ui-avatars.com/api/?name=".$row['full_name']; ?>
                            <img src="<?php echo $img; ?>" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-700">قام بالتصحيح: <?php echo htmlspecialchars($row['full_name']); ?></div>
                            <div class="text-xs text-slate-400"><?php echo date('Y/m/d - h:i A', strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>
                    <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-bold">معتمد ✅</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-slate-100">
                    
                    <div class="p-6 bg-red-50/30">
                        <span class="block text-xs font-bold text-red-400 uppercase tracking-widest mb-3">النص الأصلي (Raw Input)</span>
                        <p class="text-slate-600 leading-relaxed line-through decoration-red-300 decoration-2">
                            <?php echo htmlspecialchars($row['original_text']); ?>
                        </p>
                    </div>

                    <div class="p-6 bg-green-50/30">
                        <span class="block text-xs font-bold text-green-600 uppercase tracking-widest mb-3">التنقيح المعتمد (Clean Data)</span>
                        <p class="text-slate-800 font-medium leading-relaxed text-lg">
                            <?php echo htmlspecialchars($row['correction']); ?>
                        </p>
                    </div>

                </div>
            </div>

            <?php endwhile; else: ?>
                <div class="text-center py-20 text-slate-400">
                    <i class="fa-solid fa-folder-open text-4xl mb-4"></i>
                    <p>لا توجد بيانات تم تصحيحها حتى الآن.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>