<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ensure user has a company, although the middleware/flow should likely guarantee this or handle it.
        // For now, consistent with legacy, we assume they might not if they are just a user, but legacy 
        // code did a left join. If no company, we might show a "Create Company" view or empty state.
        // The legacy code redirects if not logged in.

        $company = $user->company;

        if (!$company) {
        // Fallback or redirect to company creation if that's the flow
        // For this task, we'll assume the company exists as per the legacy "Business" dashboard context
        // or pass null and handle in view.
        }

        $companyId = $user->company_id;

        // Statistics
        // 1. Team Count (Employees/Experts)
        // 1. Team Count (All Employees/Experts + Admin)
        $teamCount = User::where('company_id', $companyId)
            ->count();

        // 2. Services Count
        // Using DB facade if ExpertService model doesn't exist yet, or to keep it simple as per legacy query
        $servicesCount = DB::table('expert_services')
            ->whereIn('expert_id', function ($query) use ($companyId) {
            $query->select('id')
                ->from('users')
                ->where('company_id', $companyId);
        })
            ->count();

        return view('dashboard.index', compact('user', 'company', 'teamCount', 'servicesCount'));
    }

    public function settings()
    {
        $user = Auth::user();
        // If no company, pass an empty Company model instance to the view
        $company = $user->company ?? new \App\Models\Company();

        return view('dashboard.settings', [
            'user' => $user,
            'company' => $company
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // If no company exists, create a new one
        if (!$company) {
            $company = new \App\Models\Company();
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'cr_number' => 'required|string|max:255',
            'industry' => 'required|string',
            'size' => 'required|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $company->name = $validated['company_name'];
        $company->cr_number = $validated['cr_number'];
        $company->industry = $validated['industry'];
        $company->size = $validated['size'];
        $company->is_requester = $request->has('is_requester');
        $company->is_supplier = $request->has('is_supplier');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('companies', 'public_uploads');
            // Store path for asset() including uploads/ prefix since this field stores full relative path
            $company->company_logo = 'uploads/' . $path; // Result: uploads/companies/filename.png
        }

        $company->save();

        // If this was a new company, associate it with the user
        if (!$user->company_id) {
            $user->company_id = $company->company_id;
            $user->save();
        }

        return back()->with('success', __('dashboard.success_update'));
    }

    public function projects()
    {
        $user = Auth::user();
        $company = $user->company;

        // If no company, we can redirect or show empty state. 
        // For consistency with settings, we might want to ensure they have a company first,
        // or just show empty list if no company_id.

        $projects = [];
        if ($company) {
            $companyId = $company->company_id;

            // Fetch projects where company is requester or supplier
            $projects = DB::table('projects')
                ->leftJoin('companies as c_req', 'projects.requester_company_id', '=', 'c_req.company_id')
                ->leftJoin('companies as c_sup', 'projects.supplier_company_id', '=', 'c_sup.company_id')
                ->where('projects.requester_company_id', $companyId)
                ->orWhere('projects.supplier_company_id', $companyId)
                ->select(
                'projects.*',
                'c_req.name as requester_name',
                'c_sup.name as supplier_name'
            )
                ->orderBy('projects.created_at', 'desc')
                ->get();
        }

        return view('dashboard.projects', compact('user', 'company', 'projects'));
    }

    public function team()
    {
        $user = Auth::user();
        $company = $user->company;

        $members = collect([]);
        if ($company) {
            $members = User::where('company_id', $company->company_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('dashboard.team', compact('user', 'company', 'members'));
    }

    public function inviteMember(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return back()->with('error', 'No company associated.');
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:expert,supplier,admin',
            'phone' => 'nullable|string|max:20',
        ]);

        // Creating a new user as "invited"
        $member = new User();
        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->phone = $validated['phone'] ?? null;
        $member->password = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)); // Random password
        $member->company_id = $company->company_id;
        $member->role = $validated['role']; // Use selected role
        
        // Inherit domain if it's a law firm
        if ($user->expert_domain == 'law' || in_array(strtolower($company->industry), ['law', 'legal', 'قانون', 'محاماة'])) {
            $member->expert_domain = 'law';
        }

        $member->is_active = false; // Pending
        $member->save();

        // Generate signed activation URL
        $activationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'activation.show',
            now()->addDays(7),
        ['id' => $member->id]
        );

        // Send invitation email
        \Illuminate\Support\Facades\Mail::to($member->email)->send(new \App\Mail\InviteEmployee($member, $activationUrl));

        return back()->with('success', __('dashboard.invite_sent_success'));
    }

    public function updateMember(Request $request, $id)
    {
        $user = Auth::user();
        $member = User::where('id', $id)->where('company_id', $user->company_id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:expert,supplier,admin',
            'phone' => 'nullable|string|max:20',
        ]);

        $member->name = $validated['name'];
        $member->role = $validated['role'];
        $member->phone = $validated['phone'];
        $member->save();

        return back()->with('success', 'Member updated successfully.');
    }

    public function deleteMember($id)
    {
        $user = Auth::user();
        
        if ($user->id == $id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $member = User::where('id', $id)->where('company_id', $user->company_id)->firstOrFail();
        $member->delete();

        return back()->with('success', 'Member deleted successfully.');
    }
    public function tasks()
    {
        $user = Auth::user();

        $tasks = \App\Models\AiTask::where('client_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('dashboard.tasks', compact('user', 'tasks'));
    }

    public function uploadTasks(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:153600' // 150MB
        ]);

        $user = Auth::user();
        $file = $request->file('csv_file');

        try {
            // Save the file for async processing
            $filePath = $file->store('csv_uploads');

            // Dispatch the job
            \App\Jobs\ProcessTaskUploadJob::dispatch($filePath, $user->id);

            return redirect()->route('dashboard.tasks')
                ->with('success', "Tasks are being uploaded and processed in the background. Results will appear shortly.");
        }
        catch (\Exception $e) {
            return back()->with('error', 'Error uploading tasks: ' . $e->getMessage());
        }
    }
}
