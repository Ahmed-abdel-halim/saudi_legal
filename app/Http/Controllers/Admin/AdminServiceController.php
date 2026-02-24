<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertService;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    /**
     * Display a listing of all services (Services Board)
     */
    public function index(Request $request)
    {
        $query = ExpertService::with('expert')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('expert', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $services = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total' => ExpertService::count(),
            'active' => ExpertService::where('is_active', true)->count(),
            'inactive' => ExpertService::where('is_active', false)->count(),
            'avg_price' => ExpertService::avg('price') ?? 0,
        ];

        return view('admin.services.index', compact('services', 'stats'));
    }

    /**
     * Toggle the active status of a service.
     */
    public function toggleStatus($id)
    {
        $service = ExpertService::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        return back()->with('success', 'Service status updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy($id)
    {
        $service = ExpertService::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service deleted successfully.');
    }
}
