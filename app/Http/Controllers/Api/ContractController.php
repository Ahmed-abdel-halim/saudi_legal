<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function __construct(private ContractService $contractService) {}
    
    public function start(Request $request, $type, $id)
    {
        $this->contractService->startService($type, $id, Auth::id());
        return response()->json(['message' => 'Service started successfully']);
    }
    
    public function finish(Request $request, $type, $id)
    {
        $this->contractService->finishService($type, $id, Auth::id());
        return response()->json(['message' => 'Service marked as finished']);
    }
    
    public function confirm(Request $request, $type, $id)
    {
        $this->contractService->confirmDelivery($type, $id, Auth::id());
        return response()->json(['message' => 'Delivery confirmed']);
    }
    
    public function cancel(Request $request, $type, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $this->contractService->cancelService($type, $id, Auth::id(), $request->reason);
        return response()->json(['message' => 'Service cancelled']);
    }
    
    public function dispute(Request $request, $type, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $this->contractService->openDispute($type, $id, Auth::id(), $request->reason);
        return response()->json(['message' => 'Dispute opened']);
    }
}
