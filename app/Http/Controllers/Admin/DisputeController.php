<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ProjectOffer;
use App\Models\ServicePurchase;
use App\Services\ChatService;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DisputeController extends Controller
{
    public function __construct(
        private ChatService $chatService,
        private ContractService $contractService
    ) {}

    /**
     * Display all disputed contracts
     */
    public function index()
    {
        Gate::authorize('resolveDisputes', auth()->user());

        $disputes = collect();

        // Get disputed offers
        $disputedOffers = ProjectOffer::where('service_status', 'disputed')
            ->with(['project', 'expert', 'conversation'])
            ->get()
            ->map(fn($offer) => [
                'id' => $offer->id,
                'type' => 'offer',
                'title' => $offer->project->title,
                'expert' => $offer->expert->name,
                'company' => $offer->project->requester->name ?? 'Unknown',
                'created_at' => $offer->created_at,
                'conversation_id' => $offer->conversation->id ?? null,
            ]);

        // Get disputed purchases
        $disputedPurchases = ServicePurchase::where('service_status', 'disputed')
            ->with(['expert', 'client', 'conversation'])
            ->get()
            ->map(fn($purchase) => [
                'id' => $purchase->id,
                'type' => 'hourly_purchase',
                'title' => "Hourly Service - {$purchase->hours_purchased} hours",
                'expert' => $purchase->expert->name,
                'company' => $purchase->client->name,
                'created_at' => $purchase->created_at,
                'conversation_id' => $purchase->conversation->id ?? null,
            ]);

        $disputes = $disputedOffers->merge($disputedPurchases)
            ->sortByDesc('created_at');

        return view('admin.disputes.index', compact('disputes'));
    }

    /**
     * Show dispute details
     */
    public function show($type, $id)
    {
        Gate::authorize('resolveDisputes', auth()->user());

        $contract = $type === 'offer' 
            ? ProjectOffer::with(['project', 'expert', 'conversation.messages'])->findOrFail($id)
            : ServicePurchase::with(['expert', 'client', 'conversation.messages'])->findOrFail($id);

        return view('admin.disputes.show', compact('contract', 'type'));
    }

    /**
     * Resolve dispute in favor of company (refund)
     */
    public function resolveForCompany(Request $request, $type, $id)
    {
        Gate::authorize('resolveDisputes', auth()->user());

        $request->validate([
            'resolution_note' => 'required|string|max:1000'
        ]);

        $contract = $type === 'offer' 
            ? ProjectOffer::findOrFail($id)
            : ServicePurchase::findOrFail($id);

        // Update contract status
        $contract->update([
            'service_status' => 'cancelled',
            'resolution_note' => $request->resolution_note,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        // Close conversation
        if ($contract->conversation) {
            $contract->conversation->update(['status' => Conversation::STATUS_CLOSED]);

            // Send system message
            $this->chatService->createSystemMessage(
                $contract->conversation,
                "Dispute resolved by admin in favor of company. Resolution: {$request->resolution_note}"
            );
        }

        return redirect()->route('admin.disputes.index')
            ->with('success', 'Dispute resolved in favor of company. Refund processed.');
    }

    /**
     * Resolve dispute in favor of expert (payment released)
     */
    public function resolveForExpert(Request $request, $type, $id)
    {
        Gate::authorize('resolveDisputes', auth()->user());

        $request->validate([
            'resolution_note' => 'required|string|max:1000'
        ]);

        $contract = $type === 'offer' 
            ? ProjectOffer::findOrFail($id)
            : ServicePurchase::findOrFail($id);

        // Update contract status
        $contract->update([
            'service_status' => 'completed',
            'completed_at' => now(),
            'resolution_note' => $request->resolution_note,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        // Close conversation
        if ($contract->conversation) {
            $contract->conversation->update(['status' => Conversation::STATUS_CLOSED]);

            // Send system message
            $this->chatService->createSystemMessage(
                $contract->conversation,
                "Dispute resolved by admin in favor of expert. Payment released. Resolution: {$request->resolution_note}"
            );
        }

        // Update expert metrics
        $expert = $contract->expert;
        $expert->increment('completed_contracts');
        
        return redirect()->route('admin.disputes.index')
            ->with('success', 'Dispute resolved in favor of expert. Payment released.');
    }

    /**
     * Send admin message to dispute conversation
     */
    public function sendMessage(Request $request, $type, $id)
    {
        Gate::authorize('sendSystemMessages', auth()->user());

        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $contract = $type === 'offer' 
            ? ProjectOffer::findOrFail($id)
            : ServicePurchase::findOrFail($id);

        if ($contract->conversation) {
            $this->chatService->createSystemMessage(
                $contract->conversation,
                "[Admin] " . $request->message
            );
        }

        return back()->with('success', 'Message sent to conversation.');
    }
}
