<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AiTask;
use App\Models\LegalTask;
use App\Models\LegalQaPair;
use App\Models\LegalCitation;
use App\Models\AiResponse;
use App\Models\GovernanceLog;
use Illuminate\Support\Facades\DB;

class ResetExpertLegalTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legal:reset-expert {email : The email of the expert/lawyer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and reopen completed legal tasks for an expert, pre-populating with their previous edits and preserving old earnings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = trim($this->argument('email'));

        $expert = User::where('email', $email)->first();
        if (!$expert) {
            $this->error("Expert with email '{$email}' not found.");
            return self::FAILURE;
        }

        $this->info("Found expert: {$expert->name} (ID: {$expert->id}, Role: {$expert->role})");

        if ($expert->role !== 'expert' && $expert->role !== 'freelancer') {
            $this->error("User is not an expert or freelancer.");
            return self::FAILURE;
        }

        $trustScoreBefore = $expert->trust_score;

        // Start transaction
        DB::beginTransaction();

        try {
            // 1. Unban and reset trust stats
            $expert->update([
                'is_banned' => false,
                'banned_at' => null,
                'ban_reason' => null,
                'is_active' => true,
                'is_active_for_hire' => true,
                'trust_score' => 100.00,
                'gold_tasks_completed' => 0,
                'gold_tasks_failed' => 0,
            ]);

            $this->info("Expert trust stats reset. Banned: No, Trust Score: 100.");

            // 2. Release any active assignments
            $deletedAssignments = DB::table('task_assignments')
                ->where('expert_id', $expert->id)
                ->delete();
            $this->info("Released {$deletedAssignments} active task assignments.");

            // 3. Reset any QA pairs currently marked as "Processing" by this expert
            $resetProcessing = LegalQaPair::where('reviewer_id', $expert->id)
                ->where('review_status', 'Processing')
                ->update([
                    'review_status' => 'Pending',
                    'reviewer_id' => null,
                ]);
            $this->info("Reset {$resetProcessing} QA pairs from 'Processing' to 'Pending'.");

            // 4. Fetch all completed responses
            $responses = AiResponse::where('expert_id', $expert->id)
                ->whereHas('task.legalTask')
                ->with(['task.legalTask.qaPair'])
                ->get();

            $totalResponses = $responses->count();
            $this->info("Found {$totalResponses} completed legal task responses to reopen.");

            $clonedCount = 0;
            foreach ($responses as $response) {
                $oldAiTask = $response->task;
                if (!$oldAiTask) continue;
                $oldLegalTask = $oldAiTask->legalTask;
                if (!$oldLegalTask) continue;
                $oldQaPair = $oldLegalTask->qaPair;
                if (!$oldQaPair) continue;

                // Determine previous answer (use her edits or original if she accepted)
                $expertPreviousAnswer = $response->corrected_data ?: $oldQaPair->generated_answer;

                // Clone AiTask
                $newAiTask = AiTask::create([
                    'task_type' => $oldAiTask->task_type,
                    'original_data' => $oldAiTask->original_data,
                    'ai_suggestion' => $expertPreviousAnswer,
                    'status' => 'pending',
                    'payment_status' => $oldAiTask->payment_status,
                    'is_gold_standard' => $oldAiTask->is_gold_standard,
                    'gold_answer' => $oldAiTask->gold_answer,
                    'required_responses' => $oldAiTask->required_responses,
                    'current_responses' => 0,
                    'consensus_status' => 'pending',
                    'client_id' => $oldAiTask->client_id,
                    'task_domain' => $oldAiTask->task_domain,
                    'allowed_roles' => $oldAiTask->allowed_roles,
                    'allow_all_roles' => $oldAiTask->allow_all_roles,
                ]);

                // Clone LegalQaPair
                $newQaPair = LegalQaPair::create([
                    'legal_record_id' => $oldQaPair->legal_record_id,
                    'qa_id' => $oldQaPair->qa_id,
                    'question' => $oldQaPair->question,
                    'generated_answer' => $expertPreviousAnswer, // workbench proposed answer
                    'review_status' => 'Pending',
                    'reviewer_id' => null,
                    'corrected_answer' => null,
                    'time_spent' => null,
                    'has_custom_citations' => $oldQaPair->has_custom_citations,
                ]);

                // Clone LegalTask
                LegalTask::create([
                    'task_id' => $newAiTask->id,
                    'source_type' => 'legal_qa_pair',
                    'source_id' => $newQaPair->id,
                    'task_type' => $oldLegalTask->task_type,
                    'status' => 'pending',
                    'question' => $oldLegalTask->question,
                    'proposed_answer' => $expertPreviousAnswer,
                    'correct_answer' => $oldLegalTask->correct_answer,
                    'law_system_name' => $oldLegalTask->law_system_name,
                    'law_article_number' => $oldLegalTask->law_article_number,
                    'law_article_text' => $oldLegalTask->law_article_text,
                    'case_reference' => $oldLegalTask->case_reference,
                    'case_text' => $oldLegalTask->case_text,
                    'is_correct' => null,
                    'expert_comment' => null,
                    'domain' => $oldLegalTask->domain,
                    'source_file' => $oldLegalTask->source_file,
                    'row_number' => $oldLegalTask->row_number,
                    'time_spent' => null,
                    'correct_law_system' => null,
                    'correct_law_article' => null,
                ]);

                // Clone LegalCitations
                $citations = LegalCitation::where('legal_qa_pair_id', $oldQaPair->id)->get();
                foreach ($citations as $citation) {
                    LegalCitation::create([
                        'legal_record_id' => $citation->legal_record_id,
                        'legal_qa_pair_id' => $newQaPair->id,
                        'system_name' => $citation->system_name,
                        'article_number' => $citation->article_number,
                        'citation_source' => $citation->citation_source,
                        'legal_article_id' => $citation->legal_article_id,
                        'added_by_expert' => $citation->added_by_expert,
                    ]);
                }

                $clonedCount++;
            }

            // 5. Governance Log
            GovernanceLog::create([
                'expert_id' => $expert->id,
                'event_type' => 'expert_unbanned',
                'event_data' => [
                    'cloned_tasks_count' => $clonedCount,
                    'reason' => 'Admin reset and reopen legal tasks for second audit run.'
                ],
                'trust_score_before' => $trustScoreBefore,
                'trust_score_after' => 100.00,
            ]);

            DB::commit();

            $this->info("Successfully cloned and reopened {$clonedCount} tasks for {$expert->email}.");
            return self::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
