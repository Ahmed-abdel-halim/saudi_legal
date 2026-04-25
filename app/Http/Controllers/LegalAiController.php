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

        $contextArticles = $this->searchService->search($question, 3);
        
        if ($contextArticles->isEmpty()) {
            return response()->json([
                'answer' => "عذراً، لم أجد نصوصاً قانونية مباشرة تتعلق بسؤالك في قاعدة البيانات الحالية. هل يمكنك صياغة السؤال بشكل مختلف؟",
                'citations' => []
            ]);
        }

        $contextText = "";
        $citations = [];
        foreach ($contextArticles as $article) {
            $contextText .= "النظام: {$article->legislation_title} | {$article->article_title}: {$article->content}\n\n";
            $citations[] = [
                'title' => $article->legislation_title,
                'article' => $article->article_title,
                'text' => $article->content
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
            return "مرحباً! لقد قمت باستخراج المواد القانونية لك بالأسفل. 
(ملاحظة: لتفعيل صياغة الإجابة الذكية، يرجى إضافة GEMINI_API_KEY في ملف .env الخاص بالمشروع للحصول عليها مجاناً من aistudio.google.com).";
        }

        $prompt = "أنت مستشار قانوني سعودي محترف ورسمي. 
قواعدك الأساسية:
1. يجب أن تبني إجابتك حصرياً على النصوص القانونية المرفقة لك أدناه.
2. اقتبس بأرقام المواد وأسماء الأنظمة المرفقة لتأكيد إجابتك.
3. إذا كان السؤال خارج إطار النصوص المرفقة، اعتذر بلباقة وتحدث بلسان المستشار.
4. استخدم لغة عربية فصحى قانونية ورصينة.

النصوص القانونية السعودية المتاحة للإجابة منها:
" . $context . "

سؤال العميل:
" . $question;

        try {
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
                        'temperature' => 0.3
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "لم أتمكن من صياغة الإجابة.";
            } else {
                return "حدث خطأ أثناء التواصل مع محرك Gemini: " . $response->body();
            }
        } catch (\Exception $e) {
            return "حدث خطأ في الاتصال بالذكاء الاصطناعي: " . $e->getMessage();
        }
    }
}
