<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HowItWorksController extends Controller
{
    /**
     * Display the "How It Works" page.
     */
    public function index()
    {
        $currentLang = app()->getLocale();
        
        return view('how-it-works.index', [
            'currentLang' => $currentLang,
        ]);
    }

    /**
     * Display the Benefits page.
     */
    public function benefits()
    {
        return view('how-it-works.benefits');
    }

    /**
     * Display the Pricing page.
     */
    public function pricing()
    {
        return view('how-it-works.pricing');
    }

    /**
     * Display the FAQ page.
     */
    public function faq()
    {
        return view('how-it-works.faq');
    }
}
