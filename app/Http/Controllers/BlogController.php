<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
                     ->orderBy('posted_at', 'desc')
                     ->get();

        return view('blog.index', compact('posts'));
    }
}
