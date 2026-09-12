<?php

namespace App\Services\Sharia;

class ShariaNormalizer
{
    public static array $stopwords = [
        'ما', 'هو', 'هي', 'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'حكم', 'حكمه', 'حكمها', 'الحكم',
        'ماذا', 'كيف', 'متى', 'أين', 'ذلك', 'هذا', 'هذه', 'وما', 'وهل', 'داخل', 'عند', 'بعد',
        'قبل', 'بين', 'التي', 'الذي', 'الذين', 'إذا', 'ان', 'أن', 'أو', 'ثم', 'لا', 'لم', 'لن',
        'له', 'لها', 'لهم', 'لنا', 'به', 'بها', 'بهم', 'فيه', 'فيها', 'منه', 'منها', 'عليه', 'عليها',
        'الشرعي', 'شرعا', 'شرعاً', 'فيها', 'كان', 'يكون', 'تكون'
    ];

    /**
     * Normalize Arabic text for accurate semantic and keyword deduplication.
     */
    public static function normalize(string $text): string
    {
        // 1. Remove Tashkeel (Diacritics / Harakat)
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        // 2. Remove Tatweel / Kashida
        $text = preg_replace('/[\x{0640}]/u', '', $text);

        // 3. Normalize Alef variants (أ, إ, آ, ٱ -> ا)
        $text = preg_replace('/[أإآٱ]/u', 'ا', $text);

        // 4. Normalize Taa Marbuta (ة -> ه)
        $text = preg_replace('/[ة]/u', 'ه', $text);

        // 5. Normalize Yaa / Alef Maqsura (ى -> ي)
        $text = preg_replace('/[ى]/u', 'ي', $text);

        // 6. Remove excess punctuation and spaces
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim(mb_strtolower($text, 'UTF-8'));
    }

    /**
     * Extract substantive keywords, filtering out general grammar/question stopwords.
     */
    public static function extractSubstantiveKeywords(string $text): array
    {
        $normalized = self::normalize($text);
        $words = array_filter(explode(' ', $normalized), fn($w) => mb_strlen($w) >= 3);
        $substantive = array_filter($words, fn($w) => !in_array($w, self::$stopwords));
        return array_values(array_unique($substantive));
    }

    /**
     * Compute a deduplication hash for a question or text.
     */
    public static function deduplicationHash(string $text): string
    {
        $words = self::extractSubstantiveKeywords($text);
        sort($words);
        return hash('sha256', implode(' ', $words));
    }

    /**
     * Check similarity score between two Arabic strings based on substantive keywords (0.0 to 1.0).
     */
    public static function similarity(string $str1, string $str2): float
    {
        $words1 = self::extractSubstantiveKeywords($str1);
        $words2 = self::extractSubstantiveKeywords($str2);

        if (empty($words1) || empty($words2)) return 0.0;

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        return count($intersection) / count($union);
    }
}
