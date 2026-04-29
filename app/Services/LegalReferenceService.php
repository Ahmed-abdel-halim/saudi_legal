<?php

namespace App\Services;

use App\Models\LegalArticle;

class LegalReferenceService
{
    /**
     * Extract and fetch legal articles mentioned in a given text
     */
    public function getMentionedArticles(string $text, $fallbackSystem = null, $fallbackArticleNum = null)
    {
        $references = $this->extractReferences($text);
        
        if (empty($references) && $fallbackArticleNum && $fallbackArticleNum !== 'غير محدد') {
            $references[] = [
                'number' => $this->convertArabicNumbers($fallbackArticleNum),
                'system' => $fallbackSystem
            ];
        }

        if (empty($references)) {
            return collect();
        }

        $articles = collect();
        foreach ($references as $ref) {
            $systemToUse = $ref['system'] ?: $fallbackSystem;
            $article = $this->findArticle($ref['number'], $systemToUse);
            if ($article) {
                $articles->push($article);
            }
        }

        return $articles->unique('id');
    }

    /**
     * Wild Intelligent Extraction (الفصاحة والذكاء)
     */
    private function extractReferences(string $text)
    {
        $normalizedText = $this->normalizeTextualNumbers($text);
        
        // Clean up parentheses: ( 5 ) -> (5)
        $normalizedText = preg_replace('/\(\s*([0-9٠-٩]+)\s*\)/u', '($1)', $normalizedText);

        $matches = [];
        $lastSystem = null;

        // Pattern Parts
        $prefix = '[لوباتوف]*';
        // Added "مادته" and "مادتها" to the article words
        $articleWord = '(?:المادة|المواد|المادتين|مادته|مادتها|موادها)';
        $numberPart = '([\(\)0-9٠-٩\/\-،\s+و]+)';
        $paragraphPart = '(?:\s+' . $prefix . 'الفقرة\s+[\(\)\w\p{L}]+)?';
        
        // Smarter System Matcher: Stops at "الصادر" or "رقم" or "بتاريخ" and limits length to avoid matching entire sentences.
        // It stops at space followed by common transition words: المحكمة, الفصل, بتضمين, إذا, حتى, بما, في, على, عن, من, أن, المتعلق, الخاصة
        $systemMatcher = '((?:نظام|اللائحة|لائحة)\s+(?:(?!\s+(?:الصادر|رقم|بتاريخ|لعام|تاريخ|وهو|المحكمة|الفصل|بتضمين|إذا|حتى|بما|في|على|عن|من|أن|المتعلق|الخاصة))[\p{L}\s]){1,50}?)(?=\s+(?:الصادر|رقم|بتاريخ|لعام|تاريخ|وهو|المحكمة|الفصل|بتضمين|إذا|حتى|بما|في|على|عن|من|أن|المتعلق|الخاصة)|[\.\،\n\r]|$)';

        // 1. Explicit Pattern
        $explicitPattern = '/' . $prefix . $articleWord . '[:\s]+(?:رقم\s+)?' . $numberPart . $paragraphPart . '(?:\s+من\s+)?' . $systemMatcher . '/u';
        
        // 2. Implicit (Same system)
        $implicitPattern = '/' . $prefix . $articleWord . '[:\s]+(?:رقم\s+)?' . $numberPart . $paragraphPart . '\s+من\s+(?:ذات\s+النظام|النظام\s+المذكور|النظام\s+نفسه|منه)/u';
        
        // 3. Contextual/Simple
        $simplePattern = '/' . $prefix . $articleWord . '[:\s]+(?:رقم\s+)?' . $numberPart . '/u';

        // Execution - Pass 1
        if (preg_match_all($explicitPattern, $normalizedText, $found, PREG_SET_ORDER)) {
            foreach ($found as $match) {
                $lastSystem = trim($match[2]);
                foreach ($this->splitNumbers($match[1]) as $num) {
                    $matches[] = ['number' => $num, 'system' => $lastSystem];
                }
            }
        }

        // Execution - Pass 2
        if (preg_match_all($implicitPattern, $normalizedText, $found, PREG_SET_ORDER)) {
            foreach ($found as $match) {
                foreach ($this->splitNumbers($match[1]) as $num) {
                    $matches[] = ['number' => $num, 'system' => $lastSystem];
                }
            }
        }

        // Execution - Pass 3
        if (preg_match_all($simplePattern, $normalizedText, $found, PREG_SET_ORDER)) {
            foreach ($found as $match) {
                foreach ($this->splitNumbers($match[1]) as $num) {
                    $num = $this->convertArabicNumbers($num);
                    $exists = false;
                    foreach($matches as $m) { if($m['number'] == $num) $exists = true; }
                    if(!$exists) {
                        $matches[] = ['number' => $num, 'system' => $lastSystem];
                    }
                }
            }
        }

        return $matches;
    }

    private function splitNumbers($str)
    {
        $str = $this->convertArabicNumbers($str);
        $str = str_replace(['(', ')'], ' ', $str);
        $clean = str_replace(['و', '،', ','], ' ', $str);
        $parts = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        return array_unique($parts);
    }

    private function normalizeTextualNumbers($text)
    {
        $ones = [
            'الأولى' => 1, 'الأول' => 1, 'الثانية' => 2, 'الثاني' => 2, 'الثالثة' => 3, 'الثالث' => 3,
            'الرابعة' => 4, 'الرابع' => 4, 'الخامسة' => 5, 'الخامس' => 5, 'السادسة' => 6, 'السادس' => 6,
            'السابعة' => 7, 'السابع' => 7, 'الثامنة' => 8, 'الثامن' => 8, 'التاسعة' => 9, 'التاسع' => 9,
            'العاشرة' => 10, 'العاشر' => 10
        ];
        foreach ($ones as $word => $val) {
            $text = str_replace([$word . ' بعد المئة', $word . ' بعد المائة'], (string)($val + 100), $text);
        }

        $units = [
            'والعشرون' => 20, 'والعشرين' => 20, 'والثلاثون' => 30, 'والثلاثين' => 30, 'والأربعون' => 40, 'والأربعين' => 40,
            'والخمسون' => 50, 'والخمسين' => 50, 'والستون' => 60, 'والستين' => 60, 'والسبعون' => 70, 'والسبعين' => 70,
            'والثمانون' => 80, 'والثمانين' => 80, 'والتسعون' => 90, 'والتسعين' => 90
        ];
        $ones_complex = [
            'الحادية' => 1, 'الحادي' => 1, 'الثانية' => 2, 'الثاني' => 2, 'الثالثة' => 3, 'الثالث' => 3,
            'الرابعة' => 4, 'الرابع' => 4, 'الخامسة' => 5, 'الخامس' => 5, 'السادسة' => 6, 'السادس' => 6,
            'السابعة' => 7, 'السابع' => 7, 'الثامنة' => 8, 'الثامن' => 8, 'التاسعة' => 9, 'التاسع' => 9
        ];
        foreach ($units as $uWord => $uDigit) {
            foreach ($ones_complex as $oWord => $oDigit) {
                $text = str_replace($oWord . ' ' . $uWord, (string)($uDigit + $oDigit), $text);
            }
        }

        $map = [
            'الحادية عشرة' => '11', 'الحادية عشر' => '11', 'الحادي عشر' => '11',
            'الثانية عشرة' => '12', 'الثانية عشر' => '12', 'الثاني عشر' => '12',
            'الثالثة عشرة' => '13', 'الثالثة عشر' => '13', 'الثالث عشر' => '13',
            'الرابعة عشرة' => '14', 'الرابعة عشر' => '14', 'الرابع عشر' => '14',
            'الخامسة عشرة' => '15', 'الخامسة عشر' => '15', 'الخامس عشر' => '15',
            'السادسة عشرة' => '16', 'السادسة عشر' => '16', 'السادس عشر' => '16',
            'السابعة عشرة' => '17', 'السابعة عشر' => '17', 'السابع عشر' => '17',
            'الثامنة عشرة' => '18', 'الثامنة عشر' => '18', 'الثامن عشر' => '18',
            'التاسعة عشرة' => '19', 'التاسعة عشر' => '19', 'التاسع عشر' => '19',
            'العشرون' => '20', 'العشرين' => '20', 'الثلاثون' => '30', 'الثلاثين' => '30', 'الأربعون' => '40', 'الأربعين' => '40',
            'الخمسون' => '50', 'الخمسين' => '50', 'الستون' => '60', 'الستين' => '60', 'السبعون' => '70', 'السبعين' => '70',
            'الثمانون' => '80', 'الثمانين' => '80', 'التسعون' => '90', 'التسعين' => '90', 'المائة' => '100', 'المئة' => '100'
        ];
        foreach ($map as $word => $digit) { $text = str_replace($word, $digit, $text); }
        foreach ($ones as $word => $digit) { $text = str_replace($word, (string)$digit, $text); }

        return $text;
    }

    private function findArticle($number, $systemName = null)
    {
        $query = LegalArticle::query();
        
        $possibleTitles = [
            "المادة {$number}",
            "المادة ({$number})",
            "المادة " . $this->numberToArabicText($number)
        ];

        $query->where(function($q) use ($possibleTitles) {
            foreach ($possibleTitles as $title) {
                $q->orWhere('article_title', 'LIKE', "%{$title}%");
            }
        });

        if ($systemName) {
            $cleanSystem = preg_replace('/^(من نظام|نظام|اللائحة|من لائحة|نظام الـ|بناء على|استنادا على|المواد[:\s]+)\s+/u', '', $systemName);
            $cleanSystem = trim($cleanSystem);
            
            // Intelligence mapping
            if (str_contains($cleanSystem, 'الإثبات')) {
                $query->where('legislation_title', 'LIKE', '%نظام الإثبات%');
            } elseif (str_contains($cleanSystem, 'المرافعات')) {
                $query->where('legislation_title', 'LIKE', '%نظام المرافعات الشرعية%');
            } elseif (str_contains($cleanSystem, 'التجارية')) {
                $query->where('legislation_title', 'LIKE', str_contains($systemName, 'اللائحة') ? '%اللائحة التنفيذية لنظام المحاكم التجارية%' : '%نظام المحاكم التجارية%');
            } elseif (str_contains($cleanSystem, 'المدنية')) {
                $query->where('legislation_title', 'LIKE', '%نظام المعاملات المدنية%');
            } elseif (str_contains($cleanSystem, 'الشركات')) {
                $query->where('legislation_title', 'LIKE', '%نظام الشركات%');
            } elseif (str_contains($cleanSystem, 'التحكيم')) {
                $query->where('legislation_title', 'LIKE', '%نظام التحكيم%');
            } else {
                $query->where('legislation_title', 'LIKE', '%' . $cleanSystem . '%');
            }
        }

        return $query->first();
    }

    private function numberToArabicText($n)
    {
        $n = (int)$n;
        $map = [
            1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة',
            6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة', 10 => 'العاشرة',
            11 => 'الحادية عشرة', 12 => 'الثانية عشرة', 13 => 'الثالثة عشرة', 14 => 'الرابعة عشرة', 15 => 'الخامسة عشرة',
            16 => 'السادسة عشرة', 17 => 'السابعة عشرة', 18 => 'الثامنة عشرة', 19 => 'التاسعة عشرة', 20 => 'العشرون',
            30 => 'الثلاثون', 40 => 'الأربعون', 50 => 'الخمسون', 60 => 'الستون', 70 => 'السبعون', 80 => 'الثمانون', 90 => 'التسعون'
        ];

        if (isset($map[$n])) return $map[$n];
        
        if ($n > 100) {
            $rem = $n % 100;
            if ($rem == 0) return 'المائة';
            return $this->numberToArabicText($rem) . " بعد المائة";
        }

        if ($n > 20) {
            $ones = $n % 10;
            $tens = $n - $ones;
            $onesMap = [1 => 'الحادية', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة', 6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة'];
            return ($onesMap[$ones] ?? $ones) . " و" . ($map[$tens] ?? $tens);
        }

        return (string)$n;
    }

    private function convertArabicNumbers($str)
    {
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $latin = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($arabic, $latin, $str);
    }
}
