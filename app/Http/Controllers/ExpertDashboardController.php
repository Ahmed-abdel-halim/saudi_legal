<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AiTask;
use App\Models\ExpertService;

class ExpertDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 2. Statistics
        $total_tasks = 0;
        $tasks_today = 0;
        
        try {
            $stats = DB::table('ai_responses_v2')
                ->select(DB::raw('count(*) as total_tasks'))
                ->selectRaw("SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as tasks_today")
                ->selectRaw("MAX(created_at) as last_activity")
                ->where('expert_id', $user->id)
                ->first();

            $total_tasks = $stats->total_tasks ?? 0;
            $tasks_today = $stats->tasks_today ?? 0;
        } catch (\Exception $e) {
            $total_tasks = 0;
            $tasks_today = 0;
        }

        // 3. Financials
        $price_per_task = 5;
        $total_balance = $total_tasks * $price_per_task;
        $today_balance = $tasks_today * $price_per_task;

        // 4. Level Logic
        $expert_level = __('expert_dashboard.level_new');
        $badge_color = 'bg-gray-100 text-gray-600';
        $badge_icon = 'fa-user';

        if ($total_tasks > 500) {
            $expert_level = __('expert_dashboard.level_elite');
            $badge_color = 'bg-purple-100 text-purple-700 border-purple-200';
            $badge_icon = 'fa-crown';
        } elseif ($total_tasks > 100) {
            $expert_level = __('expert_dashboard.level_certified');
            $badge_color = 'bg-blue-100 text-blue-700 border-blue-200';
            $badge_icon = 'fa-certificate';
        } elseif ($total_tasks > 20) {
            $expert_level = __('expert_dashboard.level_active');
            $badge_color = 'bg-green-100 text-green-700 border-green-200';
            $badge_icon = 'fa-star';
        }

        // 5. Pending Tasks Count (Strict Assignment Logic)
        $pending_count = 0;
        if ($user->expert_domain && $user->expert_specialization) {
            $pending_count = AiTask::where('status', 'pending')
                ->where('task_domain', $user->expert_domain)
                ->where(function($q) use ($user) {
                    $role = trim($user->expert_specialization);
                    $q->where('allow_all_roles', true)
                      ->orWhere('allowed_roles', 'LIKE', '%"' . $role . '"%')
                      ->orWhere('allowed_roles', 'LIKE', '%' . str_replace('\\', '\\\\', substr(json_encode($role), 1, -1)) . '%');
                })
                ->whereDoesntHave('responses', function($q) use ($user) {
                    $q->where('expert_id', $user->id);
                })
                ->count();
        }

        // 6. History
        $history = collect([]);
        try {
            $history = DB::table('ai_responses_v2')
                ->where('expert_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $history = collect([]);
        }

        return view('dashboard.expert.index', compact(
            'user',
            'total_tasks',
            'tasks_today',
            'total_balance',
            'today_balance',
            'expert_level',
            'badge_color',
            'badge_icon',
            'pending_count',
            'history',
            'price_per_task'
        ));
    }

    public function availability(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            abort(403, 'غير مصرح للطلاب الوصول لهذه الصفحة');
        }
        $msg = '';

        if ($request->isMethod('post')) {
            $days = $request->input('days', []);
            $start_time = $request->input('start_time');
            $end_time = $request->input('end_time');
            $is_active = $request->has('is_active') ? 1 : 0;

            $availability_json = json_encode(['days' => $days, 'start' => $start_time, 'end' => $end_time]);

            // Using DB update or Model update
            // Since we added columns to users table
            DB::table('users')->where('id', $user->id)->update([
                'availability_settings' => $availability_json,
                'is_active_for_hire' => $is_active,
                'updated_at' => now()
            ]);
            
            // Refresh user to get updated data if needed, or just set msg
            $msg = "✅ تم تحديث أوقات العمل بنجاح. أنت الآن جاهز للاستقبال!";
            
            // Re-fetch user to make sure we have latest data for view
            $user = DB::table('users')->where('id', $user->id)->first();
        }

        $settings = json_decode($user->availability_settings ?? '{"days":[], "start":"09:00", "end":"17:00"}', true);
        $active_days = $settings['days'] ?? [];
        
        return view('dashboard.expert.availability', compact('user', 'settings', 'active_days', 'msg'));
    }

    public function cvBuilder(Request $request)
    {
        $user = Auth::user();
        
        if ($request->isMethod('post')) {
            try {
                $validated = $request->validate([
                    'full_name' => 'nullable|string|max:255',
                    'job_title' => 'nullable|string|max:255',
                    'expert_domain' => 'nullable|string|in:medicine,law,engineering,saudi_dialects',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'nullable|string|max:20',
                    'bio' => 'nullable|string|max:2000',
                    'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                ]);

                $updateData = ['updated_at' => now()];

                // Handle avatar upload
                if ($request->hasFile('avatar')) {
                    try {
                        // Delete old avatar if exists
                        if ($user->avatar_path && \Storage::disk('public')->exists($user->avatar_path)) {
                            \Storage::disk('public')->delete($user->avatar_path);
                        }
                        
                        $avatarPath = $request->file('avatar')->store('avatars', 'public');
                        $updateData['avatar_path'] = $avatarPath;
                    } catch (\Exception $e) {
                        \Log::error('Avatar upload failed: ' . $e->getMessage());
                    }
                }

                // Update basic fields
                if ($request->filled('full_name')) {
                    $updateData['name'] = $validated['full_name'];
                }
                
                if ($request->filled('email')) {
                    $updateData['email'] = $validated['email'];
                }
                
                if ($request->filled('phone')) {
                    $updateData['phone'] = $validated['phone'];
                }
                
                if ($request->filled('bio')) {
                    $updateData['bio'] = $validated['bio'];
                }

                // CRITICAL: Handle domain and specialization for strict assignment
                if ($request->filled('expert_domain')) {
                    $updateData['expert_domain'] = $validated['expert_domain'];
                }
                
                if ($request->filled('job_title')) {
                    $updateData['job_title'] = $validated['job_title'];
                    // Sync job_title to expert_specialization for task assignment
                    $updateData['expert_specialization'] = $validated['job_title'];
                }

                // Update user
                DB::table('users')->where('id', $user->id)->update($updateData);

                // Refresh user data
                $user = \App\Models\User::find($user->id);
                
                $successMessage = app()->getLocale() === 'ar' 
                    ? '✅ تم تحديث السيرة الذاتية بنجاح!' 
                    : '✅ CV updated successfully!';
                
                if ($request->filled('job_title') && $request->filled('expert_domain')) {
                    $successMessage .= app()->getLocale() === 'ar'
                        ? ' سيتم تعيين المهام بناءً على تخصصك.'
                        : ' Tasks will be assigned based on your specialization.';
                }
                
                return redirect()->route('dashboard.expert.cv-builder')
                    ->with('success', $successMessage);
                    
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                \Log::error('CV Builder error: ' . $e->getMessage());
                $errorMessage = app()->getLocale() === 'ar'
                    ? 'حدث خطأ أثناء حفظ البيانات. يرجى المحاولة مرة أخرى.'
                    : 'An error occurred while saving. Please try again.';
                return back()->with('error', $errorMessage)->withInput();
            }
        }

        return view('dashboard.expert.cv-builder', compact('user'));
    }

    public function services(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'student') {
            abort(403, 'غير مصرح للطلاب الوصول لهذه الصفحة');
        }

        $msg = '';

        // 1. Add Service
        if ($request->isMethod('post') && $request->has('add_service')) {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string',
                'price' => 'required|numeric|min:0',
                'delivery_days' => 'required|integer|min:1',
                'description' => 'required|string',
            ]);

            ExpertService::create([
                'expert_id' => $user->id,
                'title' => $data['title'],
                'category' => $data['category'],
                'price' => $data['price'],
                'delivery_days' => $data['delivery_days'],
                'description' => $data['description'],
            ]);

            $msg = "✅ تم إضافة الباقة بنجاح!";
        }

        // Fetch Services
        $services = ExpertService::where('expert_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.expert.services', compact('user', 'services', 'msg'));
    }

    public function deleteService($id)
    {
        $user = Auth::user();
        $service = ExpertService::where('service_id', $id)->where('expert_id', $user->id)->first();

        if ($service) {
            $service->delete();
            return redirect()->route('dashboard.expert.services')->with('success', 'تم حذف الخدمة بنجاح');
        }

        return redirect()->route('dashboard.expert.services')->with('error', 'فشل حذف الخدمة');
    }

    public function workbench()
    {
        $user = Auth::user();

        // 1. Stats for Today
        $today_date = date('Y-m-d');
        $tasks_today = DB::table('ai_responses_v2')
            ->where('expert_id', $user->id)
            ->whereDate('created_at', $today_date)
            ->count();
            
        $price_per_task = 5;
        $earnings_today = $tasks_today * $price_per_task;
        
        // 2. Fetch one pending task (random or next available)
        // Ignoring tasks assigned to others if that logic existed, but for now just any pending
        $currentTask = AiTask::where('status', 'pending')
            ->orderBy('id', 'asc')
            ->first();

        // Pass data to view (matching the view's expected variables from standard Controller practice)
        // The view expects: $currentTask (array or object), $tasks_today, $earnings_today
        return view('dashboard.expert.workbench', compact('user', 'currentTask', 'tasks_today', 'earnings_today'));
    }

    public function handleTaskAction(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action');
        $taskId = $request->input('task_id');

        if (!$taskId) {
            return response()->json(['success' => false, 'message' => 'معرف المهمة مفقود']);
        }

        $task = AiTask::find($taskId);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'المهمة غير موجودة']);
        }

        if ($action === 'skip_task') {
            // Update status to skipped
            $task->status = 'skipped';
            $task->save();
            return response()->json(['success' => true]);
        }

        if ($action === 'submit_task') {
            $correction = trim($request->input('correction'));
            
            if (empty($correction)) {
                return response()->json(['success' => false, 'message' => 'البيانات فارغة']);
            }

            // Save response in ai_responses_v2
            // Assuming we have a model or use DB
            DB::table('ai_responses_v2')->insert([
                'task_id' => $taskId,
                'expert_id' => $user->id,
                'correction' => $correction,
                'created_at' => now(),
            ]);

            // Mark task as completed
            $task->status = 'completed';
            $task->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'إجراء غير معروف']);
    }

    public function settings()
    {
        $user = Auth::user();
        return view('dashboard.expert.settings', compact('user'));
    }
}
