<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LegalSearchService;
use Illuminate\Support\Facades\Http;

class LegalAiController extends Controller
{
    protected $searchService;

    public function __construct(LegalSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index()
    {
        return view('saudi_legal.chat');
    }

    public function ask(Request $request)
    {
        $request->validate(['question' => 'required|string|max:500']);
        $question = $request->question;

        // Search for relevant tasks/judgments
        $contextTasks = $this->searchService->search($question, 3);

        if ($contextTasks->isEmpty()) {
            return response()->json([
                'answer' => "عذراً، لم أجد أحكاماً قضائية أو سوابق مباشرة تتعلق بسؤالك في قاعدة البيانات الحالية. هل يمكنك صياغة السؤال بشكل مختلف؟",
                'citations' => []
            ]);
        }

        $contextText = "";
        $citations = [];
        foreach ($contextTasks as $task) {
            $ref = $task->case_reference ?? "حكم غير محدد";
            $contextText .= "المرجع: {$ref}\nالسؤال المرتبط: {$task->question}\nالنص القضائي/المبدأ: {$task->case_text}\n\n";

            $citations[] = [
                'title' => $ref,
                'article' => $task->question, // Using question as the "article" title for context
                'text' => $task->case_text // Remove truncation so the user sees the full judgment
            ];
        }

        $answer = $this->generateAiAnswer($question, $contextText);

        return response()->json([
            'answer' => $answer,
            'citations' => $citations
        ]);
    }

    private function generateAiAnswer($question, $context)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return "مرحباً! لقد قمت باستخراج السوابق القضائية لك بالأسفل. 
(ملاحظة: لتفعيل صياغة الإجابة الذكية، يرجى إضافة GEMINI_API_KEY في ملف .env الخاص بالمشروع).";
        }

        $prompt = "أنت مستشار قانوني سعودي خبير متخصص في تحليل الأحكام القضائية والسوابق.
مهمتك: الإجابة على سؤال العميل بناءً على 'السوابق القضائية' المرفقة لك أدناه.

قواعدك الصارمة:
1. الإجابة يجب أن تكون مستمدة من النصوص المرفقة (السوابق القضائية).
2. ابدأ إجابتك بذكر المبدأ القضائي المستخلص من الأحكام المرفقة.
3. استشهد بأرقام الأحكام والمراجع المذكورة في النصوص (مثال: 'استناداً إلى الحكم رقم ...').
4. إذا لم تجد إجابة مباشرة، وضح ما تقرره السوابق في حالات مشابهة.
5. لغة رصينة، قانونية، ومباشرة.

السوابق القضائية السعودية المتاحة:
" . $context . "

سؤال العميل:
" . $question;

        try {
            // Update to Gemini 2.5 Flash which is the standard model in 2026
            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2, // Lower temperature for more factual legal answers
                        'topP' => 0.8,
                        'topK' => 40
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "لم أتمكن من استخلاص المبدأ القضائي.";
            } else {
                return "حدث خطأ أثناء التواصل مع محرك Gemini: " . $response->body();
            }
        } catch (\Exception $e) {
            return "حدث خطأ في الاتصال بالذكاء الاصطناعي: " . $e->getMessage();
        }
    }
}
