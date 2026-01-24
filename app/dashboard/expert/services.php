<?php
// dashboard/expert/services.php
include '../../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// 1. إضافة خدمة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $title = htmlspecialchars(trim($_POST['title']));
    $category = htmlspecialchars(trim($_POST['category']));
    $price = floatval($_POST['price']);
    $days = intval($_POST['delivery_days']);
    $desc = htmlspecialchars(trim($_POST['description']));

    if (!empty($title) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO expert_services (expert_id, title, category, price, delivery_days, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdis", $user_id, $title, $category, $price, $days, $desc);
        if ($stmt->execute()) {
            $msg = "✅ تم إضافة الباقة بنجاح!";
        }
    }
}

// 2. حذف خدمة
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM expert_services WHERE service_id = $id AND expert_id = $user_id");
    header("Location: services.php");
    exit();
}

// جلب الخدمات
$services = $conn->query("SELECT * FROM expert_services WHERE expert_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الخدمات | TimeShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-slate-50 pb-20">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 px-4 py-3 shadow-sm">
        <div class="container mx-auto max-w-5xl flex justify-between items-center">
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span class="text-2xl">📦</span> باقاتي وخدماتي
            </h1>
            <a href="index.php" class="text-indigo-600 font-bold hover:bg-indigo-50 px-3 py-1 rounded transition">عودة للرئيسية</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-5xl">
        
        <?php if($msg): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- نموذج الإضافة (ثابت) -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                    <h2 class="font-bold text-lg text-slate-800 mb-4">إضافة باقة جديدة</h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">عنوان الخدمة</label>
                            <input type="text" name="title" placeholder="مثال: مراجعة كود PHP" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">التصنيف</label>
                            <select name="category" class="w-full p-2 border rounded-lg bg-white outline-none">
                                <option value="Tech">تقنية وبرمجة</option>
                                <option value="Design">تصميم وإبداع</option>
                                <option value="Marketing">تسويق</option>
                                <option value="Consulting">استشارات</option>
                                <option value="Other">أخرى</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">السعر (ريال)</label>
                                <input type="number" name="price" placeholder="500" class="w-full p-2 border rounded-lg font-bold text-emerald-600 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">مدة التسليم (يوم)</label>
                                <input type="number" name="delivery_days" placeholder="2" class="w-full p-2 border rounded-lg outline-none" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">ماذا ستقدم؟ (الوصف)</label>
                            <textarea name="description" rows="3" placeholder="- تقرير مفصل&#10;- إصلاح الأخطاء..." class="w-full p-2 border rounded-lg resize-none outline-none" required></textarea>
                        </div>
                        <button type="submit" name="add_service" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold shadow transition">
                            + نشر الخدمة
                        </button>
                    </form>
                </div>
            </div>

            <!-- قائمة الخدمات -->
            <div class="lg:col-span-2 space-y-4">
                <?php if($services->num_rows > 0): ?>
                    <?php while($row = $services->fetch_assoc()): ?>
                        <div class="bg-white p-5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:shadow-md transition relative group">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-1 rounded-full font-bold uppercase"><?php echo $row['category']; ?></span>
                                    <h3 class="font-bold text-lg text-slate-800 mt-1"><?php echo $row['title']; ?></h3>
                                </div>
                                <div class="text-left">
                                    <span class="block font-black text-xl text-emerald-600"><?php echo $row['price']; ?> ر.س</span>
                                    <span class="text-xs text-slate-400 font-bold">تسليم <?php echo $row['delivery_days']; ?> أيام</span>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 mt-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <?php echo nl2br($row['description']); ?>
                            </p>
                            
                            <a href="?delete=<?php echo $row['service_id']; ?>" onclick="return confirm('حذف هذه الخدمة؟')" class="absolute top-4 left-4 text-slate-300 hover:text-red-500 transition p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h3 class="font-bold text-slate-800">لا توجد خدمات مضافة</h3>
                        <p class="text-sm text-slate-500 mt-1">ابدأ بإضافة باقتك الأولى من النموذج الجانبي</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>