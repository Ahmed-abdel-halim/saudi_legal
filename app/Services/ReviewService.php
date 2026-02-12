<?php

namespace App\Services;

use App\Models\ProjectOffer;
use App\Models\Review;
use App\Models\ServicePurchase;
use App\Models\User;
use App\Notifications\ReviewReceivedNotification;
use Exception;

class ReviewService
{
    public function __construct(private ChatService $chatService) {}
    
    public function submitReview(
        $contractType,
        $contractId,
        $companyId,
        array $ratings,
        ?string $comment
    ): Review {
        $contract = $contractType === 'offer' 
            ? ProjectOffer::findOrFail($contractId)
            : ServicePurchase::findOrFail($contractId);
        
        // Validate
        if ($contract->service_status !== 'completed') {
            throw new Exception('Can only review completed services');
        }
        
        $conversation = $contract->conversation;
        if ($conversation->participant_1 != $companyId) {
            throw new Exception('Only the company can submit a review');
        }
        
        // Check for existing review
        if (Review::where('contract_type', $contractType)
                  ->where('contract_id', $contractId)
                  ->exists()) {
            throw new Exception('Review already exists for this contract');
        }
        
        // Create review
        $review = Review::create([
            'contract_type' => $contractType,
            'contract_id' => $contractId,
            'company_id' => $companyId,
            'expert_id' => $contract->expert_id,
            'rating' => $ratings['overall'],
            'communication_rating' => $ratings['communication'],
            'quality_rating' => $ratings['quality'],
            'delivery_time_rating' => $ratings['delivery_time'],
            'comment' => $comment,
        ]);
        
        // Update expert ratings
        $this->updateExpertRatings($contract->expert_id);
        
        // Create system message
        $this->chatService->createSystemMessage(
            $conversation,
            "Company has submitted a review for this service."
        );
        
        // Notify expert
        User::find($contract->expert_id)->notify(
            new ReviewReceivedNotification($review)
        );
        
        return $review;
    }
    
    private function updateExpertRatings($expertId): void
    {
        $expert = User::find($expertId);
        
        $avgRating = Review::where('expert_id', $expertId)->avg('rating');
        $count = Review::where('expert_id', $expertId)->count();
        
        $expert->update([
            'rating_average' => round($avgRating, 2),
            'rating_count' => $count,
        ]);
    }
    
    public function getExpertReviews($expertId, $limit = 10)
    {
        return Review::where('expert_id', $expertId)
            ->with(['company', 'contract'])
            ->latest()
            ->limit($limit)
            ->get();
    }
    
    public function getRatingDistribution($expertId): array
    {
        $reviews = Review::where('expert_id', $expertId)->get();
        
        return [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];
    }
}
