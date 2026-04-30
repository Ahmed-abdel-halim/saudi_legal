<?php

namespace App\Services;

use App\Models\LegalArticle;

class LegalReferenceService
{
    /**
     * Main entry point to get articles from any text
     */
    public function getMentionedArticles(string $text, $fallbackSystem = null, $fallbackArticleNum = null)
    {
        $text = $this->convertArabicNumbers($text);
        $references = $this->extractReferences($text);
        
        $articles = collect();
        foreach ($references as $ref) {
            $systemToUse = $ref['system'] ?: $fallbackSystem;
            $article = $this->findArticle($ref['number'], $systemToUse);
            if ($article) $articles->push($article);
        }

        // Fallback to task specific article if text extraction yielded nothing
        if ($articles->isEmpty() && $fallbackArticleNum && $fallbackArticleNum !== 'غير محدد') {
            $num = $this->convertArabicNumbers($fallbackArticleNum);
            $article = $this->findArticle($num, $fallbackSystem);
            if ($article) $articles->push($article);
        }

        return $articles->unique('id');
    }

    private function extractReferences(string $text)
    {
        $normalizedText = $this->normalizeTextualNumbers($text);
        $matches = [];
        $lastDetectedSystem = null;
        
        // Pattern for "Material X" or "Materials X, Y, Z"
        $articlePattern = '/(?:المادة|المواد|للمادة|للمواد|بالمادة|بالمواد|مادته|مادتها|موادها)[\s:]+([\d\(\)\s،و\-]+)/u';
        
        if (preg_match_all($articlePattern, $normalizedText, $found, PREG_SET_ORDER)) {
            foreach ($found as $match) {
                $numbersString = $match[1];
                $numbers = $this->splitNumbers($numbersString);
                
                $pos = mb_strpos($normalizedText, $match[0]);
                $context = mb_substr($normalizedText, $pos, 350);
                
                $system = $this->detectSystem($context);
                
                // Handle "Same System" references
                if (!$system && preg_match('/(ذات النظام|النظام المذكور|نفس النظام|النظام نفسه|منه|نظامه)/u', $context)) {
                    $system = $lastDetectedSystem;
                }
                
                // Check context BEFORE the mention
                if (!$system) {
                    $preContext = mb_substr($normalizedText, max(0, $pos - 300), 300);
                    $system = $this->detectSystem($preContext);
                }

                if ($system) $lastDetectedSystem = $system;

                foreach ($numbers as $num) {
                    $matches[] = ['number' => $num, 'system' => $system ?: $lastDetectedSystem];
                }
            }
        }

        return $matches;
    }

    private function detectSystem($text)
    {
        $norm = $this->normalizeArabic($text);
        
        // Core Saudi Laws Mapping
        $map = [
            'اثبات' => 'نظام الإثبات',
            'مرافعات' => 'نظام المرافعات الشرعية',
            'تجاريه' => 'نظام المحاكم التجارية',
            'مدنيه' => 'نظام المعاملات المدنية',
            'معاملات' => 'نظام المعاملات المدنية',
            'شركات' => 'نظام الشركات',
            'تحكيم' => 'نظام التحكيم',
            'تنفيذ' => 'نظام التنفيذ',
            'عمل' => 'نظام العمل',
            'عمالي' => 'نظام العمل',
            'جزائيه' => 'نظام الإجراءات الجزائية',
            'عقوبات' => 'نظام العقوبات',
            'مظالم' => 'نظام ديوان المظالم',
            'افلاس' => 'نظام الإفلاس',
            'تامينات' => 'نظام التأمينات الاجتماعية',
            'جمارك' => 'نظام الجمارك',
            'ضريبه' => 'نظام الضريبة',
        ];

        foreach ($map as $key => $fullName) {
            if (str_contains($norm, $key)) {
                // Check if it's the executive regulations (اللائحة التنفيذية)
                if (str_contains($norm, 'لائحه') || str_contains($norm, 'تنفيذيه')) {
                    return "اللائحة التنفيذية ل" . $fullName;
                }
                return $fullName;
            }
        }
        
        return null;
    }

    private function findArticle($number, $systemName = null)
    {
        if (empty($number)) return null;
        $query = LegalArticle::query();
        
        // Multi-format search for the article number/title
        $arabicText = $this->numberToArabicText($number);
        $arabicTextAlt = str_replace('ون', 'ين', $arabicText);

        $query->where(function($q) use ($number, $arabicText, $arabicTextAlt) {
            $q->where('article_title', 'LIKE', "% {$number}%")
              ->orWhere('article_title', 'LIKE', "%({$number})%")
              ->orWhere('article_title', 'LIKE', "%{$number} %")
              ->orWhere('article_title', 'LIKE', "%{$arabicText}%")
              ->orWhere('article_title', 'LIKE', "%{$arabicTextAlt}%");
        });

        if ($systemName) {
            $norm = $this->normalizeArabic($systemName);
            // Dynamic system filtering
            $keywords = preg_split('/\s+/', $norm, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function($q) use ($keywords, $norm) {
                // Primary keywords
                $mainKeywords = array_filter($keywords, function($w) { return mb_strlen($w) > 3; });
                foreach ($mainKeywords as $word) {
                    $q->where('legislation_title', 'LIKE', "%{$word}%");
                }
                // Alef-insensitive fallback for "اثبات" etc
                if (str_contains($norm, 'اثبات')) $q->orWhere('legislation_title', 'LIKE', '%إثبات%');
            });
        }

        $res = $query->first();
        
        // Deep fallback: Search within content if system is confirmed
        if (!$res && $systemName) {
            $fallback = LegalArticle::query();
            $norm = $this->normalizeArabic($systemName);
            if (str_contains($norm, 'اثبات')) $fallback->where('legislation_title', 'LIKE', '%إثبات%')->orWhere('legislation_title', 'LIKE', '%اثبات%');
            elseif (str_contains($norm, 'تجاريه')) $fallback->where('legislation_title', 'LIKE', '%تجارية%');
            
            return $fallback->where(function($q) use ($number) {
                    $q->where('content', 'LIKE', "%المادة {$number}%")
                      ->orWhere('content', 'LIKE', "%المادة ({$number})%");
                })->first();
        }

        return $res;
    }

    private function normalizeArabic($text)
    {
        if (empty($text)) return '';
        $text = preg_replace('/[أإآ]/u', 'ا', $text);
        $text = str_replace(['ة','ى'], ['ه','ي'], $text);
        return mb_strtolower(trim($text));
    }

    private function splitNumbers($str)
    {
        $clean = str_replace(['و', '،', ',', '(', ')'], ' ', $str);
        return array_unique(preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY));
    }

    private function normalizeTextualNumbers($text)
    {
        // Comprehensive map for common article numbers in Arabic
        $map = [
            'الأولى' => '1', 'الثانية' => '2', 'الثالثة' => '3', 'الرابعة' => '4', 'الخامسة' => '5',
            'السادسة' => '6', 'السابعة' => '7', 'الثامنة' => '8', 'التاسعة' => '9', 'العاشرة' => '10',
            'الحادية عشرة' => '11', 'الثانية عشرة' => '12', 'الثالثة عشرة' => '13',
            'العشرون' => '20', 'العشرين' => '20', 'الثلاثون' => '30', 'الثلاثين' => '30',
            'التسعين' => '90', 'التسعون' => '90',
            'الحادية والتسعين' => '91', 'الحادية والتسعون' => '91',
            'الثانية والتسعين' => '92', 'الثانية والتسعون' => '92',
            'السابعة والتسعين' => '97', 'السابعة والتسعون' => '97',
            'الثامنة والتسعين' => '98', 'الثامنة والتسعون' => '98',
            'المائة' => '100', 'الواحدة بعد المائة' => '101', 'الرابعة بعد المائة' => '164' // specifically for article 164
        ];
        foreach ($map as $word => $digit) { $text = str_replace($word, $digit, $text); }
        return $text;
    }

    private function convertArabicNumbers($str)
    {
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $latin = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($arabic, $latin, $str);
    }

    private function numberToArabicText($n)
    {
        $map = [
            1 => 'الأولى', 2 => 'الثانية', 10 => 'العاشرة',
            92 => 'الثانية والتسعون', 97 => 'السابعة والتسعون', 98 => 'الثامنة والتسعون',
            164 => 'الرابعة بعد المائة'
        ];
        return $map[(int)$n] ?? (string)$n;
    }
}
