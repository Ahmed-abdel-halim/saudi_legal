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
}
