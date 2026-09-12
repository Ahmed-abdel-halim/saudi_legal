<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShariaCitation extends Model
{
    protected $fillable = [
        'sharia_record_id',
        'sharia_qa_pair_id',
        'citation_type',
        'surah_name',
        'ayah_number',
        'quran_text',
        'hadith_text',
        'hadith_source',
        'hadith_number',
        'hadith_grade',
        'scholar_name',
        'scholar_quote',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(ShariaRecord::class, 'sharia_record_id');
    }

    public function qaPair(): BelongsTo
    {
        return $this->belongsTo(FatwaQaPair::class, 'sharia_qa_pair_id');
    }

    public function toCitationArray(): array
    {
        if ($this->citation_type === 'quran') {
            return [
                'type'        => 'quran',
                'surah'       => $this->surah_name,
                'ayah'        => $this->ayah_number,
                'text'        => $this->quran_text,
            ];
        }

        if ($this->citation_type === 'hadith') {
            return [
                'type'        => 'hadith',
                'text'        => $this->hadith_text,
                'source'      => $this->hadith_source,
                'number'      => $this->hadith_number,
                'grade'       => $this->hadith_grade,
            ];
        }

        return [
            'type'            => 'scholar',
            'scholar_name'    => $this->scholar_name,
            'quote'           => $this->scholar_quote,
        ];
    }
}
