<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LegalSearchService;
use App\Services\LegalReferenceService;
use Illuminate\Support\Facades\Http;

class LegalAiController extends Controller
{
    protected $searchService;
    protected $referenceService;

    public function __construct(LegalSearchService $searchService, LegalReferenceService $referenceService)
    {
        $this->searchService = $searchService;
        $this->referenceService = $referenceService;
    }

    public function index()
    {
        return view('saudi_legal.chat');
    }

    public function ask(Request $request)
    {
        $request->validate(['question' => 'required|string|max:1000']);
        $question = $request->question;

        // 1. Hybrid Search (Keywords + Legal Logic)
        $contextTasks = $this->searchService->search($question, 5);

        $allArticles = collect();
        
        // 2. Direct Article Search if no judgments found
        if ($contextTasks->isEmpty()) {
            $allArticles = $this->referenceService->getMentionedArticles($question);
            // If still empty, search articles by keywords
            if ($allArticles->isEmpty()) {
                $allArticles = \App\Models\LegalArticle::where(function($q) use ($question) {
                    $q->where('content', 'LIKE', "%{$question}%")
                      ->orWhere('article_title', 'LIKE', "%{$question}%");
                })->limit(5)->get();
            }
        }

        if ($contextTasks->isEmpty() && $allArticles->isEmpty()) {
            return response()->json([
                'answer' => "عذراً، لم أجد أحكاماً قضائية أو نصوصاً نظامية مطابقة في قاعدة بياناتي الحالية. هل يمكنك تقديم تفاصيل أكثر أو ذكر رقم المادة التي تبحث عنها؟",
                'citations' => []
            ]);
        }

        $contextText = "";
        $citations = [];

        // 2. Build Rich Context from Judgments/Questions/Articles
        foreach ($contextTasks as $task) {
            $typeLabel = "مرجع قانوني";
            $badgeLabel = "أحكام قضائية"; // Default to الأحكام
            
            if (isset($task->source_type)) {
                if ($task->source_type == 'judgment') { $typeLabel = "حكم قضائي"; $badgeLabel = "أحكام قضائية"; }
                elseif ($task->source_type == 'consultation') { $typeLabel = "استشارة سابقة"; $badgeLabel = "استشارة سابقة"; }
                elseif ($task->source_type == 'article') { $typeLabel = "مادة نظامية"; $badgeLabel = "نص نظام"; }
            }
            
            $ref = $task->case_reference ?? (isset($task->id) ? "مرجع #{$task->id}" : "مرجع عام");
            if (trim($ref) == "مادة رقم" || trim($ref) == "null" || empty(trim($ref))) {
                $ref = $task->question ?? "مرجع عام"; // Fallback
            }
            
            $textToShow = $task->case_text ?: $task->correct_answer;
            
            // Skip completely empty items or items with only 'null'
            if (!$textToShow || trim($textToShow) == 'null' || trim($textToShow) == '') continue;

            $contextText .= "--- {$typeLabel} [{$ref}] ---\n";
            $contextText .= "السؤال/الموضوع: {$task->question}\n";
            $contextText .= "النص/الأسباب: {$textToShow}\n";
            if ($task->correct_answer && $task->correct_answer != $textToShow) {
                $contextText .= "الإجابة/المنطوق: {$task->correct_answer}\n";
            }
            $contextText .= "\n";

            // Extract articles using the Wild Intelligent engine (only for tasks, not articles themselves)
            if (!isset($task->source_type) || $task->source_type != 'article') {
                $articles = $this->referenceService->getMentionedArticles($textToShow . " " . $task->correct_answer);
                foreach ($articles as $art) { $allArticles->push($art); }
            }

            $citations[] = [
                'type' => $task->source_type ?? 'judgment',
                'title' => $ref,
                'article' => $badgeLabel,
                'text' => $textToShow
            ];
        }

        // 3. Add Extracted Law Articles to Context
        if ($allArticles->isNotEmpty()) {
            $contextText .= "--- نصوص الأنظمة السعودية ذات الصلة ---\n";
            foreach ($allArticles->unique('id') as $article) {
                $contextText .= "[{$article->legislation_title} - {$article->article_title}]:\n{$article->content}\n\n";
                
                $citations[] = [
                    'type' => 'law_article',
                    'title' => "{$article->article_title} - {$article->legislation_title}",
                    'article' => "نص نظام",
                    'text' => $article->content
                ];
            }
        }

        // 4. Generate Eloquent AI Answer
        $answer = $this->generateAiAnswer($question, $contextText);

        return response()->json([
            'answer' => $answer,
            'citations' => $citations
        ]);
    }

    private function generateAiAnswer($question, $context)
    {
        $apiKey = trim(config('services.gemini.key'));
        
        if (empty($apiKey)) {
            return "مرحباً! لقد قمت باستخراج السوابق القانونية لك. (يرجى تفعيل GEMINI_API_KEY في ملف .env للحصول على صياغة ذكية).";
        }

        $isShortQuestion = mb_strlen($question) < 200;
        
        $prompt = "أنت 'رديف القانوني'، مستشار قانوني سعودي فصيح وخبير. 
مهمتك الأساسية: الإجابة على سؤال العميل بدقة بناءً على 'المعلومات القانونية والمراجع' المرفقة.

القواعد الذهبية للاستشارة:
1. (أولوية النظام): اعتمد دائماً على 'نصوص الأنظمة' (المواد القانونية) كمرجعك الأول والأهم. الأحكام القضائية والاستشارات السابقة تُستخدم كأمثلة داعمة فقط إذا طابقت موضوع السؤال.
2. (تجاهل السياق الخاطئ): إذا كان سؤال العميل في مجال معين (مثلاً: أحوال شخصية، نفقة، طلاق)، ووجدت في المراجع المرفقة حكماً قضائياً في مجال آخر (مثلاً: عمالي، تجاري)، **تجاهل الحكم القضائي تماماً** ولا تذكره أو تلخصه في إجابتك أبداً.
3. (الإجابة المباشرة): إذا طرح العميل سؤالاً محدداً، أجب عليه مباشرة ولا تقم بـ 'تحليل' أو 'تلخيص' أي نصوص قضائية مرفقة إلا إذا طلب العميل ذلك صراحةً.
4. دائماً ابدأ إجابتك بـ 'أهلاً بك، بصفتي رديف القانوني...'.
5. إذا كانت المراجع لا تحتوي على الإجابة، أخبر العميل بوضوح: 'عذراً، لم أجد نصوصاً نظامية محددة في المراجع الحالية للإجابة على سؤالك.'.

المعلومات القانونية المتاحة (المراجع):
" . $context . "

سؤال العميل الحالي:
" . $question;

        try {
            // Trying Gemini 2.5 Flash (Latest & Greatest available for this key)
            $response = Http::withoutVerifying()
                ->timeout(80)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, [
                    'contents' => [['parts' => [['text' => mb_substr($prompt, 0, 30000)]]]],
                ]);

            if ($response->successful()) {
                return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? "عذراً، لم أتمكن من صياغة الإجابة.";
            }

            // Fallback to Gemini 2.5 Pro if Flash fails
            if ($response->status() == 404 || $response->status() == 403) {
                $response = Http::withoutVerifying()
                    ->timeout(80)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=" . $apiKey, [
                        'contents' => [['parts' => [['text' => mb_substr($prompt, 0, 30000)]]]],
                    ]);
                
                if ($response->successful()) {
                    return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? "عذراً، لم أتمكن من صياغة الإجابة.";
                }
            }

            $errorBody = $response->body();
            \Illuminate\Support\Facades\Log::error("Gemini API Error: " . $response->status() . " - " . $errorBody);
            
            return "عذراً، حدث خطأ فني (الرمز: " . $response->status() . "). يرجى التأكد من إعدادات الحساب في Google AI Studio.";
        } catch (\Exception $e) {
            return "خطأ في الاتصال بالمحرك: " . $e->getMessage();
        }
    }
}
