<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $experts = DB::table('users')->where('role', 'expert')->get();

        foreach ($experts as $expert) {
            // Count completed (passed) gold standard tasks
            // They passed if they edited/corrected the trap question
            $completed = DB::table('ai_responses_v2')
                ->join('ai_tasks_v2', 'ai_responses_v2.task_id', '=', 'ai_tasks_v2.id')
                ->where('ai_responses_v2.expert_id', $expert->id)
                ->where('ai_tasks_v2.is_gold_standard', 1)
                ->where('ai_responses_v2.action', 'edited')
                ->count();

            // Count failed gold standard tasks
            // They failed if they accepted/approved the trap question
            $failed = DB::table('ai_responses_v2')
                ->join('ai_tasks_v2', 'ai_responses_v2.task_id', '=', 'ai_tasks_v2.id')
                ->where('ai_responses_v2.expert_id', $expert->id)
                ->where('ai_tasks_v2.is_gold_standard', 1)
                ->where('ai_responses_v2.action', 'accepted')
                ->count();

            // Recalculate trust score
            $newScore = 100.00 - ($failed * 10);

            // Determine ban status
            $isBanned = $newScore < 60;
            $isActive = $expert->is_active;
            $isActiveForHire = $expert->is_active_for_hire;
            $banReason = $expert->ban_reason;

            if ($isBanned) {
                $isActive = 0;
                $isActiveForHire = 0;
                if (!$expert->is_banned) {
                    $banReason = 'انخفض مؤشر الثقة عن 60 بسبب الفشل في أسئلة الاختبار بعد إعادة الحساب.';
                }
            } else {
                // If they are no longer banned due to recalculated trust score, restore them
                if ($expert->is_banned && (empty($expert->ban_reason) || 
                    str_contains($expert->ban_reason, 'الثقة') || 
                    str_contains($expert->ban_reason, 'Trust score'))) {
                    $isBanned = 0;
                    $isActive = 1;
                    $isActiveForHire = 1;
                    $banReason = null;
                }
            }

            // Update user record
            DB::table('users')->where('id', $expert->id)->update([
                'gold_tasks_completed' => $completed,
                'gold_tasks_failed'    => $failed,
                'trust_score'          => $newScore,
                'is_banned'            => $isBanned,
                'is_active'            => $isActive,
                'is_active_for_hire'   => $isActiveForHire,
                'ban_reason'           => $banReason,
                'updated_at'           => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed for rollback as it is a data correction migration
    }
};
