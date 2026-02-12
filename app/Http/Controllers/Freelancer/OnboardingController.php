<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\Skill;
use Illuminate\Http\Request; // Import correct Request class
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function showSkills()
    {
        $skills = Skill::all();
        return view('freelancer.onboarding.skills', compact('skills'));
    }

    public function storeSkills(Request $request)
    {
        // Decode JSON input
        $skillsJson = $request->input('skills_json');
        
        // Handle empty initial load or parsing error
        if (empty($skillsJson)) {
             return redirect()->back()->withErrors(['skills_json' => 'الرجاء اختيار مهارة واحدة على الأقل.']);
        }

        $skillsData = json_decode($skillsJson, true);

        if (empty($skillsData) || !is_array($skillsData)) {
            return redirect()->back()->withErrors(['skills_json' => 'الرجاء اختيار مهارة واحدة على الأقل.']);
        }

        $user = Auth::user();
        
        // Ensure profile exists
        $profile = FreelancerProfile::firstOrCreate(['user_id' => $user->id]);

        $skillIds = [];

        foreach ($skillsData as $item) {
            // Tagify sends [{"value":"Skill Name"}]
            // Fallback for simple array ["Skill Name"] just in case
            $skillNameRaw = is_array($item) ? ($item['value'] ?? '') : $item;
            $skillName = trim($skillNameRaw);

            if (empty($skillName)) continue;

            // Check if skill exists (case-insensitive)
            // We check both name and name_ar just in case
            $skill = Skill::where('name', $skillName)
                          ->orWhere('name_ar', $skillName)
                          ->first();

            if (!$skill) {
                // Create new skill
                $skill = Skill::create(['name' => $skillName]);
            }

            $skillIds[] = $skill->skill_id;
        }

        // Sync skills to profile
        $profile->skills()->sync($skillIds);

        return redirect()->route('freelancer.dashboard');
    }
}
