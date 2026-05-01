<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SentimentCsvService;
use Illuminate\Http\Request;

class SentimentTaskController extends Controller
{
    /**
     * Show tracker
     */
    public function index(Request $request)
    {
        $query = \App\Models\LinguisticTask::where('task_type', 'sentiment')->with('expert');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('comment_text', 'like', '%' . $request->search . '%')
                    ->orWhere('csv_file', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->latest()->paginate(20);

        // Also get batch statistics
        $batches = \App\Models\LinguisticTask::where('task_type', 'sentiment')
            ->select('csv_file', \Illuminate\Support\Facades\DB::raw('count(*) as total'), \Illuminate\Support\Facades\DB::raw('sum(case when status="completed" then 1 else 0 end) as completed'))
            ->whereNotNull('csv_file')
            ->groupBy('csv_file')
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('max(created_at)'))
            ->get();

        return view('admin.sentiment.index', compact('tasks', 'batches'));
    }

    /**
     * Handle CSV upload
     */
    public function store(Request $request, SentimentCsvService $service)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB
        ]);

        try {
            $results = $service->processCsv($request->file('csv_file'));

            $message = "Successfully processed {$results['total_rows']} rows. Created {$results['tasks_created']} tasks.";
            if (!empty($results['errors'])) {
                $message .= " Encounered " . count($results['errors']) . " errors.";
            }

            return back()->with('success', $message)
                ->with('analysis_results', $results);

        } catch (\Exception $e) {
            return back()->with('error', 'Error processing CSV: ' . $e->getMessage());
        }
    }
}
