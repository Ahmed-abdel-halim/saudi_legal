<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SentimentCsvService;
use Illuminate\Http\Request;

class SentimentTaskController extends Controller
{
    /**
     * Show upload form
     */
    public function create()
    {
        return view('admin.sentiment.upload');
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
