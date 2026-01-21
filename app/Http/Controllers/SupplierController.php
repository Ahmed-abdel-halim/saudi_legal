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

        try {
            $companies = $this->fetchCompaniesFromDatabase($industryFilter, $sizeFilter);
            $industries = $this->fetchIndustriesFromDatabase();
            $sizes = $this->fetchSizesFromDatabase();
        } catch (\Exception $e) {
            // Fallback to mock data if database tables are missing
            $companies = $this->getMockCompanies($currentLang);
            $industries = $this->getMockIndustries($currentLang);
            $sizes = $this->getMockSizes($currentLang);

            if (!empty($industryFilter)) {
                $companies = $companies->filter(function ($c) use ($industryFilter) {
                    return $c->industry === $industryFilter;
                })->values();
            }

            if (!empty($sizeFilter)) {
                $companies = $companies->filter(function ($c) use ($sizeFilter) {
                    return $c->size === $sizeFilter;
                })->values();
            }
        }

        return view('suppliers.browse', [
            'companies' => $companies,
            'industries' => $industries,
            'sizes' => $sizes,
            'industryFilter' => $industryFilter,
            'sizeFilter' => $sizeFilter,
            'currentLang' => $currentLang,
        ]);
    }

    /**
     * Display a single supplier company profile.
     */
    public function show($id)
    {
        $currentLang = app()->getLocale();

        try {
            $company = $this->fetchCompanyById($id);
            
            if (!$company) {
                abort(404);
            }

            $services = $this->fetchCompanyServices($id);
            $ratingData = $this->fetchCompanyRating($id);
            $projectCount = $this->fetchProjectCount($id);

        } catch (\Exception $e) {
            // Fallback to mock data
            $mockCompanies = $this->getMockCompanies($currentLang);
            $company = $mockCompanies->firstWhere('company_id', (int)$id);
            
            if (!$company) {
                abort(404);
            }

            $services = $this->getMockServices($currentLang, $id);
            $ratingData = ['avg' => $company->avg_rating ?? 4.5, 'count' => rand(10, 50)];
            $projectCount = rand(5, 25);
        }

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
        $query = DB::table('companies as c')
            ->where('c.status', 'active')
            ->select([
                'c.company_id',
                'c.name',
                'c.industry',
                'c.size',
                'c.logo as company_logo',
                DB::raw('(SELECT COUNT(*) FROM services s WHERE s.company_id = c.company_id AND s.is_active = 1) AS service_count'),
                DB::raw('(SELECT AVG(r.rating) FROM reviews r JOIN projects p ON r.project_id = p.project_id WHERE p.supplier_company_id = c.company_id) AS avg_rating'),
            ]);

        if (!empty($industryFilter)) {
            $query->where('c.industry', $industryFilter);
        }

        if (!empty($sizeFilter)) {
            $query->where('c.size', $sizeFilter);
        }

        $query->orderByDesc('service_count')->orderByDesc('avg_rating');

        return $query->get()->map(function ($item) {
            $item->company_logo = $this->resolveLogo($item->company_logo, $item->name);
            return $item;
        });
    }

    private function fetchIndustriesFromDatabase()
    {
        return DB::table('companies')
            ->where('status', 'active')
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
            ->values();
    }

    private function fetchSizesFromDatabase()
    {
        return DB::table('companies')
            ->where('status', 'active')
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

        if (!filter_var($logo, FILTER_VALIDATE_URL)) {
            return asset(ltrim($logo, '/'));
        }

        return $logo;
    }

    /**
     * Mock data for companies when database is not ready.
     */
    private function getMockCompanies($lang)
    {
        $mock = [
            [
                'company_id' => 1,
                'name' => $lang === 'ar' ? 'شركة التقنية المتقدمة' : 'Advanced Tech Co.',
                'industry' => $lang === 'ar' ? 'تطوير البرمجيات' : 'Software Development',
                'size' => $lang === 'ar' ? 'كبيرة' : 'Large',
                'company_logo' => 'https://ui-avatars.com/api/?name=ATC&background=4F46E5&color=fff',
                'service_count' => 18,
                'avg_rating' => 4.7,
            ],
            [
                'company_id' => 2,
                'name' => $lang === 'ar' ? 'شركة التصميم الإبداعي' : 'Creative Design Co.',
                'industry' => $lang === 'ar' ? 'التصميم' : 'Design',
                'size' => $lang === 'ar' ? 'متوسطة' : 'Medium',
                'company_logo' => 'https://ui-avatars.com/api/?name=CDC&background=d946ef&color=fff',
                'service_count' => 12,
                'avg_rating' => 4.8,
            ],
            [
                'company_id' => 3,
                'name' => $lang === 'ar' ? 'شركة البيانات الكبيرة' : 'Big Data Co.',
                'industry' => $lang === 'ar' ? 'البيانات' : 'Data',
                'size' => $lang === 'ar' ? 'كبيرة' : 'Large',
                'company_logo' => 'https://ui-avatars.com/api/?name=BDC&background=06b6d4&color=fff',
                'service_count' => 9,
                'avg_rating' => 4.6,
            ],
            [
                'company_id' => 4,
                'name' => $lang === 'ar' ? 'شركة الاختبارات المتقدمة' : 'Advanced QA',
                'industry' => $lang === 'ar' ? 'الاختبار' : 'Testing',
                'size' => $lang === 'ar' ? 'صغيرة' : 'Small',
                'company_logo' => 'https://ui-avatars.com/api/?name=AQA&background=f39c12&color=fff',
                'service_count' => 7,
                'avg_rating' => 4.5,
            ],
            [
                'company_id' => 5,
                'name' => $lang === 'ar' ? 'شركة التكامل التقني' : 'Tech Integration Co.',
                'industry' => $lang === 'ar' ? 'التكامل' : 'Integration',
                'size' => $lang === 'ar' ? 'متوسطة' : 'Medium',
                'company_logo' => 'https://ui-avatars.com/api/?name=TIC&background=8B5CF6&color=fff',
                'service_count' => 11,
                'avg_rating' => 4.4,
            ],
            [
                'company_id' => 6,
                'name' => $lang === 'ar' ? 'شركة الأمن السيبراني' : 'Cyber Security Co.',
                'industry' => $lang === 'ar' ? 'الأمن السيبراني' : 'Cybersecurity',
                'size' => $lang === 'ar' ? 'كبيرة' : 'Large',
                'company_logo' => 'https://ui-avatars.com/api/?name=CSC&background=0f172a&color=fff',
                'service_count' => 14,
                'avg_rating' => 4.9,
            ],
        ];

        return collect($mock)->map(function ($item) {
            return (object) $item;
        });
    }

    private function getMockIndustries($lang)
    {
        return collect([
            $lang === 'ar' ? 'تطوير البرمجيات' : 'Software Development',
            $lang === 'ar' ? 'التصميم' : 'Design',
            $lang === 'ar' ? 'البيانات' : 'Data',
            $lang === 'ar' ? 'الاختبار' : 'Testing',
            $lang === 'ar' ? 'الأمن السيبراني' : 'Cybersecurity',
            $lang === 'ar' ? 'التكامل' : 'Integration',
        ])->sort()->values();
    }

    private function getMockSizes($lang)
    {
        return collect([
            $lang === 'ar' ? 'صغيرة' : 'Small',
            $lang === 'ar' ? 'متوسطة' : 'Medium',
            $lang === 'ar' ? 'كبيرة' : 'Large',
        ]);
    }

    /**
     * Fetch a single company by ID from database.
     */
    private function fetchCompanyById($id)
    {
        $company = DB::table('companies as c')
            ->where('c.company_id', $id)
            ->where('c.status', 'active')
            ->select([
                'c.company_id',
                'c.name',
                'c.industry',
                'c.size',
                'c.logo as company_logo',
                'c.description',
                'c.created_at',
                DB::raw('(SELECT COUNT(*) FROM services s WHERE s.company_id = c.company_id AND s.is_active = 1) AS service_count'),
            ])
            ->first();

        if ($company) {
            $company->company_logo = $this->resolveLogo($company->company_logo, $company->name);
        }

        return $company;
    }

    /**
     * Fetch services for a specific company.
     */
    private function fetchCompanyServices($companyId, $limit = 6)
    {
        return DB::table('services')
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->select(['service_id', 'title', 'description', 'category', 'hourly_rate', 'is_featured'])
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Fetch company rating data.
     */
    private function fetchCompanyRating($companyId)
    {
        $result = DB::table('reviews as r')
            ->join('projects as p', 'r.project_id', '=', 'p.project_id')
            ->where('p.supplier_company_id', $companyId)
            ->selectRaw('AVG(r.rating) as avg, COUNT(*) as count')
            ->first();

        return [
            'avg' => $result->avg ?? 0,
            'count' => $result->count ?? 0,
        ];
    }

    /**
     * Fetch project count for a company.
     */
    private function fetchProjectCount($companyId)
    {
        return DB::table('projects')
            ->where('supplier_company_id', $companyId)
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Mock services data for fallback.
     */
    private function getMockServices($lang, $companyId)
    {
        $services = [
            [
                'service_id' => 1,
                'title' => $lang === 'ar' ? 'تطوير تطبيقات الويب' : 'Web Application Development',
                'description' => $lang === 'ar' ? 'تطوير تطبيقات ويب متكاملة باستخدام أحدث التقنيات' : 'Full-stack web application development using latest technologies',
                'category' => $lang === 'ar' ? 'تطوير' : 'Development',
                'hourly_rate' => 250,
                'is_featured' => 1,
            ],
            [
                'service_id' => 2,
                'title' => $lang === 'ar' ? 'تصميم واجهات المستخدم' : 'UI/UX Design',
                'description' => $lang === 'ar' ? 'تصميم واجهات مستخدم جذابة وسهلة الاستخدام' : 'Creating beautiful and user-friendly interface designs',
                'category' => $lang === 'ar' ? 'تصميم' : 'Design',
                'hourly_rate' => 200,
                'is_featured' => 0,
            ],
            [
                'service_id' => 3,
                'title' => $lang === 'ar' ? 'استشارات تقنية' : 'Technical Consulting',
                'description' => $lang === 'ar' ? 'استشارات تقنية متخصصة لمشاريعك الرقمية' : 'Specialized technical consulting for your digital projects',
                'category' => $lang === 'ar' ? 'استشارات' : 'Consulting',
                'hourly_rate' => 300,
                'is_featured' => 1,
            ],
        ];

        return collect($services)->map(function ($item) {
            return (object) $item;
        });
    }
}
