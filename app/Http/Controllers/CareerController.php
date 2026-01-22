<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $careers = Career::where('is_open', true)
                         ->orderBy('created_at', 'desc')
                         ->get();

        return view('careers', compact('careers'));
    }
}
