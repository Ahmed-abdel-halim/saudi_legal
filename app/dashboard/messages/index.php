<?php // dashboard/messages/index.php (صفحة الرسائل - مترجمة) ?>
<?php 
// تضمين الهيكل (الهيدر والشريط الجانبي)
include '../../layout/dashboard_header.php'; 

// 1. جلب جميع المحادثات (المشاريع) التي يشارك فيها المستخدم
$conversations_list = [];
$stmt_convos = $conn->prepare(
    "SELECT c.conversation_id, p.project_id, p.title, 
            c_req.name AS requester_name, c_sup.name AS supplier_name, u_exp.full_name AS expert_name, u_exp.profile_image_url AS expert_image,
            (SELECT m.sent_at FROM messages m WHERE m.conversation_id = c.conversation_id ORDER BY m.sent_at DESC LIMIT 1) AS last_message_time
     FROM conversations c
     JOIN projects p ON c.project_id = p.project_id
     JOIN conversation_participants cp ON c.conversation_id = cp.conversation_id
     LEFT JOIN companies c_req ON p.requester_company_id = c_req.company_id
     LEFT JOIN companies c_sup ON p.supplier_company_id = c_sup.company_id
     LEFT JOIN users u_exp ON p.expert_user_id = u_exp.user_id
     WHERE cp.user_id = ? AND (p.status = 'active' OR p.status = 'pending_approval' OR p.status = 'disputed' OR p.status = 'completed')
     GROUP BY c.conversation_id
     ORDER BY last_message_time DESC, p.created_at DESC"
);
$stmt_convos->bind_param("i", $user_id);
$stmt_convos->execute();
$convos_result = $stmt_convos->get_result();
while($row = $convos_result->fetch_assoc()) {
    $conversations_list[] = $row;
}
$stmt_convos->close();

// 2. تحديد المحادثة المختارة
$selected_convo_id = $_GET['convo_id'] ?? $conversations_list[0]['conversation_id'] ?? null;
$selected_project_title = $L['MESSAGES_SELECT_CONVO'];
$selected_convo_image = "https://placehold.co/40x40/E2E8F0/334155?text=P";
$messages = [];

// 3. جلب رسائل المحادثة المختارة
if ($selected_convo_id) {
    $is_participant = false;
    foreach ($conversations_list as $convo) {
        if ($convo['conversation_id'] == $selected_convo_id) {
            $is_participant = true;
            $selected_project_title = $convo['title'];
            // (في تصميم واتس آب، نحتاج لصورة الطرف الآخر)
            $selected_convo_image = $convo['expert_image'] ?: 'https://placehold.co/40x40/E2E8F0/334155?text=' . strtoupper(substr($convo['expert_name'], 0, 1)); 
            break;
        }
    }

    if ($is_participant) {
        $stmt_msgs = $conn->prepare(
            "SELECT m.sender_id, m.message_body, m.sent_at, u.full_name, u.profile_image_url
             FROM messages m
             JOIN users u ON m.sender_id = u.user_id
             WHERE m.conversation_id = ?
             ORDER BY m.sent_at ASC"
        );
        $stmt_msgs->bind_param("i", $selected_convo_id);
        $stmt_msgs->execute();
        $msgs_result = $stmt_msgs->get_result();
        while($row = $msgs_result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt_msgs->close();
    } else {
        $selected_convo_id = null; // غير مصرح له
    }
}
?>

<!-- !!! --- تحديث: تغيير الألوان لتصميم واتس آب --- !!! -->
<style>
    /* خلفية واتس آب (SVG خفيف) */
    .whatsapp-bg {
        background-color: #F1F5F9; /* slate-light */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400' viewBox='0 0 800 800'%3E%3Cg fill='none' stroke='%23D1D5DB' stroke-width='1'%3E%3Cpath d='M769 229L1037 260.9M927 880L731 737 520 660 309 538 40 482.1 289 369 565 259 842 181 731 10 34 103 162 250 438 408 614 515 731 660 813 752 769 880 731 849 520 752 309 660 40 617.1 289 482 565 408 842 320 731 229 34 260.9 162 408 438 538 614 660 731 752 813 811 769 880'/%3E%3Cpath d='M-121 408L-81 369 162 250 438 181 731 229 842 260.9 614 369 34 408 162 482.1 438 538 731 660 842 617.1 614 515 34 482.1 162 408 438 320 731 260.9 842 181 614 10 34 103 162 250 438 408 614 482.1 842 538 731 660 520 752 309 849 40 811 289 737 565 660 842 617.1 731 515 520 408 309 369 40 320 289 229 565 181 842 10 731 103'/%3E%3Cpath d='M-121 811L-81 752 162 660 438 617.1 731 660 842 737 614 849 34 811 162 752 438 737 731 752 842 811 614 880 34 880 162 849 438 811 731 752 842 660 614 617.1 34 538 162 482.1 438 408 731 408 842 408 614 369 34 369 162 320 438 260.9 731 229 842 260.9 614 250 34 181 162 103 438 10 731 10 842 10 614 103 34 181 162 250 438 260.9 731 320 842 369 614 408 34 408 162 482.1 438 538 731 538 842 538 614 617.1 34 660 162 752 438 811 731 849 842 880 614 880 34 880 162 811 438 752 731 660 842 617.1'/%3E%3C/g%3E%3C/svg%3E");
    }
    /* لون فقاعة المرسل (أنا) */
    .chat-bubble-sender {
        background-color: #DCF8C6; /* أخضر واتس آب الفاتح */
        color: #303030;
    }
    /* لون فقاعة المستقبل (الآخر) */
    .chat-bubble-receiver {
        background-color: #FFFFFF; /* أبيض */
        color: #303030;
    }
</style>

<!-- بداية هيكل صفحة الرسائل (WhatsApp-Style) -->
<!-- تم تغيير الخلفية العامة لـ h-full -->
<div class="flex h-[calc(100vh-140px)] bg-white rounded-lg overflow-hidden shadow-lg border border-gray-200">

    <!-- 1. الشريط الجانبي (قائمة المحادثات) -->
    <aside class="w-1/3 border-<?php echo ($dir=='rtl')?'l':'r'; ?> border-gray-200 h-full flex flex-col bg-white">
        <!-- رأس الشريط الجانبي -->
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-slate-50">
            <h2 class="text-xl font-bold text-dark-navy"><?php echo $L['MESSAGES_TITLE']; ?></h2>
            <!-- (يمكن إضافة أيقونات هنا) -->
        </div>
        
        <!-- (يمكن إضافة شريط بحث هنا) -->
        
        <div class="flex-1 overflow-y-auto">
            <nav class="p-2 space-y-1">
                <?php if (count($conversations_list) > 0): ?>
                    <?php foreach ($conversations_list as $convo): ?>
                        <a href="index.php?convo_id=<?php echo $convo['conversation_id']; ?>" 
                           class="block p-4 rounded-lg transition-colors 
                                  <?php echo ($convo['conversation_id'] == $selected_convo_id) ? 'bg-slate-100' : 'hover:bg-slate-50'; ?>">
                            <div class="flex items-center space-x-3 <?php echo ($dir=='rtl')?'space-x-reverse':''; ?>">
                                <img src="<?php echo !empty($convo['expert_image']) ? htmlspecialchars($convo['expert_image']) : 'https://placehold.co/40x40/E2E8F0/334155?text=U'; ?>" alt="avatar" class="w-12 h-12 rounded-full flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <h3 class="font-semibold text-dark-navy truncate"><?php echo htmlspecialchars($convo['title']); ?></h3>
                                        <?php if($convo['last_message_time']): ?>
                                            <span class="text-xs text-gray-500 flex-shrink-0"><?php echo date('H:i', strtotime($convo['last_message_time'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">
                                        <?php if ($role == 'employee'): ?>
                                            <?php echo $L['MESSAGES_CLIENT_LABEL']; ?> <?php echo htmlspecialchars($convo['requester_name']); ?>
                                        <?php else: // المالك (طالب أو مورد) يرى الخبير ?>
                                            <?php echo $L['MESSAGES_EXPERT_LABEL']; ?> <?php echo htmlspecialchars($convo['expert_name']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center p-6"><?php echo $L['MESSAGES_NO_ACTIVE_CONVOS']; ?></p>
                <?php endif; ?>
            </nav>
        </div>
    </aside>

    <!-- 2. منطقة الدردشة الرئيسية -->
    <main class="flex-1 flex flex-col h-full bg-slate-50">
        <?php if ($selected_convo_id): ?>
            <!-- رأس الدردشة -->
            <header class="bg-slate-50 p-4 border-b border-gray-200 flex-shrink-0 flex items-center space-x-3 <?php echo ($dir=='rtl')?'space-x-reverse':''; ?>">
                <img src="<?php echo htmlspecialchars($selected_convo_image); ?>" alt="avatar" class="w-10 h-10 rounded-full">
                <div>
                    <h1 class="text-lg font-bold text-dark-navy"><?php echo htmlspecialchars($selected_project_title); ?></h1>
                    <!-- (يمكن إضافة حالة "متصل الآن") -->
                </div>
            </header>

            <!-- جسم الدردشة (الرسائل) -->
            <div id="message-container" class="flex-1 p-6 space-y-4 overflow-y-auto whatsapp-bg">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php $is_sender = $msg['sender_id'] == $user_id; ?>
                        
                        <!-- رسالة -->
                        <!-- 
                            المنطق:
                            - المرسل (أنا): justify-start (حسب الاتجاه) -> في LTR يسار، في RTL يمين؟ 
                            عادة المرسل (أنا) في الواتساب يكون على "جهة البداية" للغة؟ لا، المرسل (أنا) عالمياً يمين، والمستقبل يسار (أو العكس حسب التطبيق).
                            لنبسط: 
                            - أنا (Me): always aligned to "end" (right in LTR, left in RTL typically for sender? No, sender usually Right in WA LTR).
                            دعنا نستخدم التناسق:
                            - Sender (Me): justify-end (في LTR يمين)
                            - Receiver (Other): justify-start (في LTR يسار)
                            
                            في RTL:
                            - Sender (Me): justify-end (يسار)
                            - Receiver (Other): justify-start (يمين)
                        -->
                        <?php 
                            // تحديد الاتجاه بناءً على المرسل
                            // في أغلب تطبيقات الشات: أنا (المرسل) دائماً في "النهاية" (End) والطرف الآخر في "البداية" (Start)
                            $justify_class = $is_sender ? 'justify-end' : 'justify-start';
                        ?>
                        <div class="flex <?php echo $justify_class; ?>">
                            <div class="flex items-end gap-2 max-w-lg">
                                
                                <div class="<?php echo $is_sender ? 'chat-bubble-sender' : 'chat-bubble-receiver'; ?> p-3 rounded-lg shadow-sm text-sm md:text-base relative">
                                    
                                    <?php if (!$is_sender): // اسم المرسل (الآخر) ?>
                                        <span class="block font-bold text-xs text-brand-magenta mb-1"><?php echo htmlspecialchars($msg['full_name']); ?></span>
                                    <?php endif; ?>
                                    
                                    <p class="leading-relaxed" style="white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message_body']); ?></p>
                                    
                                    <span class="text-[10px] <?php echo $is_sender ? 'text-green-800' : 'text-gray-500'; ?> opacity-75 block text-<?php echo ($dir=='rtl')?'left':'right'; ?> mt-1">
                                        <?php echo date('H:i', strtotime($msg['sent_at'])); ?>
                                    </span>
                                </div>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="flex h-full items-center justify-center">
                        <p class="text-gray-500 bg-white/50 px-4 py-2 rounded-full text-sm shadow-sm">ابدأ المحادثة.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- نموذج إرسال الرسالة -->
            <footer class="bg-slate-50 p-4 border-t border-gray-200 flex-shrink-0">
                <form action="../../config/send_message_handler.php" method="POST">
                    <input type="hidden" name="conversation_id" value="<?php echo $selected_convo_id; ?>">
                    <div class="flex items-center gap-4">
                        <textarea name="message_body" rows="1" 
                                  class="flex-1 bg-white border border-gray-300 rounded-full py-3 px-5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-teal resize-none" 
                                  placeholder="<?php echo $L['MESSAGES_WRITE_PLACEHOLDER']; ?>" required></textarea>
                        <button type="submit" 
                                class="bg-brand-teal text-white p-3 rounded-full flex-shrink-0 hover:bg-opacity-90 transition-all transform hover:scale-105 shadow-md">
                            <!-- أيقونة الإرسال: تتغير حسب الاتجاه -->
                            <?php if ($dir == 'rtl'): ?>
                                <i class="fas fa-paper-plane text-lg pl-1"></i> <!-- أيقونة لليمين/اليسار حسب الفونت -->
                            <?php else: ?>
                                <i class="fas fa-paper-plane text-lg pr-1"></i>
                            <?php endif; ?>
                        </button>
                    </div>
                </form>
            </footer>

        <?php else: ?>
            <!-- حالة عدم اختيار محادثة -->
            <div class="flex-1 flex flex-col items-center justify-center h-full whatsapp-bg">
                <div class="bg-white p-6 rounded-full shadow-lg mb-4">
                    <i class="fas fa-comments text-6xl text-brand-teal"></i>
                </div>
                <p class="text-gray-600 text-lg font-medium"><?php echo $L['MESSAGES_SELECT_TO_START']; ?></p>
            </div>
        <?php endif; ?>
    </main>

</div> <!-- /نهاية هيكل صفحة الرسائل -->

<!-- سكريبت لجعل الدردشة تنزل للأسفل تلقائيًا -->
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const container = document.getElementById('message-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>

<?php 
// تضمين الفوتر الخاص بلوحة التحكم
include '../../layout/dashboard_footer.php'; 
?>