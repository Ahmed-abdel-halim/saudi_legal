<?php

namespace App\Jobs;

use App\Services\AzureSearchService;
use App\Models\LegalTask;
use App\Models\LegalQaPair;
use App\Models\LegalArticle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;

/**
 * Job لفهرسة وثيقة واحدة بشكل غير متزامن (Queue)
 * ─────────────────────────────────────────────────────────────────────────────
 * يُستخدم عند إضافة أو تعديل وثيقة قانونية لتحديث الـ index فوراً
 *
 * الاستخدام:
 *   IndexDocumentToAzure::dispatch('task', $task->id);
 *   IndexDocumentToAzure::dispatch('qa_pair', $qa->id);
 *   IndexDocumentToAzure::dispatch('article', $article->id);
 * ─────────────────────────────────────────────────────────────────────────────
 */
class IndexDocumentToAzure implements ShouldQueue
{
    use Queueable, Batchable;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30; // ثانية بين كل محاولة

    public function __construct(
        protected string $type,  // 'task' | 'qa_pair' | 'article'
        protected int    $modelId
    ) {}

    public function handle(AzureSearchService $azure): void
    {
        if (! config('azure.search.enabled', false)) {
            return; // لا تفعل شيئاً إذا كان Azure غير مفعّل
        }

        $doc = $this->buildDocument();

        if (! $doc) {
            Log::warning("[AzureIndex] Model not found: {$this->type}#{$this->modelId}");
            return;
        }

        $success = $azure->indexDocument($doc);

        if ($success) {
            Log::info("[AzureIndex] ✅ Indexed {$this->type}#{$this->modelId}");
        } else {
            Log::error("[AzureIndex] ❌ Failed to index {$this->type}#{$this->modelId}");
            $this->fail("Azure indexing failed for {$this->type}#{$this->modelId}");
        }
    }

    private function buildDocument(): ?array
    {
        return match ($this->type) {
            'task'     => $this->buildFromTask(),
            'qa_pair'  => $this->buildFromQaPair(),
            'article'  => $this->buildFromArticle(),
            default    => null,
        };
    }

    private function buildFromTask(): ?array
    {
        $task = LegalTask::find($this->modelId);
        if (! $task || empty($task->correct_answer)) return null;

        return [
            'id'             => 'task_' . $task->id,
            'question'       => $task->question ?? '',
            'answer'         => $task->correct_answer ?? '',
            'case_text'      => $task->case_text ?? '',
            'domain'         => $task->domain ?? 'general',
            'source_type'    => 'judgment',
            'law_system'     => $task->law_system_name ?? '',
            'case_reference' => $task->case_reference ?? '',
        ];
    }

    private function buildFromQaPair(): ?array
    {
        $qa = LegalQaPair::with('record:id,domain,sub_domain,source_reference')
            ->find($this->modelId);

        if (! $qa || $qa->review_status !== 'Approved') return null;

        return [
            'id'             => 'qa_' . $qa->id,
            'question'       => $qa->question ?? '',
            'answer'         => $qa->final_answer ?? '',
            'case_text'      => $qa->final_answer ?? '',
            'domain'         => $qa->record?->domain ?? 'legal',
            'source_type'    => 'qa_pair',
            'law_system'     => $qa->record?->sub_domain ?? '',
            'case_reference' => $qa->record?->source_reference ?? '',
        ];
    }

    private function buildFromArticle(): ?array
    {
        $article = LegalArticle::find($this->modelId);
        if (! $article) return null;

        return [
            'id'             => 'article_' . $article->id,
            'question'       => $article->article_title ?? '',
            'answer'         => $article->content ?? '',
            'case_text'      => $article->content ?? '',
            'domain'         => 'law_article',
            'source_type'    => 'article',
            'law_system'     => $article->legislation_title ?? '',
            'case_reference' => 'مادة رقم ' . ($article->article_number ?? ''),
        ];
    }
}
