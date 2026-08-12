<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicLegalAnswer extends Model
{
    use HasFactory;

    protected $table = 'public_legal_answers';

    protected $fillable = [
        'locale',
        'slug',
        'question',
        'answer',
        'citations',
        'views_count',
        'source_type',
        'source_id',
        'counterpart_slug',
        'translated_at',
    ];

    protected $casts = [
        'citations'     => 'array',
        'views_count'   => 'integer',
        'translated_at' => 'datetime',
    ];

    /**
     * Scope: السجلات الإنجليزية التي لم تُترجَم بعد
     */
    public function scopeUntranslated($query)
    {
        return $query->where('locale', 'en')->whereNull('translated_at');
    }

    /**
     * Scope: تصفية بالغة
     */
    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * جلب النسخة المقابلة (عربي <-> إنجليزي)
     */
    public function getCounterpartAttribute(): ?self
    {
        if (!$this->counterpart_slug) {
            return null;
        }

        $targetLocale = $this->locale === 'ar' ? 'en' : 'ar';

        return self::where('slug', $this->counterpart_slug)
            ->where('locale', $targetLocale)
            ->first();
    }

    /**
     * رابط الصفحة العامة
     */
    public function getPublicUrlAttribute(): string
    {
        return $this->locale === 'en'
            ? route('public.qa.en', $this->slug)
            : route('public.qa.ar', $this->slug);
    }

    /**
     * نص الإجابة بدون وسوم HTML
     */
    public function getPlainAnswerAttribute(): string
    {
        return strip_tags($this->answer);
    }
}
