<?php

namespace App\Services;

class ContentClassificationService
{
    /**
     * Medical keywords in Arabic
     */
    private const MEDICAL_KEYWORDS = [
        'علاج', 'مرض', 'صداع', 'حرارة', 'عملية', 'دواء', 'تشخيص', 
        'ضغط الدم', 'السكر', 'طبيب', 'مستشفى', 'عيادة', 'وجع',
        'ألم', 'حساسية', 'التهاب', 'فيروس', 'بكتيريا', 'عدوى',
        'جراحة', 'أشعة', 'تحليل', 'فحص', 'وصفة', 'صيدلية'
    ];

    /**
     * Legal keywords in Arabic
     */
    private const LEGAL_KEYWORDS = [
        'قضية', 'عقد', 'محكمة', 'طلاق', 'ميراث', 'نفقة', 'محامي',
        'مخالفة', 'شكوى رسمية', 'دعوى', 'قانون', 'حكم', 'استئناف',
        'تقاضي', 'وكيل', 'موثق', 'عدل', 'شهادة', 'إثبات', 'براءة',
        'إدانة', 'جنحة', 'جناية', 'حقوق', 'التزام'
    ];

    /**
     * Engineering keywords in Arabic
     */
    private const ENGINEERING_KEYWORDS = [
        'برمجة', 'كود', 'موقع', 'سيرفر', 'شبكة', 'كهرباء', 'خرسانة',
        'تصميم', 'API', 'قاعدة بيانات', 'تطبيق', 'سوفتوير', 'هاردوير',
        'معمار', 'إنشاء', 'بناء', 'هندسة', 'مشروع', 'تنفيذ', 'رسم',
        'مخطط', 'حساب', 'تحليل إنشائي', 'كمبيوتر', 'لارافيل', 'php',
        'javascript', 'python', 'java', 'database', 'server'
    ];

    /**
     * Business keywords in Arabic
     */
    private const BUSINESS_KEYWORDS = [
        'تجارة', 'أعمال', 'شركة', 'مشروع', 'استثمار', 'تسويق', 'مبيعات',
        'محاسبة', 'ميزانية', 'أرباح', 'خسائر', 'عقد تجاري', 'شراكة',
        'إدارة', 'موارد بشرية', 'توظيف', 'رواتب', 'ضرائب'
    ];

    /**
     * Classify content based on keyword matching
     *
     * @param string $text
     * @return string medical|legal|engineering|business|general
     */
    public function classify(string $text): string
    {
        $text = mb_strtolower($text);
        
        $scores = [
            'medical' => $this->calculateScore($text, self::MEDICAL_KEYWORDS),
            'legal' => $this->calculateScore($text, self::LEGAL_KEYWORDS),
            'engineering' => $this->calculateScore($text, self::ENGINEERING_KEYWORDS),
            'business' => $this->calculateScore($text, self::BUSINESS_KEYWORDS),
        ];

        // Get category with highest score
        $maxScore = max($scores);
        
        // If no keywords matched, return general
        if ($maxScore === 0) {
            return 'general';
        }

        // Return category with highest score
        return array_search($maxScore, $scores);
    }

    /**
     * Calculate matching score for a set of keywords
     *
     * @param string $text
     * @param array $keywords
     * @return int
     */
    private function calculateScore(string $text, array $keywords): int
    {
        $score = 0;
        
        foreach ($keywords as $keyword) {
            $keyword = mb_strtolower($keyword);
            // Count occurrences of keyword in text
            $count = mb_substr_count($text, $keyword);
            $score += $count;
        }
        
        return $score;
    }

    /**
     * Get all available categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return ['medical', 'legal', 'engineering', 'business', 'general'];
    }
}
