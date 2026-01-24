<?php
include '../../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// محاكاة عملية التحقق (في الواقع تحتاج لربط مع بوابة SMS)
if (isset($_POST['verify_phone'])) {
    // هنا يتم إرسال كود OTP للجوال
    // سنفترض أن المستخدم أدخل الكود الصحيح مباشرة للتجربة
    $conn->query("UPDATE users SET is_phone_verified = 1 WHERE user_id = $user_id");
    $msg = "تم توثيق رقم الجوال بنجاح! ✅";
}

// جلب البيانات
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعدادات الحساب والتوثيق</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-slate-50">

    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center max-w-4xl">
            <span class="font-bold text-lg text-slate-800">الإعدادات والتوثيق</span>
            <a href="index.php" class="text-indigo-600 font-bold hover:underline">العودة للرئيسية</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        
        <?php if($msg): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <h3 class="font-bold text-lg mb-4">البريد الإلكتروني</h3>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-slate-600"><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if($user['is_email_verified']): ?>
                        <span class="text-xs text-green-600 font-bold flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            تم التحقق
                        </span>
                    <?php else: ?>
                        <span class="text-xs text-red-500 font-bold mt-1">غير موثق</span>
                    <?php endif; ?>
                </div>
                <?php if(!$user['is_email_verified']): ?>
                    <button class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-100">إرسال رابط التفعيل</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <h3 class="font-bold text-lg mb-4">رقم الجوال</h3>
            <div class="flex justify-between items-center">
                <div class="w-full">
                    <?php if(!empty($user['phone'])): ?>
                        <p class="text-slate-800 text-lg font-mono ltr text-right mb-2"><?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php else: ?>
                        <input type="text" placeholder="05xxxxxxxx" class="w-full border p-2 rounded mb-2">
                    <?php endif; ?>

                    <?php if($user['is_phone_verified']): ?>
                        <span class="text-xs text-green-600 font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            تم التحقق
                        </span>
                    <?php else: ?>
                        <span class="text-xs text-orange-500 font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            غير موثق
                        </span>
                        <form method="POST" class="mt-3">
                            <button type="submit" name="verify_phone" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 w-full md:w-auto">
                                إرسال كود التحقق (OTP)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-slate-100 p-6 rounded-xl border border-slate-200">
            <h3 class="font-bold text-slate-700 mb-2">الهوية والتبعية القانونية</h3>
            <p class="text-sm text-slate-500 mb-4">يتم إدارة توثيق الهوية والارتباط الوظيفي من قبل الشركة المسؤولة عنك.</p>
            
            <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200">
                <div class="bg-gray-200 p-2 rounded-full">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-400">الشركة المسجلة</p>
                    <p class="font-bold text-slate-800">Tech Solutions Co.</p>
                </div>
                <span class="mr-auto text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-bold">نشط</span>
            </div>
        </div>

    </div>
</body>
</html>