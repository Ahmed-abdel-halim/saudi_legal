<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServicePurchase;
use App\Models\WalletTransaction;

class ServiceController extends Controller
{
    /**
     * Display a listing of available services.
     */
    public function browse(Request $request)
    {
        $currentLang = app()->getLocale();
        
        // Get filter parameters
        $filterSearch = $request->get('search', '');
        $filterIndustries = $request->get('industry', []);
        if (!is_array($filterIndustries)) {
            $filterIndustries = $filterIndustries ? [$filterIndustries] : [];
        }
        $filterMinPrice = $request->get('min_price', '');
        $filterMaxPrice = $request->get('max_price', '');
        $filterRating = $request->get('rating', '');
        
        // Fetch from database
        $services = $this->fetchServicesFromDatabase(
            $filterSearch,
            $filterIndustries,
            $filterMinPrice,
            $filterMaxPrice,
            $filterRating
        );
        $industries = $this->fetchIndustriesFromDatabase();
        
        
        return view('services.browse', [
            'services' => $services,
            'industries' => $industries,
            'filterSearch' => $filterSearch,
            'filterIndustries' => $filterIndustries,
            'filterMinPrice' => $filterMinPrice,
            'filterMaxPrice' => $filterMaxPrice,
            'filterRating' => $filterRating,
            'currentLang' => $currentLang,
        ]);
    }
    
    /**
     * Fetch services from database (both company services and expert services)
     */
    /**
     * Fetch services from database (both company and expert services)
     */
    private function fetchServicesFromDatabase($filterSearch, $filterIndustries, $filterMinPrice, $filterMaxPrice, $filterRating)
    {
        // 1. Build Expert Services Query
        // Start with expert services as the base query since we know it exists (or at least more likely)
        $query = DB::table('expert_services as es')
            ->join('users as u', 'es.expert_id', '=', 'u.id')
            ->select([
                'es.service_id as id',
                'es.title',
                'es.description',
                'es.price as price',
                DB::raw("'expert' as type"),
                'es.category as industry',
                DB::raw("NULL as company_name"),
                DB::raw("NULL as company_logo"),
                'u.name as provider_name',
                'u.avatar_path as provider_avatar',
                DB::raw("CONCAT('https://ui-avatars.com/api/?name=', REPLACE(u.name, ' ', '+'), '&background=4F46E5&color=fff') as provider_image"),
                DB::raw("'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80' as image"),
                DB::raw("5.0 as rating"), // Default rating
                DB::raw("NULL as skills")
            ]);

        // 2. Add Company Services Query (only if table exists)
        if (\Illuminate\Support\Facades\Schema::hasTable('services')) {
            $companyQuery = DB::table('services as s')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->join('companies as c', 's.company_id', '=', 'c.company_id')
                ->where('s.is_active', 1)
                ->where('c.status', 'active')
                ->select([
                    's.service_id as id',
                    's.title',
                    's.description',
                    's.hourly_rate as price',
                    DB::raw("'company' as type"),
                    'c.industry',
                    'c.name as company_name',
                    'c.logo as company_logo',
                    'c.name as company_name',
                    'c.logo as company_logo',
                    'u.full_name as provider_name',
                    DB::raw("NULL as provider_image"),
                    's.image as image',
                    DB::raw("5.0 as rating"), // Default rating
                    DB::raw("NULL as skills")
                ]);

            $query = $query->unionAll($companyQuery);
        }

        // 3. Wrap in subquery to allow filtering on calculated fields/unioned results
        $sql = $query->toSql();
        $unionQuery = DB::table(DB::raw("({$sql}) as all_services"))
            ->mergeBindings($query); // This usually requires manual binding handling if not careful.

        // 5. Apply Filters
        if (!empty($filterSearch)) {
            $unionQuery->where(function($q) use ($filterSearch) {
                $q->where('title', 'like', "%{$filterSearch}%")
                  ->orWhere('description', 'like', "%{$filterSearch}%")
                  ->orWhere('provider_name', 'like', "%{$filterSearch}%")
                  ->orWhere('industry', 'like', "%{$filterSearch}%");
            });
        }

        if (!empty($filterIndustries)) {
            $unionQuery->whereIn('industry', $filterIndustries);
        }

        if (!empty($filterMinPrice)) {
            $unionQuery->where('price', '>=', $filterMinPrice);
        }

        if (!empty($filterMaxPrice)) {
            $unionQuery->where('price', '<=', $filterMaxPrice);
        }

        if (!empty($filterRating)) {
            $unionQuery->where('rating', '>=', $filterRating);
        }

        // 6. Sorting
        $unionQuery->orderBy('rating', 'desc')
                   ->orderBy('price', 'asc');

        // 7. Paginate
        $results = $unionQuery->paginate(12);
        
        // 8. Transform attributes for view compatibility
        $results->getCollection()->transform(function ($service) {
            $service->service_id = $service->id;
            $service->hourly_rate = $service->price;
            $service->service_image = $service->image;
            $service->expert_name = $service->provider_name;
            
            // Use centralized avatar logic
            $service->expert_image = \App\Models\User::resolveAvatarUrl($service->provider_avatar ?? null, $service->provider_name);
            
            $service->avg_rating = $service->rating ?? 0;
            $service->skills_list = $service->skills;
            
            $service->skills_array = !empty($service->skills) 
                ? explode(', ', $service->skills) 
                : [];
                
            // Normalize Company Info for view
            if ($service->type === 'expert') {
                // Expert services often don't have a separate company name/logo in previous view logic
                // reusing logic from previous controller
                $service->company_name = $service->provider_name;
                $service->company_logo = \App\Models\User::resolveAvatarUrl(null, $service->provider_name);
            }
            
            return $service;
        });

        return $results;
    }
    
    /**
     * Fetch industries from database (both company and expert services)
     */
    private function fetchIndustriesFromDatabase()
    {
        $companyIndustries = collect([]);
        
        // Get industries from company services (if table exists)
        if (\Illuminate\Support\Facades\Schema::hasTable('services')) {
            $companyIndustries = DB::table('companies as c')
                ->join('services as s', 'c.company_id', '=', 's.company_id')
                ->whereNotNull('c.industry')
                ->where('c.industry', '!=', '')
                ->where('s.is_active', 1)
                ->where('c.status', 'active')
                ->distinct()
                ->pluck('c.industry');
        }
        
        // Get categories from expert services
        $expertCategories = DB::table('expert_services')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            // ->where('is_active', 1)  // Commented out to show all categories
            ->distinct()
            ->pluck('category');
        
        // Merge and deduplicate
        return $companyIndustries
            ->merge($expertCategories)
            ->unique()
            ->sort()
            ->values();
    }
    


    /**
     * Display the specified service detail.
     */
    public function show($id)
    {
        $currentLang = app()->getLocale();
        
        $service = null;
        
        // First, try to fetch from company services if table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('services')) {
            $service = DB::table('services as s')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->join('companies as c', 's.company_id', '=', 'c.company_id')
                ->where('s.service_id', $id)
                ->where('s.is_active', 1)
                ->select([
                    's.*',
                    's.*',
                    'u.full_name as expert_name',
                    DB::raw("CONCAT('https://ui-avatars.com/api/?name=', REPLACE(u.full_name, ' ', '+'), '&background=4F46E5&color=fff') as expert_image"),
                    DB::raw("NULL as expert_title"),
                    DB::raw("NULL as expert_bio"),
                'c.name as company_name',
                'c.company_id',
                'c.industry',
                'c.logo as company_logo',
                'c.description as company_description',
                DB::raw("'company' as service_type"),
                DB::raw("5.0 as avg_rating"), // Default rating
                DB::raw("0 as reviews_count"), // Default count
                DB::raw("NULL as skills_list")
            ])
            ->first();
        }
            
        // If not found in company services, try expert services
        if (!$service) {
            $service = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.service_id', $id)
                // ->where('es.is_active', 1)  // Commented out to show all services
                ->select([
                    'es.service_id',
                    'es.title',
                    'es.description',
                    'es.price as hourly_rate',
                    'es.delivery_days',
                    DB::raw("'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80' as image"), // Default service image
                    'u.name as expert_name',
                    'u.avatar_path',
                    DB::raw("CONCAT('https://ui-avatars.com/api/?name=', REPLACE(u.name, ' ', '+'), '&background=4F46E5&color=fff') as expert_image"),
                    DB::raw("NULL as expert_title"),
                    DB::raw("NULL as expert_bio"),
                    'u.name as company_name',
                    DB::raw("NULL as company_id"),
                    'es.category as industry',
                    DB::raw("CONCAT('https://ui-avatars.com/api/?name=', SUBSTRING(u.name, 1, 1), '&background=8B5CF6&color=fff') as company_logo"),
                    DB::raw("NULL as company_description"),
                    DB::raw("'expert' as service_type"),
                    DB::raw("5.0 as avg_rating"), // Default rating
                    DB::raw("0 as reviews_count"),
                    DB::raw("NULL as skills_list")
                ])
                ->first();
        }
            
        if (!$service) {
            abort(404);
        }
            
        // Use centralized avatar logic
        $service->expert_image = \App\Models\User::resolveAvatarUrl($service->avatar_path ?? null, $service->expert_name);

        // Format skills
        $service->skills_array = !empty($service->skills_list) 
            ? explode(', ', $service->skills_list) 
            : [];

        return view('services.show', compact('service', 'currentLang'));
    }

    /**
     * Show contact form for a specific service/company.
     */
    public function contact($id)
    {
        $currentLang = app()->getLocale();
        
        $service = null;

        // Try company services first
        if (\Illuminate\Support\Facades\Schema::hasTable('services')) {
            $service = DB::table('services as s')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->join('companies as c', 's.company_id', '=', 'c.company_id')
                ->where('s.service_id', $id)
                ->where('s.is_active', 1)
                ->select([
                    's.service_id',
                    's.title',
                    'u.full_name as expert_name',
                    'c.name as company_name',
                    'c.company_id',
                    DB::raw("'company' as service_type")
                ])
                ->first();
        }

        // If not found, try expert services
        if (!$service) {
            $service = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.service_id', $id)
                // ->where('es.is_active', 1)  // Commented out to show all services
                ->select([
                    'es.service_id',
                    'es.title',
                    'u.name as expert_name',
                    'u.name as company_name',
                    DB::raw("NULL as company_id"),
                    DB::raw("'expert' as service_type")
                ])
                ->first();
        }

        if (!$service) {
            abort(404);
        }

        return view('services.contact', compact('service', 'currentLang'));
    }

    /**
     * Show request expert form for a specific service.
     */
    public function request($id)
    {
        $currentLang = app()->getLocale();
        
        $service = null;

        // Try company services first
        if (\Illuminate\Support\Facades\Schema::hasTable('services')) {
            $service = DB::table('services as s')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->join('companies as c', 's.company_id', '=', 'c.company_id')
                ->where('s.service_id', $id)
                ->where('s.is_active', 1)
                ->select([
                    's.service_id',
                    's.title',
                    's.hourly_rate',
                    's.description',
                    'u.full_name as expert_name',
                    'c.name as company_name',
                    'c.company_id',
                    DB::raw("'company' as service_type")
                ])
                ->first();
        }

        // If not found, try expert services
        if (!$service) {
            $service = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.service_id', $id)
                // ->where('es.is_active', 1)  // Commented out to show all services
                ->select([
                    'es.service_id',
                    'es.title',
                    'es.price as hourly_rate',
                    'es.description',
                    'u.name as expert_name',
                    'u.name as company_name',
                    DB::raw("NULL as company_id"),
                    DB::raw("'expert' as service_type")
                ])
                ->first();
        }

        if (!$service) {
            abort(404);
        }

        return view('services.request', compact('service', 'currentLang'));
    }
    public function purchaseHours(Request $request, $id)
    {
        $request->validate([
            'hours' => 'required|integer|min:1',
            'message' => 'nullable|string',
        ]);

        $service = null;
        // Logic to find service (copied from show/request methods temporarily, should be refactored to a helper or model)
        // For now, assuming expert service for simplicity of this flow as per plan
        $service = DB::table('expert_services')->where('service_id', $id)->first();
        
        // If not expert service, maybe company service?
        if (!$service) {
             // Handle company service or 404
             // For now abort if not found
             // Note: In real app, we should standardise Service model access
             $service = DB::table('services')->where('service_id', $id)->first();
        }
        
        if (!$service) abort(404);

        $expertId = $service->expert_id ?? $service->user_id; 
        $hourlyRate = $service->price ?? $service->hourly_rate;

        $purchase = \App\Models\ServicePurchase::create([
            'expert_id' => $expertId,
            'client_id' => auth()->id(),
            'service_id' => $id,
            'hours_purchased' => $request->input('hours'),
            'hourly_rate' => $hourlyRate,
            'total_price' => $request->input('hours') * $hourlyRate,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Immediately create a chat conversation for this purchase
        $chatService = app(\App\Services\ChatService::class);
        $chatService->createChatForPurchase($purchase);

        // Notify the expert about the new service request via websocket/database
        $expert = \App\Models\User::find($expertId);
        if ($expert) {
            $expert->notify(new \App\Notifications\NewServiceRequestNotification($purchase));
        }

        // Redirect to Stripe Checkout.
        // Payment confirmation is handled exclusively by the Stripe Webhook.
        return redirect()->route('payment.checkout', $purchase->id);
    }

    /**
     * Complete a service purchase and distribute funds
     */
    public function completePurchase(Request $request, $id)
    {
        $purchase = ServicePurchase::with(['expert.company'])->findOrFail($id);

        if ($purchase->client_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($purchase->status === 'completed') {
            return back()->with('error', 'Purchase is already completed.');
        }

        if ($purchase->payment_status !== 'paid') {
            return back()->with('error', 'Purchase has not been paid yet.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update([
                'status' => 'completed',
                'service_status' => 'completed',
                'completed_at' => now(),
            ]);

            $totalPrice = $purchase->total_price;
            
            // 20% Platform Commission
            $platformCommission = $totalPrice * 0.20;
            
            // 40% Expert Wallet
            $expertShare = $totalPrice * 0.40;
            
            // 40% Provider Company Wallet
            $companyShare = $totalPrice * 0.40;

            $expert = $purchase->expert;
            $company = $expert->company;

            // Credit Expert
            if ($expert) {
                $expert->increment('wallet_balance', $expertShare);
                WalletTransaction::create([
                    'user_id' => $expert->id,
                    'type' => 'credit',
                    'amount' => $expertShare,
                    'description' => "40% Share from Service Completion (#{$purchase->id})",
                    'reference_type' => ServicePurchase::class,
                    'reference_id' => $purchase->id,
                ]);
            }

            // Credit Company
            if ($company) {
                $company->increment('wallet_balance', $companyShare);
                WalletTransaction::create([
                    'company_id' => $company->company_id,
                    'type' => 'credit',
                    'amount' => $companyShare,
                    'description' => "40% Share from Service Completion (#{$purchase->id})",
                    'reference_type' => ServicePurchase::class,
                    'reference_id' => $purchase->id,
                ]);
            }
        });

        return back()->with('success', 'Service completed successfully. Payment has been distributed.');
    }
}
