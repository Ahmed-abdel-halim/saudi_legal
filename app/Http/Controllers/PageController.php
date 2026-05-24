<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the API Documentation page.
     */
    public function apiDocs()
    {
        return view('pages.api_docs');
    }

    /**
     * Display the Security & Compliance page.
     */
    public function securityCompliance()
    {
        return view('pages.security_compliance');
    }

    /**
     * Display the RLHF service page.
     */
    public function rlhf()
    {
        return view('pages.services.rlhf');
    }

    /**
     * Display the HITL service page.
     */
    public function hitl()
    {
        return view('pages.services.hitl');
    }

    /**
     * Display the Data Infrastructure service page.
     */
    public function dataInfrastructure()
    {
        return view('pages.services.data_infrastructure');
    }
}
