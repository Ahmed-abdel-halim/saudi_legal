<?php

namespace App\Services\Sharia;

use App\Models\ShariaRecord;
use App\Models\FatwaQaPair;
use App\Models\ShariaCitation;
use Illuminate\Support\Str;

class FatwaHarvesterService
{
    /**
     * Ingest a single raw fatwa or scraped record, deduplicate, structure, and save.
     */
    public function ingestRecord(array $rawFatwa): array
    {
        // 1. Support both nested schema (38k corpus) and legacy flat schema
        $question = trim($rawFatwa['raw_and_sanitized_contexts']['question_raw'] ?? ($rawFatwa['question'] ?? ''));
        $answer   = trim($rawFatwa['raw_and_sanitized_contexts']['answer_raw'] ?? ($rawFatwa['answer'] ?? ''));

        if (empty($question) || empty($answer)) {
            return ['status' => 'skipped', 'reason' => 'Empty question or answer'];
        }

        $recordId = $rawFatwa['jurisprudential_variant']['fatwa_id'] ?? ($rawFatwa['record_id'] ?? null);
        if (!$recordId) {
            $recordId = 'SHARIA_FTW_' . md5($question);
        }

        // Fast Deduplication via Indexed record_id
        if (ShariaRecord::where('record_id', $recordId)->exists()) {
            return ['status' => 'duplicate', 'reason' => "Record {$recordId} already exists"];
        }

        $sourceAuthority = trim($rawFatwa['jurisprudential_variant']['issuing_scholar'] ?? ($rawFatwa['source_authority'] ?? 'مركز الفتوى - إسلام ويب'));
        $category        = trim($rawFatwa['fiqh_taxonomy_classification']['primary_category'] ?? ($rawFatwa['domain'] ?? 'فقه عام'));
        $subTopic        = trim($rawFatwa['fiqh_taxonomy_classification']['sub_topic'] ?? ($rawFatwa['sub_domain'] ?? ''));
        $subDomain       = !empty($subTopic) ? "{$category} > {$subTopic}" : $category;
        $directRuling    = trim($rawFatwa['jurisprudential_variant']['direct_ruling'] ?? ($rawFatwa['direct_ruling'] ?? ''));
        $sourceUrl       = $rawFatwa['jurisprudential_variant']['canonical_url'] ?? ($rawFatwa['source_url'] ?? null);
        $tags            = $rawFatwa['fiqh_taxonomy_classification']['tags'] ?? ($rawFatwa['tags'] ?? [$category]);

        // 2. Save Sharia Master Record
        $record = ShariaRecord::create([
            'record_id'           => $recordId,
            'domain'              => $category,
            'sub_domain'          => Str::limit($subDomain, 190),
            'source_authority'    => Str::limit($sourceAuthority, 190),
            'verification_status' => $rawFatwa['verification_status'] ?? 'VERIFIED',
            'title'               => Str::limit($question, 190),
            'full_text'           => $answer,
            'summary'             => Str::limit(strip_tags($answer), 250),
            'core_principles'     => ['direct_ruling' => $directRuling],
            'tags'                => $tags,
            'source_url'          => Str::limit($sourceUrl, 950),
        ]);

        // 3. Save QA Pair
        $qaPair = FatwaQaPair::create([
            'sharia_record_id' => $record->id,
            'qa_id'            => $recordId . '_QA',
            'question'         => $question,
            'generated_answer' => $answer,
            'corrected_answer' => $answer,
            'review_status'    => 'Approved',
            'traffic_light'    => 'green',
            'reviewed_at'      => now(),
        ]);

        // 4. Save Citations (Quran, Hadith, Scholars)
        $citationsCount = 0;

        // A. Quran citations from universal_evidence_engine
        $quranCits = $rawFatwa['universal_evidence_engine']['quranic_citations'] ?? [];
        if (is_array($quranCits)) {
            foreach ($quranCits as $qc) {
                if (!is_array($qc)) continue;
                $surah = is_array($qc['surah'] ?? null) ? implode(', ', $qc['surah']) : ($qc['surah'] ?? null);
                $ayah  = $qc['ayah_num'] ?? null;
                if (is_array($ayah)) {
                    $ayah = !empty($ayah) ? (int)$ayah[0] : null;
                } elseif ($ayah !== null) {
                    $ayah = is_numeric($ayah) ? (int)$ayah : null;
                }
                $qText = is_array($qc['text'] ?? null) ? implode(' ', $qc['text']) : ($qc['text'] ?? null);

                try {
                    ShariaCitation::create([
                        'sharia_record_id'  => $record->id,
                        'sharia_qa_pair_id' => $qaPair->id,
                        'citation_type'     => 'quran',
                        'surah_name'        => $surah ? Str::limit((string)$surah, 190) : null,
                        'ayah_number'       => $ayah,
                        'quran_text'        => $qText ? (string)$qText : null,
                    ]);
                    $citationsCount++;
                } catch (\Throwable $e) {}
            }
        }

        // B. Hadith citations from universal_evidence_engine
        $hadithCits = $rawFatwa['universal_evidence_engine']['hadith_citations'] ?? [];
        if (is_array($hadithCits)) {
            foreach ($hadithCits as $hc) {
                if (!is_array($hc)) continue;
                $hText   = $hc['canonical_title'] ?? ($hc['text'] ?? '');
                if (is_array($hText)) $hText = implode(' ', $hText);

                $hSource = $hc['takhrij_primary'] ?? ($hc['source'] ?? 'السنة النبوية');
                if (is_array($hSource)) $hSource = implode(', ', $hSource);

                $hGrade  = $hc['authenticity_grade'] ?? ($hc['grade'] ?? 'صحيح');
                if (is_array($hGrade)) $hGrade = implode(', ', $hGrade);

                try {
                    ShariaCitation::create([
                        'sharia_record_id'  => $record->id,
                        'sharia_qa_pair_id' => $qaPair->id,
                        'citation_type'     => 'hadith',
                        'hadith_text'       => $hText ? (string)$hText : '',
                        'hadith_source'     => $hSource ? Str::limit((string)$hSource, 190) : 'السنة النبوية',
                        'hadith_grade'      => $hGrade ? Str::limit((string)$hGrade, 190) : 'صحيح',
                    ]);
                    $citationsCount++;
                } catch (\Throwable $e) {}
            }
        }

        // C. Fallback: regex extraction if no citations were present
        if ($citationsCount === 0) {
            $extracted = $this->extractCitations($answer);
            foreach ($extracted['quran'] as $q) {
                try {
                    ShariaCitation::create([
                        'sharia_record_id'  => $record->id,
                        'sharia_qa_pair_id' => $qaPair->id,
                        'citation_type'     => 'quran',
                        'surah_name'        => $q['surah'] ?? null,
                        'ayah_number'       => is_numeric($q['ayah'] ?? null) ? (int)$q['ayah'] : null,
                        'quran_text'        => $q['text'] ?? null,
                    ]);
                    $citationsCount++;
                } catch (\Throwable $e) {}
            }
            foreach ($extracted['hadith'] as $h) {
                try {
                    ShariaCitation::create([
                        'sharia_record_id'  => $record->id,
                        'sharia_qa_pair_id' => $qaPair->id,
                        'citation_type'     => 'hadith',
                        'hadith_text'       => $h['text'],
                        'hadith_source'     => $h['source'] ?? 'صحيح السنة',
                        'hadith_grade'      => $h['grade'] ?? 'صحيح',
                    ]);
                    $citationsCount++;
                } catch (\Throwable $e) {}
            }
            foreach ($extracted['scholars'] as $s) {
                try {
                    ShariaCitation::create([
                        'sharia_record_id'  => $record->id,
                        'sharia_qa_pair_id' => $qaPair->id,
                        'citation_type'     => 'scholar',
                        'scholar_name'      => is_array($s) ? implode(', ', $s) : (string)$s,
                    ]);
                    $citationsCount++;
                } catch (\Throwable $e) {}
            }
        }

        return [
            'status'     => 'created',
            'record_id'  => $recordId,
            'qa_pair_id' => $qaPair->id,
            'citations'  => $citationsCount,
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
