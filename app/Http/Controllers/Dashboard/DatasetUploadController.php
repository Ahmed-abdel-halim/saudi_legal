<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\SubmissionService;
use Illuminate\Http\Request;

class DatasetUploadController extends Controller
{
    protected SubmissionService $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
        $this->middleware('auth');
    }

    /**
     * Show the upload form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('dashboard.admin.dataset-upload');
    }

    /**
     * Handle CSV upload
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');
            $filename = $file->getClientOriginalName();
            $path = $file->getRealPath();

            $result = $this->submissionService->createFromCsv($path, $filename);

            $message = "Successfully imported {$result['total']} tasks.";
            
            if (!empty($result['errors'])) {
                $message .= " " . count($result['errors']) . " errors occurred.";
            }

            return redirect()->back()->with('success', $message)->with('errors', $result['errors']);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }
}
