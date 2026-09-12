<?php

namespace App\Services\Sharia;

use App\Models\ShariaRecord;
use App\Models\FatwaQaPair;
use App\Models\ShariaCitation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FatwaHarvesterService
{
    /**
     * Ingest a single raw fatwa or scraped record, deduplicate, structure, and save.
     */
    public function ingestRecord(array $rawFatwa): array
    {
        $question = trim($rawFatwa['question'] ?? '');
        $answer = trim($rawFatwa['answer'] ?? '');
        $sourceAuthority = trim($rawFatwa['source_authority'] ?? 'هيئة كبار العلماء واللجنة الدائمة');
        $subDomain = trim($rawFatwa['sub_domain'] ?? 'فقه عام');
        $sourceUrl = $rawFatwa['source_url'] ?? null;

        if (empty($question) || empty($answer)) {
            return ['status' => 'skipped', 'reason' => 'Empty question or answer'];
        }

        // 1. Deduplication check against existing QA pairs
        $existingQAs = FatwaQaPair::with('record')->get();
        foreach ($existingQAs as $existing) {
            $similarity = ShariaNormalizer::similarity($question, $existing->question);
            if ($similarity >= 0.85) {
                return [
                    'status'   => 'duplicate',
                    'reason'   => "Matched existing QA #{$existing->id} with similarity: " . round($similarity * 100, 1) . "%",
                    'match_id' => $existing->id
                ];
            }
        }

        // 2. Extract Citations from text (Quran, Hadith, Scholars)
        $citations = $this->extractCitations($answer);

        // 3. Generate Record ID
        $nextId = ShariaRecord::count() + 1;
        $recordId = 'SHARIA_FATWA_' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);

        // 4. Save Record
        $record = ShariaRecord::create([
            'record_id'           => $recordId,
            'domain'              => 'Islamic Sharia & Jurisprudence',
            'sub_domain'          => $subDomain,
            'source_authority'    => $sourceAuthority,
            'verification_status' => $rawFatwa['verification_status'] ?? 'VERIFIED',
            'title'               => Str::limit($question, 120),
            'full_text'           => $answer,
            'summary'             => Str::limit(strip_tags($answer), 250),
            'core_principles'     => $rawFatwa['core_principles'] ?? [],
            'tags'                => $rawFatwa['tags'] ?? [$subDomain],
            'source_url'          => $sourceUrl,
        ]);

        // 5. Save QA Pair
        $qaPair = FatwaQaPair::create([
            'sharia_record_id' => $record->id,
            'qa_id'            => $recordId . '_QA_1',
            'question'         => $question,
            'generated_answer' => $answer,
            'corrected_answer' => $answer,
            'review_status'    => 'Approved',
            'traffic_light'    => 'green',
            'reviewed_at'      => now(),
        ]);

        // 6. Save Citations
        foreach ($citations['quran'] as $q) {
            ShariaCitation::create([
                'sharia_record_id'  => $record->id,
                'sharia_qa_pair_id' => $qaPair->id,
                'citation_type'     => 'quran',
                'surah_name'        => $q['surah'] ?? null,
                'ayah_number'       => $q['ayah'] ?? null,
                'quran_text'        => $q['text'] ?? null,
            ]);
        }

        foreach ($citations['hadith'] as $h) {
            ShariaCitation::create([
                'sharia_record_id'  => $record->id,
                'sharia_qa_pair_id' => $qaPair->id,
                'citation_type'     => 'hadith',
                'hadith_text'       => $h['text'],
                'hadith_source'     => $h['source'] ?? 'صحيح السنة',
                'hadith_grade'      => $h['grade'] ?? 'صحيح',
            ]);
        }

        foreach ($citations['scholars'] as $s) {
            ShariaCitation::create([
                'sharia_record_id'  => $record->id,
                'sharia_qa_pair_id' => $qaPair->id,
                'citation_type'     => 'scholar',
                'scholar_name'      => $s,
            ]);
        }

        return [
            'status'     => 'created',
            'record_id'  => $recordId,
            'qa_pair_id' => $qaPair->id,
            'citations'  => count($citations['quran']) + count($citations['hadith']) + count($citations['scholars']),
        ];
    }

    /**
     * Automatically extract Quran, Hadith, and Scholar citations using regex patterns.
     */
    public function extractCitations(string $text): array
    {
        $quran = [];
        $hadiths = [];
        $scholars = [];

        // 1. Quran verses inside ﴿...﴾ or brackets
        if (preg_match_all('/[﴿\(]([^﴾\)]+)[﴾\)]\s*(?:\[([^\]]+)\])?/u', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $verseText = trim($m[1]);
                $reference = isset($m[2]) ? trim($m[2]) : '';
                
                $surah = null;
                $ayah = null;
                if ($reference && preg_match('/(?:سورة\s+)?([^\d:]+)\s*[:\s]\s*(\d+)/u', $reference, $refMatch)) {
                    $surah = trim($refMatch[1]);
                    $ayah = (int)$refMatch[2];
                }

                if (mb_strlen($verseText) > 10) {
                    $quran[] = [
                        'text'  => $verseText,
                        'surah' => $surah,
                        'ayah'  => $ayah,
                    ];
                }
            }
        }

        // 2. Hadith patterns: (قال رسول الله ﷺ: «...» أو رواه ...)
        if (preg_match_all('/(?:قال رسول الله|عن النبي|قال ﷺ|لقوله ﷺ)[^«"]*[«"]([^»"]+)[»"]/u', $text, $hMatches)) {
            foreach ($hMatches[1] as $hText) {
                $source = 'متفق عليه';
                if (mb_stripos($text, 'رواه مسلم') !== false) $source = 'صحيح مسلم';
                elseif (mb_stripos($text, 'رواه البخاري') !== false) $source = 'صحيح البخاري';
                elseif (mb_stripos($text, 'أبو داود') !== false) $source = 'سنن أبي داود';
                elseif (mb_stripos($text, 'الترمذي') !== false) $source = 'جامع الترمذي';

                $hadiths[] = [
                    'text'   => trim($hText),
                    'source' => $source,
                    'grade'  => 'صحيح',
                ];
            }
        }

        // 3. Known authoritative scholars
        $knownScholars = [
            'ابن باز', 'عبدالعزيز بن باز',
            'ابن عثيمين', 'محمد بن صالح العثيمين',
            'الألباني', 'ناصر الدين الألباني',
            'ابن تيمية', 'ابن القيم',
            'اللجنة الدائمة', 'هيئة كبار العلماء',
            'النووي', 'ابن قدامة', 'ابن كثير', 'القرطبي'
        ];

        foreach ($knownScholars as $scholar) {
            if (mb_stripos($text, $scholar) !== false && !in_array($scholar, $scholars)) {
                $scholars[] = $scholar;
            }
        }

        return [
            'quran'    => $quran,
            'hadith'   => $hadiths,
            'scholars' => $scholars,
        ];
    }
}
