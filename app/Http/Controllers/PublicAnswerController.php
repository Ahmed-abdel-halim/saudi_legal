<?php

namespace App\Http\Controllers;

use App\Models\PublicLegalAnswer;
use Illuminate\Http\Request;

class PublicAnswerController extends Controller
{
    /**
     * عرض صفحة السؤال القانوني باللغة العربية
     */
    public function showArabic(string $slug)
    {
        $answer = PublicLegalAnswer::where('slug', $slug)
            ->where('locale', 'ar')
            ->firstOrFail();

        // زيادة عداد المشاهدات لكل زيارة
        $answer->increment('views_count');

        // جلب النسخة الإنجليزية المقابلة لـ hreflang
        $englishCounterpart = null;
        if ($answer->counterpart_slug) {
            $englishCounterpart = PublicLegalAnswer::where('slug', $answer->counterpart_slug)
                ->where('locale', 'en')
                ->first();
        }

        $arabicCounterpart = $answer; // نفس الصفحة هي العربية

        return view('frontend.answer-detail', compact('answer', 'englishCounterpart', 'arabicCounterpart'));
    }

    /**
     * عرض صفحة السؤال القانوني باللغة الإنجليزية
     */
    public function showEnglish(string $slug)
    {
        $answer = PublicLegalAnswer::where('slug', $slug)
            ->where('locale', 'en')
            ->firstOrFail();

        // زيادة عداد المشاهدات
        $answer->increment('views_count');

        // جلب النسخة العربية المقابلة لـ hreflang
        $arabicCounterpart = null;
        if ($answer->counterpart_slug) {
            $arabicCounterpart = PublicLegalAnswer::where('slug', $answer->counterpart_slug)
                ->where('locale', 'ar')
                ->first();
        }

        $englishCounterpart = $answer; // نفس الصفحة هي الإنجليزية

        return view('frontend.answer-detail', compact('answer', 'englishCounterpart', 'arabicCounterpart'));
    }
}
