<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->route('dashboard.settings')
                ->with('error', __('dashboard.error_no_company')); // Ensure this key exists or use string
        }

        return view('dashboard.projects.create', compact('user', 'company'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('dashboard')->with('error', 'Company profile required.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'scope_description' => 'required|string',
            'requested_duration_hours' => 'required|integer|min:1',
            'max_hourly_rate' => 'required|numeric|min:0',
            'budget' => 'nullable|numeric|min:0', // Optional overall budget
            'skills' => 'nullable|string', // Comma separated tags
        ]);

        DB::beginTransaction();

        try {
            // Create Project
            $project = Project::create([
                'title' => $validated['title'],
                'scope_description' => $validated['scope_description'],
                'requested_duration_hours' => $validated['requested_duration_hours'],
                'max_hourly_rate' => $validated['max_hourly_rate'],
                'budget' => $validated['budget'] ?? ($validated['requested_duration_hours'] * $validated['max_hourly_rate']),
                'requester_company_id' => $company->company_id,
                'status' => 'posted', // Using posted status
            ]);

            // Handle Skills
            if (!empty($validated['skills'])) {
                $skillNames = explode(',', $validated['skills']);
                $skillIds = [];

                foreach ($skillNames as $name) {
                    $name = trim($name);
                    if (empty($name)) continue;

                    // Find or create skill
                    // Case-insensitive check would be better, but for now simple check
                    $skill = Skill::firstOrCreate(
                        ['name' => $name],
                        ['name_ar' => $name] // Fallback
                    );
                    $skillIds[] = $skill->skill_id;
                }

                $project->skills()->sync($skillIds);
            }

            DB::commit();

            return redirect()->route('dashboard.projects')
                ->with('success', __('dashboard.project_created_success') ?? 'Project created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating project: ' . $e->getMessage());
        }
    }
}
