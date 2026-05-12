<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalQaPair;
use App\Models\User;
use Illuminate\Http\Request;

class LegalManagementController extends Controller
{
    /**
     * Display a listing of all legal QA pairs for monitoring.
     */
    public function index(Request $request)
    {
        $query = LegalQaPair::with(['record', 'reviewer'])
            ->when($request->status, function ($q) use ($request) {
                return $q->where('review_status', $request->status);
            })
            ->when($request->expert_id, function ($q) use ($request) {
                return $q->where('reviewer_id', $request->expert_id);
            })
            ->when($request->search, function ($q) use ($request) {
                return $q->where('question', 'LIKE', '%' . $request->search . '%')
                         ->orWhere('id', $request->search);
            });

        $stats = [
            'total'      => LegalQaPair::count(),
            'pending'    => LegalQaPair::where('review_status', 'Pending')->count(),
            'processing' => LegalQaPair::where('review_status', 'Processing')->count(),
            'completed'  => LegalQaPair::whereIn('review_status', ['Approved', 'Modified', 'Rejected'])->count(),
        ];

        $items = $query->orderBy('updated_at', 'desc')->paginate(20);
        
        $experts = User::where('role', 'expert')->get();

        return view('admin.legal.index', compact('items', 'stats', 'experts'));
    }

    /**
     * Display the details of a specific legal review.
     */
    public function show($id)
    {
        $item = LegalQaPair::with(['record.citations.article', 'reviewer'])->findOrFail($id);
        return view('admin.legal.show', compact('item'));
    }
}
