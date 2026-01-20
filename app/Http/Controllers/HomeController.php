<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Mock services data - Replace with actual database query when models are ready
        $services = [
            [
                'id' => 1,
                'title' => 'Full Stack Developer',
                'expert_name' => 'أحمد محمد',
                'expert_image' => null,
                'company_name' => 'Tech Solutions',
                'hourly_rate' => 150,
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80',
            ],
            [
                'id' => 2,
                'title' => 'UI/UX Designer',
                'expert_name' => 'سارة علي',
                'expert_image' => null,
                'company_name' => 'Design Studio',
                'hourly_rate' => 120,
                'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&q=80',
            ],
            [
                'id' => 3,
                'title' => 'DevOps Engineer',
                'expert_name' => 'خالد أحمد',
                'expert_image' => null,
                'company_name' => 'Cloud Services',
                'hourly_rate' => 180,
                'image' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=600&q=80',
            ],
        ];

        $isLoggedIn = auth()->check();
        $exploreUrl = '#';
        $supplierUrl = $isLoggedIn ? '#' : '#';

        return view('home', compact('services', 'isLoggedIn', 'exploreUrl', 'supplierUrl'));
    }
}
