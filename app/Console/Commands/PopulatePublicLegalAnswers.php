<?php

namespace App\Console\Commands;

use App\Models\AiConversation;
use App\Models\LegalQaPair;
use App\Models\LegalTask;
use App\Models\PublicLegalAnswer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PopulatePublicLegalAnswers extends Command
{
    protected $signature = 'seo:populate-answers
                            {--source=all : مصدر البيانات (all, tasks, qa_pairs, ai_chats)}
                            {--limit=50000 : أقصى عدد للسجلات المراد تحويلها}';

    protected $description = 'تحويل الاستفسارات والأسئلة القانونية المخزنة إلى صفحات الـ SEO العامة باللغتين عربي وإنجليزي باستخدام Chunking لمنع استهلاك الذاكرة';

    public function handle(): int
    {
        // رفع الـ memory limit ليعالج البيانات الضخمة دون توقف
        ini_set('memory_limit', '1024M');

        $source = $this->option('source');
        $limit  = (int) $this->option('limit');

        $this->info("🚀 بدء استيراد الأسئلة القانونية إلى صفحات الـ SEO العامة (Source: {$source}, Limit: {$limit}) ...");

        $importedCount = 0;

        if (in_array($source, ['all', 'tasks'])) {
            $importedCount += $this->populateFromLegalTasks($limit - $importedCount);
        }

        if (in_array($source, ['all', 'qa_pairs']) && $importedCount < $limit) {
            $importedCount += $this->populateFromLegalQaPairs($limit - $importedCount);
        }

        if (in_array($source, ['all', 'ai_chats']) && $importedCount < $limit) {
            $importedCount += $this->populateFromAiChats($limit - $importedCount);
        }

        $this->info("🎉 تم الانتهاء! تم إضافة {$importedCount} سؤال قانوني جديد إلى جدول الصفحات العامة.");
        $this->line("👉 شغّل الآن: php artisan sitemap:legal-qa لتوليد خرائط الـ Sitemaps للروابط الجديدة.");

        return self::SUCCESS;
    }

    private function populateFromLegalTasks(int $remainingLimit): int
    {
        if ($remainingLimit <= 0) return 0;

        $count = 0;
        $this->info("📋 جاري فحص سجلات LegalTask...");

        LegalTask::whereNotNull('question')
            ->where(function ($q) {
                $q->whereNotNull('correct_answer')
                  ->orWhereNotNull('proposed_answer');
            })
            ->chunkById(500, function ($tasks) use (&$count, $remainingLimit) {
                foreach ($tasks as $task) {
                    if ($count >= $remainingLimit) return false;

                    $question = trim($task->question);
                    $answer   = trim($task->correct_answer ?: $task->proposed_answer);

                    if (mb_strlen($question) < 5 || mb_strlen($answer) < 10) continue;

                    // ────── النسخة العربية ──────
                    $slugAr = Str::slug(mb_substr($question, 0, 80), '-', 'ar') . '-task-' . $task->id;
                    // Str::slug للعربية قد يُنتج سلسلة فارغة — نحافظ على الحروف العربية
                    if (empty(trim($slugAr, '-'))) {
                        $slugAr = 'legal-ar-task-' . $task->id;
                    }

                    // ────── النسخة الإنجليزية ──────
                    $slugEn = 'legal-en-task-' . $task->id;

                    $citations = [];
                    if ($task->law_system_name || $task->law_article_number) {
                        $citations[] = [
                            'law_system'     => $task->law_system_name ?: ($task->correct_law_system ?: 'Saudi Law'),
                            'article_number' => $task->law_article_number ?: ($task->correct_law_article ?: ''),
                            'text'           => $task->law_article_text ?: '',
                        ];
                    }

                    // إدراج النسخة العربية
                    if (!PublicLegalAnswer::where('slug', $slugAr)->exists()) {
                        PublicLegalAnswer::create([
                            'locale'           => 'ar',
                            'slug'             => $slugAr,
                            'question'         => $question,
                            'answer'           => $answer,
                            'citations'        => count($citations) > 0 ? $citations : null,
                            'source_type'      => 'legal_task',
                            'source_id'        => $task->id,
                            'counterpart_slug' => $slugEn,
                        ]);
                        $count++;
                    }

                    // إدراج النسخة الإنجليزية (السؤال والجواب بالعربي مؤقتاً — يُستبدل بالترجمة لاحقاً)
                    if (!PublicLegalAnswer::where('slug', $slugEn)->exists()) {
                        PublicLegalAnswer::create([
                            'locale'           => 'en',
                            'slug'             => $slugEn,
                            'question'         => $question,   // الترجمة تُضاف لاحقاً
                            'answer'           => $answer,
                            'citations'        => count($citations) > 0 ? $citations : null,
                            'source_type'      => 'legal_task',
                            'source_id'        => $task->id,
                            'counterpart_slug' => $slugAr,
                        ]);
                        $count++;
                    }
                }

                $this->line("  ... تم معالجة دفعة (الإجمالي الحالي: {$count})");
            });

        $this->line("  ✅ تم استيراد {$count} سجل (عربي + إنجليزي) من LegalTask.");
        return $count;
    }

    private function populateFromLegalQaPairs(int $remainingLimit): int
    {
        if ($remainingLimit <= 0) return 0;

        $count = 0;
        $this->info("📋 جاري فحص سجلات LegalQaPair...");

        LegalQaPair::with('citations')
            ->whereNotNull('question')
            ->chunkById(500, function ($pairs) use (&$count, $remainingLimit) {
                foreach ($pairs as $pair) {
                    if ($count >= $remainingLimit) return false;

                    $question = trim($pair->question);
                    $answer   = trim($pair->final_answer);

                    if (mb_strlen($question) < 5 || mb_strlen($answer) < 10) continue;

                    $slugAr = 'legal-ar-qa-' . $pair->id;
                    $slugEn = 'legal-en-qa-' . $pair->id;

                    $citations = [];
                    foreach ($pair->citations as $cit) {
                        $citations[] = [
                            'law_system'     => $cit->law_system_name ?? 'Saudi Law',
                            'article_number' => $cit->law_article_number ?? '',
                            'text'           => $cit->law_article_text ?? '',
                        ];
                    }

                    if (!PublicLegalAnswer::where('slug', $slugAr)->exists()) {
                        PublicLegalAnswer::create([
                            'locale'           => 'ar',
                            'slug'             => $slugAr,
                            'question'         => $question,
                            'answer'           => $answer,
                            'citations'        => count($citations) > 0 ? $citations : null,
                            'source_type'      => 'legal_qa_pair',
                            'source_id'        => $pair->id,
                            'counterpart_slug' => $slugEn,
                        ]);
                        $count++;
                    }

                    if (!PublicLegalAnswer::where('slug', $slugEn)->exists()) {
                        PublicLegalAnswer::create([
                            'locale'           => 'en',
                            'slug'             => $slugEn,
                            'question'         => $question,
                            'answer'           => $answer,
                            'citations'        => count($citations) > 0 ? $citations : null,
                            'source_type'      => 'legal_qa_pair',
                            'source_id'        => $pair->id,
                            'counterpart_slug' => $slugAr,
                        ]);
                        $count++;
                    }
                }

                $this->line("  ... تم معالجة دفعة (الإجمالي الحالي: {$count})");
            });

        $this->line("  ✅ تم استيراد {$count} سجل (عربي + إنجليزي) من LegalQaPair.");
        return $count;
    }

    private function populateFromAiChats(int $remainingLimit): int
    {
        if ($remainingLimit <= 0) return 0;

        $count = 0;
        $this->info("📋 جاري فحص سجلات AiConversation...");

        AiConversation::with('messages')
            ->chunkById(200, function ($conversations) use (&$count, $remainingLimit) {
                foreach ($conversations as $c) {
                    if ($count >= $remainingLimit) return false;

                    $userMsg = $c->messages->firstWhere('role', 'user');
                    $botMsg  = $c->messages->first(fn($m) => in_array($m->role, ['assistant', 'model']));

                    if (!$userMsg || !$botMsg) continue;

                    $question = trim($userMsg->message);
                    $answer   = trim($botMsg->message);

                    if (mb_strlen($question) < 5 || mb_strlen($answer) < 10) continue;

                    $slugAr = 'legal-ar-chat-' . $c->id;
                    $slugEn = 'legal-en-chat-' . $c->id;

                    if (!PublicLegalAnswer::where('slug', $slugAr)->exists()) {
                        PublicLegalAnswer::create([
                            'locale'           => 'ar',
                            'slug'             => $slugAr,
                            'question'         => $question,
                            'answer'           => $answer,
                            'citations'        => $botMsg->citations ?? null,
                            'source_type'      => 'ai_chat',
                            'source_id'        => $c->id,
                            'counterpart_slug' => $slugEn,
                        ]);
                        $count++;
                    }

                    if (!PublicLegalAnswer::where('slug', $slugEn)->exists()) {
                        PublicLegalAnswer::create([
                            'locale'           => 'en',
                            'slug'             => $slugEn,
                            'question'         => $question,
                            'answer'           => $answer,
                            'citations'        => $botMsg->citations ?? null,
                            'source_type'      => 'ai_chat',
                            'source_id'        => $c->id,
                            'counterpart_slug' => $slugAr,
                        ]);
                        $count++;
                    }
                }
            });

        $this->line("  ✅ تم استيراد {$count} سجل (عربي + إنجليزي) من AiConversation.");
        return $count;
    }
}
