<?php
// dashboard/expert/availability.php
include '../../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// حفظ الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = $_POST['days'] ?? [];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // تحويل المصفوفة لنص JSON للحفظ (أسهل طريقة للآن)
    $availability_json = json_encode(['days' => $days, 'start' => $start_time, 'end' => $end_time]);

    // تحديث قاعدة البيانات (نفترض وجود عمود availability في جدول users)
    // إذا لم يكن العمود موجوداً، سنستخدم جدولاً منفصلاً أو نضيفه.
    // للسرعة الآن، سنضيف عمود 'availability_settings' لجدول users
    $stmt = $conn->prepare("UPDATE users SET availability_settings = ?, is_active_for_hire = ? WHERE user_id = ?");
    $stmt->bind_param("sii", $availability_json, $is_active, $user_id);
    
    if ($stmt->execute()) {
        $msg = "✅ تم تحديث أوقات العمل بنجاح. أنت الآن جاهز للاستقبال!";
    }
}

// جلب الإعدادات الحالية
$stmt = $conn->prepare("SELECT availability_settings, is_active_for_hire FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$settings = json_decode($data['availability_settings'] ?? '{"days":[], "start":"09:00", "end":"17:00"}', true);
$active_days = $settings['days'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعدادات التوفر | TimeShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center max-w-2xl">
            <span class="font-bold text-lg text-slate-800">إعدادات التوفر (الدوام)</span>
            <a href="index.php" class="text-indigo-600 font-bold text-sm">عودة</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        
        <?php if($msg): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 text-sm font-bold">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">متى يمكن حجزك؟</h2>
                    <p class="text-slate-500 text-sm mt-1">حدد أيام وساعات عملك المعتادة لتظهر للعملاء.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" form="availabilityForm" name="is_active" class="sr-only peer" <?php echo ($data['is_active_for_hire']) ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    <span class="mr-3 text-sm font-bold text-slate-700">متاح للحجز</span>
                </label>
            </div>

            <form id="availabilityForm" method="POST" class="space-y-6">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3">أيام العمل الأسبوعية</label>
                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-7">
                        <?php 
                        $days_map = ['Sun'=>'الأحد', 'Mon'=>'الاثنين', 'Tue'=>'الثلاثاء', 'Wed'=>'الأربعاء', 'Thu'=>'الخميس', 'Fri'=>'الجمعة', 'Sat'=>'السبت'];
                        foreach ($days_map as $key => $label): 
                            $checked = in_array($key, $active_days) ? 'checked' : '';
                            $bg_class = in_array($key, $active_days) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300';
                        ?>
                            <label class="cursor-pointer relative">
                                <input type="checkbox" name="days[]" value="<?php echo $key; ?>" class="peer sr-only" <?php echo $checked; ?>>
                                <div class="w-full py-2 rounded-lg border-2 text-center text-xs font-bold transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                                    <?php echo $label; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">من الساعة</label>
                        <input type="time" name="start_time" value="<?php echo $settings['start']; ?>" class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-800 focus:border-indigo-500 focus:bg-white outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">إلى الساعة</label>
                        <input type="time" name="end_time" value="<?php echo $settings['end']; ?>" class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-800 focus:border-indigo-500 focus:bg-white outline-none transition">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-200 transition transform active:scale-95 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        حفظ وتحديث التوفر
                    </button>
                </div>

            </form>
        </div>

        <div class="mt-6 text-center">
            <p class="text-xs text-slate-400">
                ⚠️ بتفعيلك للحجز، أنت توافق على استقبال الطلبات الفورية خلال هذه الأوقات.
                <br> عدم الاستجابة قد يعرض حساب الشركة للإيقاف المؤقت.
            </p>
        </div>

    </div>
</body>
</html>