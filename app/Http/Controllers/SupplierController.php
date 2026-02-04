<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SupplierController extends Controller
{
    /**
     * Display a listing of supplier companies with filters.
     */
    public function browse(Request $request)
    {
        $currentLang = app()->getLocale();

        $industryFilter = $request->get('industry', '');
        $sizeFilter = $request->get('size', '');

        $companies = $this->fetchCompaniesFromDatabase($industryFilter, $sizeFilter);
        $industries = $this->fetchIndustriesFromDatabase();
        $sizes = $this->fetchSizesFromDatabase();

        return view('suppliers.browse', [
            'companies' => $companies,
            'industries' => $industries,
            'sizes' => $sizes,
            'industryFilter' => $industryFilter,
            'sizeFilter' => $sizeFilter,
            'currentLang' => $currentLang,
        ]);
    }

    public function show($id)
    {
        $currentLang = app()->getLocale();

        $company = $this->fetchCompanyById($id);
        
        if (!$company) {
            abort(404);
        }

        $services = $this->fetchCompanyServices($id);
        $ratingData = $this->fetchCompanyRating($id);
        $projectCount = $this->fetchProjectCount($id);

        return view('suppliers.profile', [
            'company' => $company,
            'services' => $services,
            'avgRating' => $ratingData['avg'] ?? 0,
            'reviewCount' => $ratingData['count'] ?? 0,
            'projectCount' => $projectCount,
            'currentLang' => $currentLang,
        ]);
    }

    private function fetchCompaniesFromDatabase($industryFilter, $sizeFilter)
    {
        $query = \App\Models\Company::where('status', 'active')
            ->where('is_supplier', 1); // Only show supplier companies

        if (!empty($industryFilter)) {
            $query->where('industry', $industryFilter);
        }

        if (!empty($sizeFilter)) {
            $query->where('size', $sizeFilter);
        }

        $companies = $query->get()->map(function ($company) {
            // Calculate service count (optional - may not exist yet)
            try {
                $company->service_count = DB::table('expert_services as es')
                    ->join('users as u', 'es.expert_id', '=', 'u.id')
                    ->where('u.company_id', $company->company_id)
                    ->where('es.is_active', 1)
                    ->count();
            } catch (\Exception $e) {
                $company->service_count = 0;
            }

            // Calculate average rating (optional - may not exist yet)
            try {
                $company->avg_rating = DB::table('reviews as r')
                    ->join('projects as p', 'r.project_id', '=', 'p.project_id')
                    ->where('p.supplier_company_id', $company->company_id)
                    ->avg('r.rating') ?? 0;
            } catch (\Exception $e) {
                $company->avg_rating = 0;
            }

            // Fix logo path
            $company->company_logo = $this->resolveLogo($company->company_logo, $company->name);

            return $company;
        });

        // Sort by service count and rating
        return $companies->sortByDesc(function ($company) {
            return ($company->service_count * 100) + ($company->avg_rating * 10);
        })->values();
    }

    private function fetchIndustriesFromDatabase()
    {
        return \App\Models\Company::where('status', 'active')
            ->where('is_supplier', 1)
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
            ->values();
    }

    private function fetchSizesFromDatabase()
    {
        return \App\Models\Company::where('status', 'active')
            ->where('is_supplier', 1)
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->distinct()
            ->orderBy('size')
            ->pluck('size')
            ->values();
    }

    private function resolveLogo($logo, $name)
    {
        if (empty($logo)) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&size=128&bold=true';
        }

        // Fix old storage/uploads paths to use direct uploads path
        if (str_starts_with($logo, 'storage/uploads/')) {
            $logo = str_replace('storage/uploads/', 'uploads/', $logo);
        }

        if (!filter_var($logo, FILTER_VALIDATE_URL)) {
            return asset(ltrim($logo, '/'));
        }

        return $logo;
    }

    /**
     * Fetch a single company by ID from database.
     */
    private function fetchCompanyById($id)
    {
        $company = \App\Models\Company::where('company_id', $id)
            ->where('status', 'active')
            ->where('is_supplier', 1)
            ->first();

        if ($company) {
            // Calculate service count
            try {
                $company->service_count = DB::table('expert_services as es')
                    ->join('users as u', 'es.expert_id', '=', 'u.id')
                    ->where('u.company_id', $company->company_id)
                    ->where('es.is_active', 1)
                    ->count();
            } catch (\Exception $e) {
                $company->service_count = 0;
            }

            // Fix logo path
            $company->company_logo = $this->resolveLogo($company->company_logo, $company->name);
        }

        return $company;
    }

    /**
     * Fetch services for a specific company.
     */
    private function fetchCompanyServices($companyId, $limit = 6)
    {
        try {
            // Fetch expert services for this company by joining users and expert_services
            $services = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('u.company_id', $companyId)
                ->where('es.is_active', 1)
                ->select([
                    'es.service_id',
                    'es.title',
                    'es.description',
                    'es.category',
                    'es.price as hourly_rate', // Map price to hourly_rate for view compatibility
                    DB::raw('0 as is_featured'), // No featured field in expert_services, default to 0
                    'u.name as expert_name',
                    'u.id as expert_id'
                ])
                ->orderByDesc('es.created_at')
                ->limit($limit)
                ->get();

            return $services;
        } catch (\Exception $e) {
            // Return empty collection if table doesn't exist
            return collect([]);
        }
    }

    /**
     * Fetch company rating data.
     */
    private function fetchCompanyRating($companyId)
    {
        try {
            $result = DB::table('reviews as r')
                ->join('projects as p', 'r.project_id', '=', 'p.project_id')
                ->where('p.supplier_company_id', $companyId)
                ->selectRaw('AVG(r.rating) as avg, COUNT(*) as count')
                ->first();

            return [
                'avg' => $result->avg ?? 0,
                'count' => $result->count ?? 0,
            ];
        } catch (\Exception $e) {
            return ['avg' => 0, 'count' => 0];
        }
    }

    /**
     * Fetch project count for a company.
     */
    private function fetchProjectCount($companyId)
    {
        try {
            return DB::table('projects')
                ->where('supplier_company_id', $companyId)
                ->where('status', 'completed')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
