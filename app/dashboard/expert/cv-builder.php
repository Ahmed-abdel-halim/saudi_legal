<?php
// dashboard/expert/cv-builder.php
// النسخة النهائية: تشمل ربط الشركة + بطاقة التوثيق + التقييمات

include '../../config/db_connect.php';
session_start();

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// --- 1. معالجة AJAX (إضافة مهارة جديدة) ---
if (isset($_POST['action']) && $_POST['action'] === 'add_new_skill') {
    header('Content-Type: application/json');
    $new_skill_name = htmlspecialchars(trim($_POST['skill_name']));
    $category_key = htmlspecialchars(trim($_POST['skill_category'])); 
    
    // جلب الاسم العربي للتصنيف
    $stmt_cat = $conn->prepare("SELECT category_ar FROM skills WHERE category = ? LIMIT 1");
    $stmt_cat->bind_param("s", $category_key);
    $stmt_cat->execute();
    $res_cat = $stmt_cat->get_result();
    $cat_ar = ($res_cat->num_rows > 0) ? $res_cat->fetch_assoc()['category_ar'] : 'أخرى';
    
    if (empty($new_skill_name)) {
        echo json_encode(['status' => 'error', 'msg' => 'الاسم مطلوب']);
        exit;
    }

    $check = $conn->prepare("SELECT skill_id FROM skills WHERE name = ? OR name_ar = ?");
    $check->bind_param("ss", $new_skill_name, $new_skill_name);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo json_encode(['status' => 'success', 'id' => $row['skill_id'], 'name' => $new_skill_name]);
    } else {
        $insert = $conn->prepare("INSERT INTO skills (name, name_ar, category, category_ar) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $new_skill_name, $new_skill_name, $category_key, $cat_ar);
        if ($insert->execute()) {
            echo json_encode(['status' => 'success', 'id' => $insert->insert_id, 'name' => $new_skill_name]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'فشل الحفظ']);
        }
    }
    exit;
}

// --- 2. معالجة الحفظ الرئيسي (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {
    
    // استخدام trim فقط للحفظ (لمنع التشفير المزدوج)
    $job_title = trim($_POST['job_title']);
    $job_title_ar = trim($_POST['job_title_ar']);
    $bio = trim($_POST['bio']);
    $bio_ar = trim($_POST['bio_ar']);
    $education = trim($_POST['education']);
    $education_ar = trim($_POST['education_ar']);
    $languages = trim($_POST['languages']);
    $experience_years = intval($_POST['experience_years']);
    $rate = floatval($_POST['rate']);
    $linkedin = filter_var($_POST['linkedin'], FILTER_SANITIZE_URL);
    $portfolio_url = filter_var($_POST['portfolio_url'], FILTER_SANITIZE_URL);

    // دالة رفع الصور
    function uploadImage($file, $prefix, $user_id) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = $prefix . "_" . $user_id . "_" . time() . "." . $ext;
            $dir = "../../uploads/" . ($prefix == 'cover' ? 'covers/' : 'avatars/');
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            if (move_uploaded_file($file['tmp_name'], $dir . $new_name)) {
                return "uploads/" . ($prefix == 'cover' ? 'covers/' : 'avatars/') . $new_name;
            }
        }
        return false;
    }

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $path = uploadImage($_FILES['profile_pic'], 'avatar', $user_id);
        if ($path) $conn->query("UPDATE users SET profile_picture = '$path' WHERE user_id = $user_id");
    }

    if (isset($_FILES['cover_pic']) && $_FILES['cover_pic']['error'] == 0) {
        $path = uploadImage($_FILES['cover_pic'], 'cover', $user_id);
        if ($path) $conn->query("UPDATE users SET cover_picture = '$path' WHERE user_id = $user_id");
    }

    // تحديث البيانات
    $stmt = $conn->prepare("UPDATE users SET job_title=?, job_title_ar=?, bio=?, bio_ar=?, education=?, education_ar=?, languages=?, experience_years=?, linkedin_url=?, portfolio_url=?, hourly_rate=? WHERE user_id=?");
    $stmt->bind_param("sssssssdssdi", $job_title, $job_title_ar, $bio, $bio_ar, $education, $education_ar, $languages, $experience_years, $linkedin, $portfolio_url, $rate, $user_id);
    
    if ($stmt->execute()) {
        // حفظ المهارات
        if (isset($_POST['skills_list'])) {
            $conn->query("DELETE FROM user_skills WHERE user_id = $user_id");
            $skills_ids = explode(',', $_POST['skills_list']);
            if (!empty($skills_ids)) {
                $stmt_skill = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
                foreach ($skills_ids as $sid) {
                    $sid = intval($sid);
                    if ($sid > 0) {
                        $stmt_skill->bind_param("ii", $user_id, $sid);
                        $stmt_skill->execute();
                    }
                }
            }
        }
        $message = "تم تحديث الملف الشخصي بنجاح! 🎉";
        $msg_type = "success";
    } else {
        $message = "حدث خطأ: " . $conn->error;
        $msg_type = "error";
    }
}

// --- جلب البيانات مع ربط الشركة (JOIN Corrected) ---
// نستخدم LEFT JOIN لضمان ظهور المستخدم حتى لو لم يكن مرتبطاً بشركة
// نستخدم الأسماء الصحيحة للأعمدة حسب الصور (companies.name, companies.is_verified_provider)
$stmt = $conn->prepare("
    SELECT 
        u.*, 
        c.name AS company_name, 
        c.company_logo, 
        c.is_verified_provider AS is_company_verified
    FROM users u 
    LEFT JOIN companies c ON u.company_id = c.company_id 
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// جلب المهارات
$all_skills_query = $conn->query("SELECT * FROM skills ORDER BY category, name_ar");
$skills_by_cat = [];
$categories_info = []; 
while ($row = $all_skills_query->fetch_assoc()) {
    $skills_by_cat[$row['category']][] = $row;
    if (!isset($categories_info[$row['category']])) $categories_info[$row['category']] = $row['category_ar'];
}

$my_skills_query = $conn->query("SELECT skill_id FROM user_skills WHERE user_id = $user_id");
$my_skills = [];
while ($row = $my_skills_query->fetch_assoc()) $my_skills[] = $row['skill_id'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الملف الشخصي | TimeShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; }
        .skill-chip { transition: all 0.2s; cursor: pointer; user-select: none; }
        .skill-chip.selected { background-color: #4F46E5; color: white; border-color: #4F46E5; }
        .skill-chip:hover:not(.selected) { background-color: #EEF2FF; border-color: #6366F1; }
        .lang-btn.active { background-color: #4F46E5; color: white; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .cover-overlay { background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.6) 100%); }
    </style>
</head>
<body class="pb-20">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center max-w-6xl">
            <div class="flex items-center gap-3">
                <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 p-2 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <span class="font-bold text-lg text-slate-800">بناء الملف الشخصي</span>
            </div>
            <div class="flex gap-2">
                <a href="index.php" class="text-slate-500 hover:text-slate-700 px-4 py-2 font-bold transition">إلغاء</a>
                <button onclick="document.getElementById('mainForm').submit();" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-bold shadow-lg shadow-indigo-200 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>حفظ ونشر</span>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6 max-w-6xl">
        
        <?php if ($message): ?>
            <div class="mb-6 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm <?php echo ($msg_type == 'success') ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                <span class="font-bold"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form id="mainForm" method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="skills_list" id="skillsInput" value="<?php echo implode(',', $my_skills); ?>">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <div class="lg:col-span-8 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden group">
                        <div class="relative h-48 bg-slate-200">
                            <?php $cover = !empty($user['cover_picture']) ? "../../" . $user['cover_picture'] : "https://via.placeholder.com/800x300/4F46E5/FFFFFF?text=Cover+Image"; ?>
                            <img id="previewCover" src="<?php echo $cover; ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 cover-overlay"></div>
                            <label for="coverUpload" class="absolute top-4 left-4 bg-black/50 hover:bg-black/70 text-white px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition flex items-center gap-2 backdrop-blur-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                تغيير الغلاف
                            </label>
                            <input id="coverUpload" type="file" name="cover_pic" onchange="previewFile('coverUpload', 'previewCover')">
                        </div>

                        <div class="px-8 pb-8 relative">
                            <div class="flex justify-between items-end -mt-12 mb-6">
                                <div class="relative">
                                    <label for="profileUpload" class="cursor-pointer block relative">
                                        <?php $avatar = !empty($user['profile_picture']) ? "../../" . $user['profile_picture'] : "https://ui-avatars.com/api/?name=".urlencode($user['full_name'])."&size=150&background=ffffff&color=4f46e5"; ?>
                                        <img id="previewProfile" src="<?php echo $avatar; ?>" class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover bg-white">
                                        <div class="absolute bottom-1 right-1 bg-indigo-600 text-white p-1.5 rounded-full shadow-md border-2 border-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </div>
                                    </label>
                                    <input id="profileUpload" type="file" name="profile_pic" onchange="previewFile('profileUpload', 'previewProfile')">
                                </div>
                                <div class="bg-slate-100 p-1 rounded-lg flex gap-1 mb-2">
                                    <button type="button" onclick="setLang('ar')" id="btn-ar" class="lang-btn active px-4 py-1.5 rounded-md text-xs font-bold transition">العربية</button>
                                    <button type="button" onclick="setLang('en')" id="btn-en" class="lang-btn px-4 py-1.5 rounded-md text-xs font-bold text-slate-500 transition">English</button>
                                </div>
                            </div>

                            <div id="section-ar">
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">المسمى الوظيفي</label>
                                    <input type="text" name="job_title_ar" value="<?php echo htmlspecialchars($user['job_title_ar'] ?? ''); ?>" placeholder="مثال: خبير تسويق رقمي" class="w-full text-2xl font-bold text-slate-800 bg-transparent border-b-2 border-slate-200 focus:border-indigo-600 focus:outline-none transition py-2">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">النبذة التعريفية</label>
                                    <textarea name="bio_ar" rows="4" placeholder="تحدث عن خبراتك وإنجازاتك..." class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 outline-none transition resize-none text-slate-700 leading-relaxed"><?php echo htmlspecialchars($user['bio_ar'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">التعليم والشهادات</label>
                                    <textarea name="education_ar" rows="3" placeholder="- بكالوريوس حاسب آلي، جامعة الملك سعود (2015)&#10;- شهادة PMP إدارة مشاريع احترافية" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 outline-none transition resize-none text-slate-700 leading-relaxed"><?php echo htmlspecialchars($user['education_ar'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div id="section-en" class="hidden" dir="ltr">
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Professional Headline</label>
                                    <input type="text" name="job_title" value="<?php echo htmlspecialchars($user['job_title'] ?? ''); ?>" placeholder="e.g. Senior Digital Marketer" class="w-full text-2xl font-bold text-slate-800 bg-transparent border-b-2 border-slate-200 focus:border-indigo-600 focus:outline-none transition py-2">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Overview</label>
                                    <textarea name="bio" rows="4" placeholder="Highlight your top skills and experience..." class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 outline-none transition resize-none text-slate-700 leading-relaxed"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Education & Certifications</label>
                                    <textarea name="education" rows="3" placeholder="- BSc Computer Science, KSU (2015)&#10;- PMP Certification" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 outline-none transition resize-none text-slate-700 leading-relaxed"><?php echo htmlspecialchars($user['education'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-xl text-slate-800">المهارات التقنية</h3>
                            <button type="button" onclick="document.getElementById('addSkillModal').classList.remove('hidden')" class="text-sm font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition flex items-center gap-1">+ مهارة جديدة</button>
                        </div>
                        <div class="flex gap-2 overflow-x-auto pb-4 mb-4 no-scrollbar">
                            <button type="button" onclick="filterSkills('all')" class="px-4 py-1.5 rounded-full text-sm font-bold bg-slate-800 text-white hover:bg-slate-700 transition category-btn active" data-cat="all">الكل</button>
                            <?php foreach ($categories_info as $key => $ar_name): ?>
                                <button type="button" onclick="filterSkills('<?php echo $key; ?>')" class="px-4 py-1.5 rounded-full text-sm font-bold bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition category-btn whitespace-nowrap" data-cat="<?php echo $key; ?>"><?php echo $ar_name; ?></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-6 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <div class="flex flex-wrap gap-2" id="skillsContainer">
                                <?php foreach ($skills_by_cat as $category => $skills): ?>
                                    <?php foreach ($skills as $skill): ?>
                                        <div class="skill-item skill-chip px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white" data-id="<?php echo $skill['skill_id']; ?>" data-category="<?php echo $category; ?>" onclick="toggleSkill(this, <?php echo $skill['skill_id']; ?>)"><?php echo htmlspecialchars($skill['name_ar']); ?></div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-xs font-bold text-slate-500 uppercase">المهارات المختارة:</h4>
                                <span id="countSkills" class="text-xs font-bold text-indigo-600">0</span>
                            </div>
                            <div id="selectedSkillsArea" class="flex flex-wrap gap-2 text-sm"><span class="text-slate-400 italic text-xs">...</span></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 opacity-60">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-xl text-slate-800">معرض الأعمال (Portfolio)</h3>
                            <span class="bg-orange-100 text-orange-600 text-xs px-2 py-1 rounded font-bold">قريباً</span>
                        </div>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center bg-slate-50">
                            <p class="text-slate-500 text-sm">أضف روابط لمشاريعك السابقة لإقناع العملاء.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-sm">التبعية والتوثيق</h3>
                            <?php if(!empty($user['is_company_verified'])): ?>
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    شركة موثقة
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-6">
                                <?php 
                                    $comp_logo = !empty($user['company_logo']) ? "../../".$user['company_logo'] : "https://ui-avatars.com/api/?name=".urlencode($user['company_name'] ?? 'C')."&background=random&size=100"; 
                                ?>
                                <img src="<?php echo $comp_logo; ?>" class="w-12 h-12 rounded-lg border border-slate-200 object-contain p-1 bg-white">
                                <div>
                                    <p class="text-xs text-slate-400">خبير معتمد لدى:</p>
                                    <h4 class="font-bold text-slate-800 text-sm">
                                        <?php echo !empty($user['company_name']) ? htmlspecialchars($user['company_name']) : 'مستقل (غير تابع لشركة)'; ?>
                                    </h4>
                                </div>
                            </div>

                            <div class="space-y-3 border-t border-slate-100 pt-4">
                                <p class="text-xs text-slate-400 font-bold mb-2">التحقق من الخبير:</p>
                                
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                        الهوية الشخصية
                                    </span>
                                    <?php if(!empty($user['is_identity_verified'])): ?>
                                        <span class="text-green-600 font-bold text-xs flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></span>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs">-</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        البريد الإلكتروني
                                    </span>
                                    <?php if(!empty($user['is_email_verified'])): ?>
                                        <span class="text-green-600 font-bold text-xs flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></span>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs">-</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        رقم الجوال
                                    </span>
                                    <?php if(!empty($user['is_phone_verified'])): ?>
                                        <span class="text-green-600 font-bold text-xs flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></span>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100 bg-yellow-50 -mx-5 -mb-5 p-4 flex items-center justify-between">
                                <span class="text-xs text-yellow-800 font-bold">تقييم العملاء</span>
                                <div class="flex items-center gap-1">
                                    <div class="flex text-yellow-500">
                                        <?php 
                                            $rating = $user['rating'] ?? 0;
                                            for($i=1; $i<=5; $i++): 
                                        ?>
                                            <svg class="w-4 h-4 <?php echo ($i <= round($rating)) ? 'fill-current' : 'text-yellow-200 fill-current'; ?>" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-xs text-yellow-700 font-bold">(<?php echo $user['reviews_count'] ?? 0; ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">تفاصيل المهنة</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">سعر الساعة (SAR)</label>
                                <div class="relative">
                                    <input type="number" name="rate" value="<?php echo $user['hourly_rate']; ?>" class="w-full pl-4 pr-10 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-bold text-lg">
                                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm">ريال</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">سنوات الخبرة</label>
                                <div class="relative">
                                    <input type="number" name="experience_years" value="<?php echo $user['experience_years'] ?? 0; ?>" class="w-full pl-4 pr-10 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none font-bold text-lg">
                                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm">سنة</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">اللغات</h3>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">اللغات التي تتقنها</label>
                            <input type="text" name="languages" value="<?php echo htmlspecialchars($user['languages'] ?? ''); ?>" placeholder="مثال: العربية، الإنجليزية" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none text-sm">
                            <p class="text-xs text-slate-400 mt-1">افصل بين اللغات بفاصلة.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">روابط خارجية</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">LinkedIn</label>
                                <input type="url" name="linkedin" value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none text-sm ltr">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">معرض أعمال خارجي</label>
                                <input type="url" name="portfolio_url" value="<?php echo htmlspecialchars($user['portfolio_url'] ?? ''); ?>" placeholder="https://behance.net/..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 outline-none text-sm ltr">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div id="addSkillModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">إضافة مهارة جديدة</h3>
            <div class="space-y-4">
                <input type="text" id="newSkillName" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="اسم المهارة">
                <select id="newSkillCategory" class="w-full px-4 py-2 border rounded-lg bg-white">
                    <?php foreach ($categories_info as $key => $ar_name): ?><option value="<?php echo $key; ?>"><?php echo $ar_name; ?></option><?php endforeach; ?>
                </select>
                <div class="flex gap-2"><button onclick="addNewSkill()" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-bold">إضافة</button><button onclick="document.getElementById('addSkillModal').classList.add('hidden')" class="flex-1 bg-slate-100 text-slate-600 py-2 rounded-lg">إلغاء</button></div>
            </div>
        </div>
    </div>

    <script>
        function setLang(lang) {
            document.querySelectorAll('.lang-btn').forEach(btn => { btn.classList.remove('active', 'bg-indigo-600', 'text-white'); btn.classList.add('text-slate-500'); });
            document.getElementById('btn-' + lang).classList.add('active', 'bg-indigo-600', 'text-white');
            document.getElementById('btn-' + lang).classList.remove('text-slate-500');
            document.getElementById('section-ar').classList.toggle('hidden', lang !== 'ar');
            document.getElementById('section-en').classList.toggle('hidden', lang !== 'en');
        }
        function previewFile(inputId, imgId) {
            const preview = document.getElementById(imgId);
            const file = document.getElementById(inputId).files[0];
            const reader = new FileReader();
            reader.onloadend = function () { preview.src = reader.result; }
            if (file) reader.readAsDataURL(file);
        }
        // Skills Logic
        let selectedSkills = [<?php echo implode(',', $my_skills); ?>].filter(Boolean);
        function initSkills() { selectedSkills.forEach(id => { const el = document.querySelector(`.skill-item[data-id="${id}"]`); if(el) el.classList.add('selected'); }); updateSelectedArea(); }
        function toggleSkill(element, id) {
            if (selectedSkills.includes(id)) { selectedSkills = selectedSkills.filter(item => item !== id); element.classList.remove('selected'); } else { selectedSkills.push(id); element.classList.add('selected'); }
            document.getElementById('skillsInput').value = selectedSkills.join(','); updateSelectedArea();
        }
        function filterSkills(category) {
            const items = document.querySelectorAll('.skill-item');
            const btns = document.querySelectorAll('.category-btn');
            btns.forEach(btn => { btn.classList.toggle('bg-slate-800', btn.dataset.cat === category); btn.classList.toggle('text-white', btn.dataset.cat === category); btn.classList.toggle('bg-slate-100', btn.dataset.cat !== category); });
            items.forEach(item => { item.style.display = (category === 'all' || item.dataset.category === category) ? 'block' : 'none'; });
        }
        function updateSelectedArea() {
            const area = document.getElementById('selectedSkillsArea');
            document.getElementById('countSkills').innerText = selectedSkills.length;
            if (selectedSkills.length === 0) { area.innerHTML = '<span class="text-slate-400 italic text-xs">...</span>'; return; }
            let html = ''; selectedSkills.forEach(id => { const el = document.querySelector(`.skill-item[data-id="${id}"]`); if(el) html += `<span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded text-xs font-bold border border-indigo-100">${el.innerText}</span>`; });
            area.innerHTML = html;
        }
        function addNewSkill() {
            const name = document.getElementById('newSkillName').value;
            const category = document.getElementById('newSkillCategory').value;
            if(!name) return alert('الاسم مطلوب');
            const formData = new FormData(); formData.append('action', 'add_new_skill'); formData.append('skill_name', name); formData.append('skill_category', category);
            fetch('', { method: 'POST', body: formData }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    const container = document.getElementById('skillsContainer');
                    const newEl = document.createElement('div');
                    newEl.className = 'skill-item skill-chip px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 selected bg-white';
                    newEl.innerText = data.name; newEl.dataset.id = data.id; newEl.dataset.category = category;
                    newEl.onclick = function() { toggleSkill(this, data.id) };
                    container.insertBefore(newEl, container.firstChild);
                    selectedSkills.push(data.id); document.getElementById('skillsInput').value = selectedSkills.join(','); updateSelectedArea();
                    document.getElementById('newSkillName').value = ''; document.getElementById('addSkillModal').classList.add('hidden'); filterSkills('all');
                } else { alert(data.msg); }
            });
        }
        document.addEventListener('DOMContentLoaded', initSkills);
    </script>
</body>
</html>