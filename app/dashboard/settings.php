<?php
// dashboard/settings.php
// صفحة إعدادات الشركة (Company Profile & Settings)

include '../config/db_connect.php';
session_start();

// 1. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 2. إعدادات اللغة (مدمجة لضمان العمل)
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar');
$_SESSION['lang'] = $lang;
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$t = [
    'ar' => [
        'title' => 'إعدادات الشركة',
        'back' => 'عودة للرئيسية',
        'section_identity' => 'هوية الشركة',
        'lbl_logo' => 'شعار الشركة',
        'btn_change_logo' => 'تغيير الشعار',
        'lbl_name' => 'اسم الشركة (القانوني)',
        'lbl_cr' => 'رقم السجل التجاري',
        'section_details' => 'تفاصيل النشاط',
        'lbl_industry' => 'القطاع / الصناعة',
        'lbl_size' => 'حجم الشركة',
        'section_roles' => 'طبيعة العمل (الأدوار)',
        'role_requester' => 'شركة طالبة (أريد توظيف خبراء)',
        'role_supplier' => 'شركة موردة (أريد تأجير موظفي للغير)',
        'btn_save' => 'حفظ التغييرات',
        'msg_success' => 'تم تحديث إعدادات الشركة بنجاح.',
        'msg_error' => 'حدث خطأ أثناء التحديث.',
        'industries' => [
            'Tech' => 'تقنية المعلومات والبرمجة',
            'Marketing' => 'التسويق والإعلان',
            'Finance' => 'المالية والمحاسبة',
            'Engineering' => 'الهندسة والمقاولات',
            'Consulting' => 'الاستشارات الإدارية',
            'Other' => 'أخرى'
        ],
        'sizes' => [
            '1-10' => '1-10 موظفين (ناشئة)',
            '11-50' => '11-50 موظف (صغيرة)',
            '51-200' => '51-200 موظف (متوسطة)',
            '201+' => '+200 موظف (كبيرة)'
        ]
    ],
    'en' => [
        'title' => 'Company Settings',
        'back' => 'Back to Dashboard',
        'section_identity' => 'Company Identity',
        'lbl_logo' => 'Company Logo',
        'btn_change_logo' => 'Change Logo',
        'lbl_name' => 'Company Name (Legal)',
        'lbl_cr' => 'CR Number',
        'section_details' => 'Business Details',
        'lbl_industry' => 'Industry',
        'lbl_size' => 'Company Size',
        'section_roles' => 'Business Roles',
        'role_requester' => 'Requester (I want to hire experts)',
        'role_supplier' => 'Supplier (I want to lease my employees)',
        'btn_save' => 'Save Changes',
        'msg_success' => 'Company settings updated successfully.',
        'msg_error' => 'Error updating settings.',
        'industries' => [
            'Tech' => 'Information Technology',
            'Marketing' => 'Marketing & Advertising',
            'Finance' => 'Finance & Accounting',
            'Engineering' => 'Engineering',
            'Consulting' => 'Consulting',
            'Other' => 'Other'
        ],
        'sizes' => [
            '1-10' => '1-10 Employees',
            '11-50' => '11-50 Employees',
            '51-200' => '51-200 Employees',
            '201+' => '200+ Employees'
        ]
    ]
][$lang];

// 3. جلب بيانات الشركة الحالية
// نفترض أن المستخدم هو المالك أو مدير له صلاحية
$stmt = $conn->prepare("SELECT c.* FROM companies c JOIN users u ON c.company_id = u.company_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
    die("خطأ: لا يوجد ملف شركة مرتبط بهذا الحساب.");
}

$company_id = $company['company_id'];

// 4. معالجة الحفظ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = htmlspecialchars(trim($_POST['company_name']));
    $cr = htmlspecialchars(trim($_POST['cr_number']));
    $ind = htmlspecialchars(trim($_POST['industry']));
    $size = htmlspecialchars(trim($_POST['size']));
    
    // التعامل مع الـ Checkboxes
    $is_req = isset($_POST['is_requester']) ? 1 : 0;
    $is_sup = isset($_POST['is_supplier']) ? 1 : 0;

    // معالجة رفع الشعار
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = "logo_" . $company_id . "_" . time() . "." . $ext;
            $dir = "../uploads/companies/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dir . $new_name)) {
                $path = "uploads/companies/" . $new_name;
                $conn->query("UPDATE companies SET company_logo = '$path' WHERE company_id = $company_id");
                $company['company_logo'] = $path; // تحديث للعرض الفوري
            }
        }
    }

    // تحديث البيانات
    $update = $conn->prepare("UPDATE companies SET name=?, cr_number=?, industry=?, size=?, is_requester=?, is_supplier=? WHERE company_id=?");
    $update->bind_param("ssssiii", $name, $cr, $ind, $size, $is_req, $is_sup, $company_id);
    
    if ($update->execute()) {
        $msg = $t['msg_success'];
        $msg_type = "success";
        // تحديث المتغيرات للعرض
        $company['name'] = $name; $company['cr_number'] = $cr; $company['industry'] = $ind;
        $company['size'] = $size; $company['is_requester'] = $is_req; $company['is_supplier'] = $is_sup;
    } else {
        $msg = $t['msg_error'] . " " . $conn->error;
        $msg_type = "error";
    }
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
                <a href="?lang=ar" class="px-3 py-1 text-xs font-bold rounded <?php echo ($lang=='ar')?'bg-indigo-600 text-white':'bg-slate-100 text-slate-500'; ?>">العربية</a>
                <a href="?lang=en" class="px-3 py-1 text-xs font-bold rounded <?php echo ($lang=='en')?'bg-indigo-600 text-white':'bg-slate-100 text-slate-500'; ?>">English</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10 max-w-4xl">
        
        <?php if($msg): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 <?php echo ($msg_type=='success')?'bg-green-50 text-green-700 border border-green-200':'bg-red-50 text-red-700 border border-red-200'; ?>">
                <span class="font-bold"><?php echo $msg; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <!-- 1. بطاقة الهوية والشعار -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="font-bold text-lg text-slate-800 mb-6 pb-2 border-b border-slate-100"><?php echo $t['section_identity']; ?></h3>
                
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Logo Upload -->
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <div class="relative group cursor-pointer w-40 h-40">
                            <?php $logoSrc = !empty($company['company_logo']) ? "../".$company['company_logo'] : "https://via.placeholder.com/150?text=LOGO"; ?>
                            <img id="logoPreview" src="<?php echo $logoSrc; ?>" class="w-full h-full rounded-2xl border-2 border-dashed border-slate-300 object-contain p-2 group-hover:border-indigo-500 transition bg-white">
                            
                            <label for="logoInput" class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white rounded-2xl opacity-0 group-hover:opacity-100 transition duration-300 cursor-pointer">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-xs font-bold"><?php echo $t['btn_change_logo']; ?></span>
                            </label>
                            <input type="file" name="logo" id="logoInput" class="hidden" onchange="previewImage(this)">
                        </div>
                        <p class="text-xs text-slate-400 mt-2 text-center">PNG, JPG (Max 2MB)</p>
                    </div>

                    <!-- Info Inputs -->
                    <div class="w-full md:w-2/3 space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_name']; ?></label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['name']); ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition font-bold text-lg text-slate-800" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_cr']; ?></label>
                            <div class="relative">
                                <input type="text" name="cr_number" value="<?php echo htmlspecialchars($company['cr_number']); ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition bg-slate-50 font-mono tracking-wider">
                                <?php if($company['is_verified_provider']): ?>
                                    <div class="absolute top-3 <?php echo ($lang=='ar')?'left-3':'right-3'; ?> flex items-center gap-1 text-green-600 text-xs font-bold bg-green-100 px-2 py-1 rounded">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        موثق
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. التفاصيل والأدوار -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- تفاصيل النشاط -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <h3 class="font-bold text-lg text-slate-800 mb-6 pb-2 border-b border-slate-100"><?php echo $t['section_details']; ?></h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_industry']; ?></label>
                            <select name="industry" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none bg-white">
                                <?php foreach($t['industries'] as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php if($company['industry'] == $key) echo 'selected'; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2"><?php echo $t['lbl_size']; ?></label>
                            <select name="size" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none bg-white">
                                <?php foreach($t['sizes'] as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php if($company['size'] == $key) echo 'selected'; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- الأدوار (Switches) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <h3 class="font-bold text-lg text-slate-800 mb-6 pb-2 border-b border-slate-100"><?php echo $t['section_roles']; ?></h3>
                    
                    <div class="space-y-6">
                        <!-- Requester Switch -->
                        <div class="flex items-center justify-between">
                            <div class="w-3/4">
                                <h4 class="font-bold text-slate-800"><?php echo $t['role_requester']; ?></h4>
                                <p class="text-xs text-slate-500 mt-1">تفعيل ميزات البحث عن خبراء وتقديم الطلبات.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_requester" class="sr-only peer" <?php if($company['is_requester']) echo 'checked'; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Supplier Switch -->
                        <div class="flex items-center justify-between">
                            <div class="w-3/4">
                                <h4 class="font-bold text-slate-800"><?php echo $t['role_supplier']; ?></h4>
                                <p class="text-xs text-slate-500 mt-1">تفعيل أدوات إدارة الموظفين وعرض الخدمات للبيع.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_supplier" class="sr-only peer" <?php if($company['is_supplier']) echo 'checked'; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- زر الحفظ -->
            <div class="pt-6 border-t border-slate-200 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?php echo $t['btn_save']; ?>
                </button>
            </div>

        </form>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>