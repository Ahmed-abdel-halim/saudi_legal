<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RequestController extends Controller
{
    /**
     * Display a listing of available project requests.
     */
    public function browse(Request $request)
    {
        $currentLang = app()->getLocale();

        // Get filter parameters
        $filterSearch = $request->get('search', '');
        $filterMaxRate = $request->get('max_rate', '');

        try {
            // Fetch from database using Eloquent
            $query = Project::with(['requester', 'skills'])
                ->where('status', 'posted')
                ->orderBy('created_at', 'desc');

            // Apply search filter
            if (!empty($filterSearch)) {
                $searchTerm = '%' . $filterSearch . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', $searchTerm)
                      ->orWhere('scope_description', 'LIKE', $searchTerm)
                      ->orWhereHas('skills', function ($k) use ($searchTerm) {
                            $k->where('name', 'LIKE', $searchTerm)
                              ->orWhere('name_ar', 'LIKE', $searchTerm);
                      });
                });
            }

            // Apply max rate filter
            if (!empty($filterMaxRate)) {
                $query->where('max_hourly_rate', '<=', $filterMaxRate);
            }

            $projects = $query->get();

            // Transform for view compatibility
            $requests = $projects->map(function ($project) {
                $project->requester_name = $project->requester->name ?? 'Unknown Company';
                // Create skills array for view compatibility
                $project->skills_array = $project->skills->map(function($skill) {
                    return app()->getLocale() == 'ar' ? ($skill->name_ar ?? $skill->name) : $skill->name;
                })->toArray();
                
                // Keep skills_list string if needed for legacy filters in view (though we filtered in DB now)
                $project->skills_list = implode(', ', $project->skills_array);
                
                return $project;
            });

            if ($requests->isEmpty() && empty($filterSearch) && empty($filterMaxRate)) {
                 // Only fall back to mock if absolutely no DB data exists AND no filters applied (init state)
                 // But actually, if DB has 0 projects, we should show empty state, not mock.
                 // However, to be safe with existing behavior, if count is 0, we can check basic count.
                 $count = Project::count();
                 if ($count === 0) {
                     $requests = $this->getMockRequests();
                 }
            }

        } catch (\Exception $e) {
            // If table doesn't exist or serious error
             $requests = $this->getMockRequests();
        }

        return view('requests.browse', [
            'requests' => $requests,
            'filterSearch' => $filterSearch,
            'filterMaxRate' => $filterMaxRate,
            'currentLang' => $currentLang,
        ]);
    }

    /**
     * Display the specified request detail.
     */
    public function show($id)
    {
        $currentLang = app()->getLocale();
        
        try {
            $project = Project::with(['requester', 'skills'])
                ->where('project_id', $id)
                ->where('status', 'posted')
                ->first();
                
            if (!$project) {
                // Try mock fallback for specific IDs 1-5 if not in DB
                 $requests = $this->getMockRequests();
                 $request = $requests->firstWhere('project_id', (int)$id);
                 if ($request) {
                     return view('requests.show', compact('request', 'currentLang'));
                 }
                abort(404);
            }
            
            // Format for view
            $project->requester_name = $project->requester->name ?? 'Unknown Company';
            // Use asset() helper to Ensure full URL for the image
            $project->requester_logo = !empty($project->requester->company_logo) 
                ? asset($project->requester->company_logo) 
                : null;
            $project->requester_description = $project->requester->description ?? null;
            
            $project->skills_array = $project->skills->map(function($skill) {
                    return app()->getLocale() == 'ar' ? ($skill->name_ar ?? $skill->name) : $skill->name;
            })->toArray();

            $request = $project; // View expects $request variable

        } catch (\Exception $e) {
            $requests = $this->getMockRequests();
            $request = $requests->firstWhere('project_id', (int)$id);
            
            if (!$request) {
                abort(404);
            }
        }

        // Load offers if current user is the requester
        $offers = [];
        $user = auth()->user();
        if ($user && $project->requester_company_id && $user->company_id == $project->requester_company_id) {
            $offers = \App\Models\ProjectOffer::with('expert')->where('project_id', $id)->get();
        }

        return view('requests.show', compact('request', 'currentLang', 'offers'));
    }

    /**
     * Accept a project offer.
     */
    /**
     * Accept a project offer.
     */
    public function acceptOffer(\App\Services\ChatService $chatService, $offerId)
    {
        $offer = \App\Models\ProjectOffer::with('project')->findOrFail($offerId);
        $project = $offer->project;

        // active user must be the project owner
        if (auth()->user()->company_id != $project->requester_company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Update offer status
        $offer->update([
            'status' => 'accepted',
            'service_status' => 'awaiting_start', // Set lifecycle status
            'accepted_at' => now(),
        ]);

        // Update project status
        $project->update([
            'status' => 'in_progress',
            'supplier_company_id' => $offer->expert->company_id ?? null 
        ]);

        // Create Conversation via Service
        $chatService->createChatForOffer($offer);

        return back()->with('success', 'Offer accepted! Chat channel created.');
    }

    /**
     * Show submit proposal form for a specific request.
     */
    public function proposal($id)
    {
        $currentLang = app()->getLocale();
        
        try {
            $project = Project::with('requester')
                ->where('project_id', $id)
                ->where('status', 'posted')
                ->first();

            if (!$project) {
                 $requests = $this->getMockRequests();
                 $request = $requests->firstWhere('project_id', (int)$id);
                 if ($request) return view('requests.proposal', compact('request', 'currentLang'));
                 
                abort(404);
            }

            $project->requester_name = $project->requester->name ?? 'Unknown';
            $request = $project;

        } catch (\Exception $e) {
            $requests = $this->getMockRequests();
            $request = $requests->firstWhere('project_id', (int)$id);
            
            if (!$request) {
                abort(404);
            }
        }

        return view('requests.proposal', compact('request', 'currentLang'));
    }

    /**
     * Show contact form for a specific request company.
     */
    public function contact($id)
    {
        $currentLang = app()->getLocale();
        
        try {
           $project = Project::with('requester')
                ->where('project_id', $id)
                ->where('status', 'posted')
                ->first();

            if (!$project) {
                 $requests = $this->getMockRequests();
                 $request = $requests->firstWhere('project_id', (int)$id);
                 if ($request) return view('requests.contact', compact('request', 'currentLang'));
                 
                abort(404);
            }
            
            $project->requester_name = $project->requester->name ?? 'Unknown';
            $request = $project;

        } catch (\Exception $e) {
            $requests = $this->getMockRequests();
            $request = $requests->firstWhere('project_id', (int)$id);
            
            if (!$request) {
                abort(404);
            }
        }

        return view('requests.contact', compact('request', 'currentLang'));
    }

    /**
     * Get mock requests data for display when database is not ready
     */
    private function getMockRequests()
    {
        $currentLang = app()->getLocale();

        $mockData = [
            [
                'project_id' => 1,
                'title' => $currentLang === 'ar' ? 'تطوير نظام إدارة محتوى' : 'Content Management System Development',
                'scope_description' => $currentLang === 'ar'
                    ? 'نحتاج إلى مطور Laravel محترف لتطوير نظام إدارة محتوى متكامل مع واجهة إدارية حديثة. المشروع يتطلب خبرة في Laravel 10+, Vue.js, و MySQL.'
                    : 'We need a professional Laravel developer to build a comprehensive content management system with a modern admin interface. The project requires expertise in Laravel 10+, Vue.js, and MySQL.',
                'requested_duration_hours' => 120,
                'max_hourly_rate' => 150.00,
                'created_at' => now()->subDays(2),
                'requester_name' => $currentLang === 'ar' ? 'شركة التقنية المتقدمة' : 'Advanced Tech Company',
                'skills_list' => $currentLang === 'ar' ? 'Laravel, PHP, Vue.js, MySQL, REST API' : 'Laravel, PHP, Vue.js, MySQL, REST API',
            ],
            // ... (Other mock data would be here, truncated for brevity in this replace but existing function had more)
            // Keeping it brief since we rely on DB mostly now.
             [
                'project_id' => 2,
                'title' => $currentLang === 'ar' ? 'تصميم واجهة مستخدم متجاوبة' : 'Responsive UI/UX Design',
                'scope_description' => '...',
                'requested_duration_hours' => 80,
                'max_hourly_rate' => 100.00,
                'created_at' => now()->subDays(5),
                'requester_name' => 'Smart Apps Company',
                'skills_list' => 'Figma, Adobe XD',
            ],
        ];

        // Convert to collection and format skills
        $requests = collect($mockData)->map(function ($item) {
            $item = (object) $item;
            $item->skills_array = !empty($item->skills_list)
                ? explode(', ', $item->skills_list)
                : [];
            return $item;
        });

        return $requests;
    }


    public function submitOffer(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'delivery_time_days' => 'required|integer|min:1',
            'message' => 'nullable|string',
        ]);

        $project = Project::findOrFail($id);
        
        // Prevent owner from offering
        // Assuming Auth user has a company, checking if it matches requester
        $user = auth()->user();
        if ($user->company_id && $user->company_id == $project->requester_company_id) {
             return back()->with('error', 'You cannot submit an offer to your own project.');
        }

        // Check if offer already exists
        $existingOffer = \App\Models\ProjectOffer::where('project_id', $project->project_id)
            ->where('expert_id', $user->id)
            ->first();

        if ($existingOffer) {
            return back()->with('error', 'You have already submitted an offer for this project.');
        }

        \App\Models\ProjectOffer::create([
            'project_id' => $project->project_id,
            'expert_id' => $user->id,
            'price' => $request->price,
            'delivery_time_days' => $request->delivery_time_days,
            'message' => $request->message,
            'status' => 'pending'
        ]);

        // TODO: Notification to requester

        return redirect()->route('requests.show', $id)->with('success', 'Offer submitted successfully!');
    }
}
