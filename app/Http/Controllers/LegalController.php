<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display the Terms of Service page.
     */
    public function terms()
    {
        return view('legal.terms');
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy()
    {
        return view('legal.privacy');
    }

    /**
     * Display the MSA page.
     */
    public function msa()
    {
        return view('legal.msa');
    }

    /**
     * Display the NDA (Non-Disclosure Agreement) page.
     */
    public function nda()
    {
        return view('legal.nda');
    }
}
