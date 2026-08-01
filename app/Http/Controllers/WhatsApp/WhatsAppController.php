<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\LegalTask;
use App\Models\LegalJudgment;
use App\Services\TwilioService;
use App\Services\WhatsAppRagService;
use App\Services\ChatwootService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * إخلاء المسؤولية الموحد المُضاف في فوتر الرسائل
     */
    const DISCLAIMER = "\n\n───────────────\n⚠️ _إخلاء مسؤولية: جميع الإجابات والمعلومات القانونية المرفقة هي لغايات الاسترشاد والمعرفة العامة فقط ولا تُعد استشارة قانونية رسمية ملزمة._";

    public function __construct(
        protected TwilioService      $twilio,
        protected WhatsAppRagService $rag,
        protected ChatwootService    $chatwoot
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  WEBHOOK — يستقبل كل رسائل Twilio الواردة
    // ─────────────────────────────────────────────────────────────────────────

    public function webhook(Request $request)
    {
        // 1. التحقق من صحة التوقيع إذا كانت البيانات متوفرة (في بيئة الإنتاج)
        if (config('app.env') === 'production') {
            $signature = $request->header('X-Twilio-Signature', '');
            $url       = $request->fullUrl();
            if (!$this->twilio->validateRequest($url, $request->all(), $signature)) {
                Log::warning('[WhatsApp] Webhook request with invalid Twilio signature rejected');
                return response('Unauthorized', 403);
            }
        }

        // 2. استخراج بيانات الرسالة الواردة من Twilio
        $from        = $request->input('From', '');   // مثال: whatsapp:+966500000000
        $to          = $request->input('To', '');     // رقم البوت المـُستلم: whatsapp:+966570079182
        $body        = trim($request->input('Body', ''));
        $profileName = $request->input('ProfileName', '');

        // تنظيف رقم البوت لاستخدامه في روابط wa.me الضغطة الواحدة
        $botPhone    = preg_replace('/\D/', '', $to ?: config('services.twilio.whatsapp_from', ''));

        Log::info('[WhatsApp] رسالة واردة', ['from' => $from, 'body' => $body, 'botPhone' => $botPhone]);

        if (empty($from) || empty($body)) {
            return response('OK', 200);
        }

        // 3. إيجاد أو إنشاء جلسة المحادثة لهذا الرقم
        $conversation = WhatsAppConversation::firstOrCreate(
            ['phone_number' => $from],
            [
                'display_name'  => $profileName,
                'session_state' => 'idle',
                'message_count' => 0,
                'free_limit'    => (int) config('services.twilio.free_limit', 10),
            ]
        );

        // تحديث الاسم إذا تغير
        if ($profileName && $conversation->display_name !== $profileName) {
            $conversation->update(['display_name' => $profileName]);
        }

        // 4. كشف النية (Intent Detection)
        $intent = $this->detectIntent($body, $conversation->session_state);

        // 5. معالجة الـ Intent وإعداد الرد والأزرار التفاعلية (Quick Reply Buttons) والصورة المرفقة
        $reply    = '';
        $buttons  = [];
        $mediaUrl = null;

        switch ($intent) {
            case 'start_chat':
                $reply    = $this->handleStartChat($conversation);
                $buttons  = ['القائمة الرئيسية 🏠', 'إنهاء المحادثة 🛑'];
                $mediaUrl = config('app.url') . '/images/icon.png';
                break;

            case 'end_chat':
                $reply   = $this->handleEndChat($conversation);
                $buttons = ['ابدأ الاستشارة ⚖️'];
                break;

            case 'case_lookup':
                $reply   = $this->handleCaseLookup($conversation, $body, $botPhone);
                $buttons = ['القائمة الرئيسية 🏠', 'إنهاء المحادثة 🛑'];
                break;

            case 'pure_greeting':
                $reply   = $this->handlePureGreeting($conversation, $body);
                $buttons = ['ابدأ الاستشارة ⚖️', 'إنهاء المحادثة 🛑'];
                break;

            case 'legal_query':
                $reply   = $this->handleLegalQuery($conversation, $body, $botPhone);
                $buttons = ['القائمة الرئيسية 🏠', 'إنهاء المحادثة 🛑'];
                break;

            case 'idle_prompt':
            default:
                $reply   = $this->getIdlePrompt();
                $buttons = ['ابدأ الاستشارة ⚖️'];
                break;
        }

        // 6. إرسال الرد المنسق مع الأزرار التفاعلية والصورة المرفقة عبر Twilio
        $this->twilio->sendMessage($from, $reply, $buttons, $mediaUrl);

        // 7. مزامنة الرسالة والرد مع حساب Chatwoot تلقائياً
        try {
            $this->chatwoot->syncIncomingAndOutgoing($from, $profileName, $body, $reply);
        } catch (\Exception $e) {
            Log::warning('[WhatsApp Controller] Chatwoot sync error: ' . $e->getMessage());
        }

        return response('OK', 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INTENT DETECTION
    // ─────────────────────────────────────────────────────────────────────────

    private function detectIntent(string $body, string $sessionState): string
    {
        $normalizedBody = mb_strtolower(trim($body));

        // 1. كشف الضغط على الأزرار التفاعلية أو الكلمات الرئيسية للصنع الصريح
        if (in_array($normalizedBody, [
            'القائمة الرئيسية', 'القائمة الرئيسية 🏠', 'الرئيسية',
            'المساعد القانوني', 'المساعد القانوني ⚖️', 'مساعد قانوني',
            'ابدأ الاستشارة', 'ابدأ الاستشارة ⚖️', 'ابدأ', 'ابدا', 'ابدأ الآن', 'ابدأ الان'
        ], true)) {
            return 'start_chat';
        }

        if (in_array($normalizedBody, [
            'إنهاء المحادثة', 'إنهاء المحادثة 🛑', 'إنهاء الجلسة', 'إنهاء'
        ], true)) {
            return 'end_chat';
        }

        // 2. كشف الاستفسار عن رقم قضية مباشر (مثل: 4471036594 أو قضية 4471036594 أو حكم 4430630992)
        if (preg_match('/^(?:عرض\s+|قضية\s+|مرجع\s+|رقم\s+|حكم\s+|قرار\s+|حكم\s+رقم\s+|قضية\s+رقم\s+)?(\d{3,15})$/u', $normalizedBody)) {
            return 'case_lookup';
        }

        // 3. كشف التحية المجردة والمجاملات (سواء كانت الجلسة active أم idle)
        if ($this->isPureGreeting($body)) {
            return 'pure_greeting';
        }

        // 4. إذا كانت الجلسة نشطة بالفعل (in_chat)
        if ($sessionState === 'in_chat') {
            // كشف طلب الخروج Explicit Exit
            $endExactTriggers = ['0', 'رجوع', 'خروج', 'انهاء', 'إنهاء', 'وداعا', 'وداعاً', 'bye', 'exit', 'quit'];
            foreach ($endExactTriggers as $trigger) {
                if ($normalizedBody === $trigger || mb_strpos($normalizedBody, 'خروج') !== false || mb_strpos($normalizedBody, 'إنهاء الجلسة') !== false) {
                    return 'end_chat';
                }
            }

            // كشف طلب إعادة البدء الصريح Explicit Restart Menu
            if ($normalizedBody === '1' || $normalizedBody === 'مساعد قانوني' || $normalizedBody === 'تصفح المساعدة' || $normalizedBody === 'تصفح المساعدة القانونية') {
                return 'start_chat';
            }

            // أي رسالة أخرى تُمثل سؤالاً قانونياً
            return 'legal_query';
        }

        // 5. إذا كانت الجلسة غير نشطة (idle)
        $startPhrases = [
            'مساعد قانوني', 'مساعد', 'قانوني', 'ابدأ', 'ابدا',
            'تصفح المساعدة', 'استشارة',
        ];

        // المطابقة الدقيقة للرقم 1 فقط كرمز بدء
        if ($normalizedBody === '1') {
            return 'start_chat';
        }

        foreach ($startPhrases as $phrase) {
            if (mb_strpos($normalizedBody, $phrase) !== false) {
                return 'start_chat';
            }
        }

        // مطابقة كلمة الخروج في وضع البداية
        if ($normalizedBody === '0' || $normalizedBody === 'رجوع' || $normalizedBody === 'خروج') {
            return 'end_chat';
        }

        // تفعيل الجلسة تلقائياً والإجابة المباشرة على أي سؤال يُرسل في أول رسالة دون إجبار المستخدم على كتابة كلمة معينة
        return 'legal_query';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HANDLERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * بدء جلسة المحادثة وإرسال رسالة الترحيب مع خيارات التفاعل وإخلاء المسؤولية
     */
    private function handleStartChat(WhatsAppConversation $conversation): string
    {
        $conversation->update([
            'session_state'  => 'in_chat',
            'last_active_at' => now(),
        ]);

        $name = $conversation->display_name ? "، {$conversation->display_name}" : '';
        $remaining = max(0, $conversation->free_limit - $conversation->message_count);

        return "🌟 *مرحباً بك{$name} في المساعد القانوني لمنصة رديف!*

⚖️ يمكنك طرح أي سؤال قانوني يخص الأنظمة والقضايا السعودية، وسأجيبك بناءً على أحدث المراجع والقضايا القضائية.

📌 *أمثلة على الأسئلة:*
• على من يقع عبء إثبات التزوير؟
• ما نص المادة 15 من نظام الشركات؟
• ما شروط تملك العقار للأجانب في السعودية؟

💡 لديك *{$remaining} استشارة مجانية* متبقية.

👇 *يمكنك استخدام الأزرار أدناه أو كتابة سؤالك مباشرة:*
" . self::DISCLAIMER;
    }

    /**
     * إنهاء الجلسة وإرسال رسالة الوداع
     */
    private function handleEndChat(WhatsAppConversation $conversation): string
    {
        $conversation->update(['session_state' => 'idle']);

        return "👋 *تم إنهاء جلسة الاستشارة بنجاح.*

شكراً لاستخدامك المساعد القانوني لمنصة *رديف*.
يمكنك العودة في أي وقت بالنقر على زر *المساعد القانوني ⚖️* أدناه."
        . self::DISCLAIMER;
    }

    /**
     * عرض نص القضية الكاملة عند إرسال رقم القضية أو المرجع مباشرة
     */
    private function handleCaseLookup(WhatsAppConversation $conversation, string $body, string $botPhone = ''): string
    {
        if (!preg_match('/(\d{3,15})/u', $body, $matches)) {
            return "لم نتمكن من التعرف على رقم القضية. يرجى إرسال رقم القضية مجرداً (مثال: 4471036594)." . self::DISCLAIMER;
        }

        $caseNumber = $matches[1];

        // 1. البحث في جدول المهام والقضايا LegalTask
        $task = LegalTask::where('case_reference', $caseNumber)
            ->orWhere('case_reference', 'LIKE', "%{$caseNumber}%")
            ->orWhere('id', (int)$caseNumber)
            ->first();

        if ($task) {
            $ref = $task->case_reference ?? "قضية #{$task->id}";
            $questionText = trim($task->question ?? '');
            $caseText = trim($task->case_text ?? $task->proposed_answer ?? '');
            $correctAnswer = trim($task->correct_answer ?? '');

            $output = "📜 *تفاصيل نص القضية الكاملة (مرجع رقم: {$ref})*\n\n";

            if (!empty($questionText)) {
                $output .= "📌 *موضوع الدعوى / السؤال:* \n" . mb_substr($questionText, 0, 300) . (mb_strlen($questionText) > 300 ? '...' : '') . "\n\n";
            }

            if (!empty($caseText)) {
                $output .= "⚖️ *أسباب الحكم والوقائع:* \n" . mb_substr($caseText, 0, 600) . (mb_strlen($caseText) > 600 ? '...' : '') . "\n\n";
            }

            if (!empty($correctAnswer) && $correctAnswer !== $caseText) {
                $output .= "✅ *المنطوق / النتيجة:* \n" . mb_substr($correctAnswer, 0, 300) . (mb_strlen($correctAnswer) > 300 ? '...' : '') . "\n\n";
            }

            if (!empty($botPhone)) {
                $output .= "🔗 *رابط إعادة الإرسال السريع:* https://wa.me/{$botPhone}?text={$caseNumber}\n\n";
            }

            $output .= "💡 *نصيحة:* يمكنك كتابة أي سؤال قانوني آخر أو إرسال رقم قضية أخرى لعرض تفاصيلها.";
            return $output . self::DISCLAIMER;
        }

        // 2. البحث الثانوي في الأحكام LegalJudgment
        $judgment = LegalJudgment::where('case_number', $caseNumber)
            ->orWhere('case_number', 'LIKE', "%{$caseNumber}%")
            ->orWhere('id', (int)$caseNumber)
            ->first();

        if ($judgment) {
            $output = "📜 *تفاصيل الحكم القضائي (رقم القضية: {$judgment->case_number})*\n\n";
            if (!empty($judgment->title)) {
                $output .= "📌 *العنوان:* {$judgment->title}\n\n";
            }
            if (!empty($judgment->summary)) {
                $output .= "⚖️ *الملخص/الأسباب:* \n" . mb_substr($judgment->summary, 0, 500) . (mb_strlen($judgment->summary) > 500 ? '...' : '') . "\n\n";
            }
            if (!empty($judgment->judgment_text)) {
                $output .= "✅ *نص الحكم:* \n" . mb_substr($judgment->judgment_text, 0, 600) . (mb_strlen($judgment->judgment_text) > 600 ? '...' : '') . "\n\n";
            }

            if (!empty($botPhone)) {
                $output .= "🔗 *رابط إعادة الإرسال السريع:* https://wa.me/{$botPhone}?text={$caseNumber}\n\n";
            }

            return $output . self::DISCLAIMER;
        }

        // 3. المحاولة الثالثة: البحث الذكي عبر RAG وقواعد البيانات المتجهة Vector Search
        try {
            $ragResult = $this->rag->ask("عرض تفاصيل ووقائع الحكم أو القضية رقم {$caseNumber}");
            if (!empty($ragResult['answer']) && mb_strlen(trim($ragResult['answer'])) > 20) {
                return $this->formatForWhatsApp($ragResult['answer'], $ragResult['citations'] ?? [], $botPhone) . self::DISCLAIMER;
            }
        } catch (\Exception $e) {
            Log::warning('[WhatsApp] Fallback RAG lookup failed for case ' . $caseNumber . ': ' . $e->getMessage());
        }

        $linkInfo = !empty($botPhone) ? "\n👉 *إعادة إرسال رقم الحكم بضغطة واحدة:* https://wa.me/{$botPhone}?text={$caseNumber}" : "";
        return "🔍 لم نجد تفاصيل مخزنة برقم القضية أو المرجع (*{$caseNumber}*) في قاعدة البيانات حالياً. يمكنك طرح سؤالك القانوني وسنبحث لك في كافة السوابق والأنظمة." . $linkInfo . self::DISCLAIMER;
    }

    /**
     * معالجة السؤال القانوني عبر محرك RAG
     */
    private function handleLegalQuery(WhatsAppConversation $conversation, string $question, string $botPhone = ''): string
    {
        // تفعيل الجلسة تلقائياً إذا كانت غير نشطة
        if ($conversation->session_state !== 'in_chat') {
            $conversation->update([
                'session_state'  => 'in_chat',
                'last_active_at' => now(),
            ]);
        }

        // التحقق من حد الرسائل
        if ($conversation->hasReachedLimit()) {
            return $this->getLimitReachedMessage($conversation);
        }

        // جلب تاريخ المحادثة الأخير
        $history = $conversation->getRecentHistory(6)->map(fn($m) => [
            'role'    => $m->role,
            'content' => $m->content,
        ])->toArray();

        // حفظ سؤال المستخدم
        WhatsAppMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'role'                     => 'user',
            'content'                  => $question,
        ]);

        // استدعاء محرك الذكاء الاصطناعي
        try {
            $result = $this->rag->ask($question, $history);
            $answer = $result['answer'];
            $citations = $result['citations'];
            $isGreeting = $result['is_greeting'] ?? false;
        } catch (\Exception $e) {
            Log::error('[WhatsApp] RAG error: ' . $e->getMessage());
            $answer    = 'عذراً، حدث خطأ فني. يرجى المحاولة لاحقاً.';
            $citations = [];
            $isGreeting = false;
        }

        // تنسيق الإجابة لواتساب مع تنقية وتعرِيب المصادر
        $formattedAnswer = $this->formatForWhatsApp($answer, $citations, $botPhone);

        // حفظ رد المساعد
        WhatsAppMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'role'                     => 'assistant',
            'content'                  => $answer,
        ]);

        // تحديث عداد الرسائل
        $conversation->incrementAndTouch();

        // إذا تم تصنيف الرسالة كتحية، يُكتفى بالرد دون إخلاء مسؤولية أو تنبيه بالحد
        if ($isGreeting) {
            return $formattedAnswer;
        }

        // إضافة تذكير بالرصيد المتبقي إذا اقترب من الحد
        $remaining = max(0, $conversation->free_limit - $conversation->message_count - 1);
        if ($remaining <= 2 && $remaining > 0) {
            $formattedAnswer .= "\n\n⚠️ _تبقى لك {$remaining} استشارة مجانية فقط._";
        }

        // إضافة إخلاء المسؤولية الموحد في نهاية الرسالة
        return $formattedAnswer . self::DISCLAIMER;
    }

    /**
     * معالجة التحية المجردة وإرجاع رد تحية بسيط ومباشر بدون مصادر أو إخلاء مسؤولية
     */
    private function handlePureGreeting(WhatsAppConversation $conversation, string $body): string
    {
        $conversation->update([
            'session_state'  => 'in_chat',
            'last_active_at' => now(),
        ]);

        return $this->getGreetingReply($body);
    }

    /**
     * التحقق مما إذا كانت الرسالة تحية مجردة أو مجاملة بدون سؤال قانوني
     */
    private function isPureGreeting(string $text): bool
    {
        $clean = mb_strtolower(trim($text));

        // تنظيف العلامات والرموز والتواكيل والتطويل
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);

        if (empty($clean)) {
            return false;
        }

        // قائمة الكلمات الدالة على التحية والمجاملة
        $greetingWords = [
            'السلام', 'عليكم', 'ورحمة', 'الله', 'وبركاته', 'سلام', 'سلامات',
            'مرحبا', 'مرحباً', 'مرحبتين', 'مراحيب',
            'اهلا', 'أهلا', 'اهلاً', 'أهلاً', 'اهلين', 'أهلين', 'اهدا', 'وسهلاً', 'وسهلا',
            'صباح', 'مساء', 'الخير', 'النور', 'الورد', 'السرور',
            'كيف', 'حالك', 'الحال', 'الحالية', 'كيفك', 'اخبارك', 'شخبارك', 'علومك', 'شلونك',
            'يعطيك', 'العافية', 'يعطيكوا', 'عافية', 'الله', 'يعافيك',
            'شكرا', 'شكراً', 'مشكور', 'مشكورة', 'تسلم', 'تسلمي', 'جزاك', 'خير', 'خيور', 'جزيل', 'الشكر',
            'يا', 'هلا', 'غالي', 'حياك', 'حياكم',
            'hi', 'hello', 'hey', 'good', 'morning', 'evening', 'afternoon', 'how', 'are', 'you', 'thanks', 'thank'
        ];

        $words = explode(' ', $clean);

        // إزالة كل كلمات التحية
        $nonGreetingWords = array_values(array_filter($words, function ($w) use ($greetingWords) {
            return !in_array($w, $greetingWords, true);
        }));

        // إذا لم تتبقَ أي كلمات بعد إزالة كلمات التحية -> فهي تحية مجردة مؤكدة
        if (empty($nonGreetingWords)) {
            return true;
        }

        // إذا كانت الكلمات المتبقية أقل من أو تساوي كلمتين ولا تحتوي على كلمات قانونية أو دالة على سؤال
        if (count($nonGreetingWords) <= 2) {
            $legalKeywords = [
                'مادة', 'نظام', 'قضية', 'حكم', 'عقد', 'عمل', 'استئناف', 'محكمة', 'عقوبة',
                'تعويض', 'حق', 'تركة', 'طلاق', 'تزوير', 'شركة', 'شركات', 'عقار', 'إثبات',
                'اثبات', 'سؤال', 'استفسار', 'طلب', 'مساعدة', 'نص', 'شرط', 'شروط', 'لوائح',
                'لائحة', 'تنفيذية', 'مبلغ', 'حقوق', 'راتب', 'فصل', 'إنهاء', 'انهاء'
            ];

            $questionMarkers = [
                'ما', 'ماذا', 'هل', 'كيف', 'متى', 'اين', 'أين', 'كم', 'ليه', 'لماذا', 'مين', 'من'
            ];

            foreach ($nonGreetingWords as $w) {
                if (in_array($w, $legalKeywords, true) || in_array($w, $questionMarkers, true)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * صياغة رد التحية بشكل مقتضب وأنيق دون مصادر أو إخلاء مسؤولية
     */
    private function getGreetingReply(string $body): string
    {
        $clean = mb_strtolower(trim($body));

        if (mb_strpos($clean, 'صباح') !== false) {
            return "صباح النور والسرور! كيف يمكنني مساعدتك في الأنظمة والقضايا السعودية اليوم؟";
        }

        if (mb_strpos($clean, 'مساء') !== false) {
            return "مساء النور والسرور! كيف يمكنني مساعدتك في الأنظمة والقضايا السعودية اليوم؟";
        }

        if (mb_strpos($clean, 'شكرا') !== false || mb_strpos($clean, 'شكراً') !== false || mb_strpos($clean, 'مشكور') !== false || mb_strpos($clean, 'يعطيك') !== false || mb_strpos($clean, 'جزاك') !== false) {
            return "العفو! أهلاً وسهلاً بك في أي وقت. ⚖️ هل لديك أي استفسار قانوني آخر؟";
        }

        if (mb_strpos($clean, 'سلام') !== false) {
            return "وعليكم السلام ورحمة الله وبركاته. كيف يمكنني مساعدتك في الأنظمة والقضايا السعودية اليوم؟";
        }

        return "أهلاً وسهلاً بك! كيف يمكنني مساعدتك في الأنظمة والقضايا السعودية اليوم؟";
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FORMAT & MESSAGES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * تحويل الإجابة من HTML/Markdown إلى تنسيق واتساب (plain text + WhatsApp bold)
     * مع تعريب وتجميل قسم المصادر وإتاحة التفاعل المباشر عبر رابط wa.me
     */
    private function formatForWhatsApp(string $answer, array $citations, string $botPhone = ''): string
    {
        // إزالة HTML tags
        $text = strip_tags($answer);

        // تحويل Markdown headers إلى bold في واتساب
        $text = preg_replace('/^#{1,3}\s+(.+)$/mu', '*$1*', $text);

        // تحويل bold Markdown إلى WhatsApp bold
        $text = preg_replace('/\*\*(.+?)\*\*/u', '*$1*', $text);

        // جعل أرقام القضايا داخل النص تفاعلية عبر رابط wa.me
        $text = preg_replace_callback('/(القضية\s+رقم\s+|مرجع\s+قانوني\s+\[?|مرجع\s+#|حكم\s+رقم\s+)(\d{3,15})\]?/u', function ($matches) use ($botPhone) {
            $prefix = $matches[1];
            $num    = $matches[2];
            if (!empty($botPhone)) {
                return "{$prefix}{$num} 🔗 *(اضغط للإرسال التلقائي: https://wa.me/{$botPhone}?text={$num} )*";
            }
            return "{$prefix}{$num} 🔗 *(لِعرض نص القضية كاملاً أرسل: {$num})*";
        }, $text);

        // تنظيف المسافات الزائدة
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        // صياغة قسم المصادر بشكل عربي أنيق ومنظم بدون مصطلحات إنجليزية مع روابط تفاعلية
        if (!empty($citations)) {
            $sourcesText = "\n\n📚 *المصادر والمرجعيات القضائية:*";
            $seen        = [];
            $count       = 0;

            foreach ($citations as $cite) {
                $rawTitle = trim($cite['title'] ?? '');
                if (empty($rawTitle) || isset($seen[$rawTitle]) || $count >= 4) continue;
                $seen[$rawTitle] = true;
                $count++;

                // تعريب اسم النظام أو المرجع
                $systemName = $this->translateSystemName($cite['system'] ?? '');

                // تنسيق عنوان المرجع
                $formattedTitle = $rawTitle;
                if (is_numeric($rawTitle)) {
                    $formattedTitle = "حكم قضائي رقم {$rawTitle}";
                }

                // استخراج الأرقام لربطها برابط تفاعلي مباشر
                if (preg_match('/(\d{3,15})/', $rawTitle, $numMatch)) {
                    $caseNum = $numMatch[1];
                    if (!empty($botPhone)) {
                        $waLink = "https://wa.me/{$botPhone}?text={$caseNum}";
                        $sourcesText .= "\n• 📜 *{$formattedTitle}* - {$systemName}\n  👉 *اضغط هنا لإرسال رقم الحكم تلقائياً:* {$waLink}";
                    } else {
                        $sourcesText .= "\n• 📜 *{$formattedTitle}* - {$systemName}\n  👈 _أرسل *{$caseNum}* لعرض نص القضية كاملاً_";
                    }
                } else {
                    $sourcesText .= "\n• 📜 *{$formattedTitle}* - {$systemName}";
                }
            }

            $text .= $sourcesText;
        }

        return $text;
    }

    /**
     * ترجمة وتنقية أسماء الأنظمة والمراجع إلى أسماء عربية رسمية
     */
    private function translateSystemName(string $system): string
    {
        $system = trim($system);
        if (empty($system)) {
            return 'السوابق والأحكام القضائية';
        }

        $translations = [
            'Commercial Law'         => 'نظام المحاكم التجارية',
            'Commercial'             => 'نظام المحاكم التجارية',
            'Commercial Courts Law'  => 'نظام المحاكم التجارية',
            'Labor Law'              => 'نظام العمل السعودي',
            'Labor'                  => 'نظام العمل السعودي',
            'Civil Law'              => 'نظام المعاملات المدنية',
            'Civil System'           => 'نظام المعاملات المدنية',
            'Evidence Law'           => 'نظام الإثبات',
            'Evidence'               => 'نظام الإثبات',
            'Personal Status'        => 'نظام الأحوال الشخصية',
            'Criminal Law'           => 'نظام الإجراءات الجزائية',
            'Executive Regulations'  => 'اللائحة التنفيذية',
        ];

        return $translations[$system] ?? $system;
    }

    private function getLimitReachedMessage(WhatsAppConversation $conversation): string
    {
        $appUrl = config('app.url', 'https://radiif.com');
        return "🔒 *لقد استنفدت رصيدك المجاني ({$conversation->free_limit} استشارات).*

للاستمرار في الاستفادة من المساعد القانوني، يرجى التسجيل على منصة رديف:
🔗 {$appUrl}/register/company

✨ سيحصل كل عضو على *20 استشارة مجانية* إضافية عند التسجيل!"
        . self::DISCLAIMER;
    }

    private function getIdlePrompt(): string
    {
        return "👋 *مرحباً بك في المساعد القانوني لمنصة رديف.*

اضغط على الزر أدناه لبدء جلسة استشارة جديدة:"
        . self::DISCLAIMER;
    }

    private function getDefaultReply(): string
    {
        return "لم أفهم طلبك. استخدم الأزرار أدناه لبدء استشارة قانونية جديدة أو لإنهائها."
        . self::DISCLAIMER;
    }
}
