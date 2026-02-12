<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ProjectOffer;
use App\Models\ServicePurchase;
use App\Models\User;
use App\Notifications\DisputeOpenedNotification;
use App\Notifications\ServiceCompletedNotification;
use App\Notifications\ServiceFinishedNotification;
use App\Notifications\ServiceStartedNotification;
use Exception;

class ContractService
{
    public function __construct(private ChatService $chatService) {}
    
    /**
     * Expert starts working on the service
     */
    public function startService($contractType, $contractId, $expertId): void
    {
        $contract = $this->getContract($contractType, $contractId);
        
        // Validate
        if ($contract->expert_id != $expertId) {
            throw new Exception('Only the assigned expert can start the service');
        }
        
        if ($contract->service_status != 'awaiting_start') {
            throw new Exception('Service cannot be started in current status');
        }
        
        // Update status
        $contract->update([
            'service_status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        // Create system message
        $conversation = $contract->conversation;
        if ($conversation) {
            $this->chatService->createSystemMessage(
                $conversation,
                "Expert has started working on the service."
            );
            
            // Notify company
            $companyId = $conversation->participant_1;
            $company = User::find($companyId);
            if ($company) {
                $company->notify(new ServiceStartedNotification($contract));
            }
        }
    }
    
    /**
     * Expert marks service as finished
     */
    public function finishService($contractType, $contractId, $expertId): void
    {
        $contract = $this->getContract($contractType, $contractId);
        
        if ($contract->expert_id != $expertId) {
            throw new Exception('Only the assigned expert can finish the service');
        }
        
        if ($contract->service_status != 'in_progress') {
            throw new Exception('Service must be in progress to finish');
        }
        
        $contract->update([
            'service_status' => 'awaiting_confirmation',
            'finished_at' => now(),
        ]);
        
        $conversation = $contract->conversation;
        if ($conversation) {
            $this->chatService->createSystemMessage(
                $conversation,
                "Expert marked the service as completed and is awaiting your confirmation."
            );
            
            // Notify company
            $companyId = $conversation->participant_1;
            User::find($companyId)->notify(
                new ServiceFinishedNotification($contract)
            );
        }
    }
    
    /**
     * Company confirms delivery
     */
    public function confirmDelivery($contractType, $contractId, $companyId): void
    {
        $contract = $this->getContract($contractType, $contractId);
        $conversation = $contract->conversation;
        
        if ($conversation->participant_1 != $companyId) {
            throw new Exception('Only the company can confirm delivery');
        }
        
        if ($contract->service_status != 'awaiting_confirmation') {
            throw new Exception('Service must be awaiting confirmation');
        }
        
        // Complete the contract
        $contract->update([
            'service_status' => 'completed',
            'completed_at' => now(),
        ]);
        
        // Close conversation
        $conversation->update(['status' => Conversation::STATUS_CLOSED]);
        
        // Update expert metrics
        $this->updateExpertMetrics($contract->expert_id, 'completed');
        
        $this->chatService->createSystemMessage(
            $conversation,
            "Company confirmed successful delivery. Contract completed."
        );
        
        // Notify expert
        User::find($contract->expert_id)->notify(
            new ServiceCompletedNotification($contract)
        );
    }
    
    /**
     * Cancel service (rules apply based on status)
     */
    public function cancelService($contractType, $contractId, $userId, $reason = null): void
    {
        $contract = $this->getContract($contractType, $contractId);
        $conversation = $contract->conversation;
        
        // If work started, convert to dispute
        if (in_array($contract->service_status, ['in_progress', 'awaiting_confirmation'])) {
            $this->openDispute($contractType, $contractId, $userId, $reason ?? 'Cancellation requested after work started');
            return;
        }
        
        // Before work starts - allow cancellation
        $contract->update(['service_status' => 'cancelled']);
        
        if ($conversation) {
            $conversation->update(['status' => Conversation::STATUS_CLOSED]); // Close chat too? Or keep it open? Usually cancel closes chat.
            
            $this->chatService->createSystemMessage(
                $conversation,
                "Service cancelled: " . ($reason ?? 'No reason provided')
            );
        }
        
        // Update expert metrics if expert cancelled
        if ($contract->expert_id == $userId) {
            $this->updateExpertMetrics($contract->expert_id, 'cancelled');
        }
    }
    
    /**
     * Open dispute
     */
    public function openDispute($contractType, $contractId, $userId, $reason): void
    {
        $contract = $this->getContract($contractType, $contractId);
        $conversation = $contract->conversation;
        
        // Validate user is participant
        if (!$conversation->isParticipant($userId)) {
            throw new Exception('Only participants can open disputes');
        }
        
        $contract->update(['service_status' => 'disputed']);
        $conversation->update(['status' => Conversation::STATUS_ACTIVE]); // Lock status? ACTIVE is default. Maybe add IS_SUSPENDED? For now keeping active for communication.
        
        $this->chatService->createSystemMessage(
            $conversation,
            "A dispute has been opened. Platform support has been notified.\nReason: {$reason}"
        );
        
        // Notify admins
        $this->notifyAdmins($contract, $reason);
        
        // Update expert metrics
        $this->updateExpertMetrics($contract->expert_id, 'disputed');
    }
    
    private function getContract($type, $id)
    {
        return $type === 'offer' 
            ? ProjectOffer::findOrFail($id)
            : ServicePurchase::findOrFail($id);
    }
    
    private function updateExpertMetrics($expertId, $action): void
    {
        $expert = User::find($expertId);
        
        switch ($action) {
            case 'completed':
                $expert->increment('completed_contracts');
                break;
            case 'cancelled':
                $expert->increment('cancelled_contracts');
                break;
            case 'disputed':
                $expert->increment('disputed_contracts');
                break;
        }
        
        // Recalculate completion rate
        $total = $expert->completed_contracts + $expert->cancelled_contracts + $expert->disputed_contracts;
        if ($total > 0) {
            $expert->completion_rate = ($expert->completed_contracts / $total) * 100;
            $expert->save();
        }
    }
    
    private function notifyAdmins($contract, $reason): void
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new DisputeOpenedNotification($contract, $reason));
        }
    }
}
