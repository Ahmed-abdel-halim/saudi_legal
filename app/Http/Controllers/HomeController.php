<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch latest expert services from database
        $services = \DB::table('expert_services as es')
            ->join('users as u', 'es.expert_id', '=', 'u.id')
            ->leftJoin('companies as c', 'u.company_id', '=', 'c.company_id')
            ->where('es.is_active', 1)
            ->select([
                'es.service_id',
                'es.title',
                'es.price as hourly_rate',
                'u.name as expert_name',
                'u.avatar_path as expert_image',
                'c.name as company_name'
            ])
            ->orderByDesc('es.created_at')
            ->orderByDesc('es.created_at')
            ->take(3)
            ->get()
            ->map(function ($service) {
                // Fix avatar path if it exists
                if ($service->expert_image) {
                    // Convert storage/uploads to uploads for direct public access
                    if (str_starts_with($service->expert_image, 'storage/uploads/')) {
                        $service->expert_image = str_replace('storage/uploads/', 'uploads/', $service->expert_image);
                    }
                    $service->expert_image = asset($service->expert_image);
                }
                
                // Set default image to null (view can handle fallback)
                $service->image = null;
                
                return $service;
            });

        $isLoggedIn = auth()->check();
        $exploreUrl = route('services.browse');
        $supplierUrl = $isLoggedIn ? '#' : '#';

        return view('home', compact('services', 'isLoggedIn', 'exploreUrl', 'supplierUrl'));
    }
}
