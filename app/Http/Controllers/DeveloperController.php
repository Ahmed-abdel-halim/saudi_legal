<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BetaAccessRequest;
use Illuminate\Support\Str;

class DeveloperController extends Controller
{
    /**
     * Display the Developer Portal & API Documentation
     */
    public function index()
    {
        return view('developers.index');
    }

    /**
     * Show the Beta Access Request Form
     */
    public function betaForm()
    {
        return view('developers.beta_access');
    }

    /**
     * Handle the Beta Access Application submission
     */
    public function submitBeta(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:150',
            'email'             => 'required|email|max:150',
            'phone'             => 'nullable|string|max:50',
            'company'           => 'nullable|string|max:150',
            'organization_type' => 'required|string|in:law_firm,legaltech,enterprise,developer,individual,other',
            'use_case'          => 'required|string|max:2000',
            'expected_volume'   => 'required|string|in:under_10k,10k_to_100k,over_100k',
            'tech_stack'        => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:1000',
        ]);

        // Generate a test/sandbox token for the applicant
        $sandboxKey = 'radif_test_' . Str::random(32);

        $record = BetaAccessRequest::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'] ?? null,
            'company'           => $validated['company'] ?? null,
            'organization_type' => $validated['organization_type'],
            'use_case'          => $validated['use_case'],
            'expected_volume'   => $validated['expected_volume'],
            'tech_stack'        => $validated['tech_stack'] ?? null,
            'notes'             => $validated['notes'] ?? null,
            'status'            => 'pending',
            'api_key_sandbox'   => $sandboxKey,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => 'تم استلام طلبك بنجاح! فريق منصة رديف سيتواصل معك خلال 24 ساعة لتفعيل مفتاح الـ Production.',
                'sandbox_key' => $sandboxKey,
            ]);
        }

        return redirect()->route('developers.beta')->with([
            'success'     => 'تم استلام طلبك بنجاح! تم حجز مقعدك في النسخة التجريبية للمطورين.',
            'sandbox_key' => $sandboxKey,
            'applicant_name' => $record->name,
        ]);
    }
}
