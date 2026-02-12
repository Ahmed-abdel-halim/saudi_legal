<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}
    
    public function store(Request $request, $type, $id)
    {
        $request->validate([
            'overall' => 'required|integer|min:1|max:5',
            'communication' => 'required|integer|min:1|max:5',
            'quality' => 'required|integer|min:1|max:5',
            'delivery_time' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $review = $this->reviewService->submitReview(
            $type,
            $id,
            Auth::id(),
            [
                'overall' => $request->overall,
                'communication' => $request->communication,
                'quality' => $request->quality,
                'delivery_time' => $request->delivery_time,
            ],
            $request->comment
        );
        
        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review,
        ]);
    }

    public function getExpertReviews($id)
    {
        $reviews = $this->reviewService->getExpertReviews($id);
        return response()->json(['reviews' => $reviews]);
    }

    public function getRatingDistribution($id)
    {
        $distribution = $this->reviewService->getRatingDistribution($id);
        return response()->json(['distribution' => $distribution]);
    }
}
