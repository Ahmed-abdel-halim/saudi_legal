<?php

namespace App\Observers;

use App\Jobs\IndexDocumentToAzure;
use App\Models\LegalQaPair;

/**
 * مراقب LegalQaPair
 * يُفهرس الوثيقة في Azure AI Search تلقائياً عند الموافقة عليها
 */
class LegalQaPairObserver
{
    /**
     * عند تحديث حالة QA Pair → إذا وافق الخبير، ارفع للـ index
     */
    public function updated(LegalQaPair $qa): void
    {
        // فهرسة فقط عند تغيير حالة المراجعة
        if (! $qa->wasChanged('review_status')) {
            return;
        }

        if ($qa->review_status === 'Approved' || $qa->review_status === 'Modified') {
            IndexDocumentToAzure::dispatch('qa_pair', $qa->id)
                ->onQueue('azure-indexing');
        }
    }

    /**
     * عند الحذف، احذف من الـ index أيضاً (مستقبلاً)
     */
    public function deleted(LegalQaPair $qa): void
    {
        // TODO: dispatch DeleteDocumentFromAzure job
    }
}
