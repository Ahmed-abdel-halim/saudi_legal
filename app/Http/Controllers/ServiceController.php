<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $service->expert_image = $service->provider_image;
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
                $service->company_logo = "https://ui-avatars.com/api/?name=" . substr($service->provider_name, 0, 1) . "&background=8B5CF6&color=fff";
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
     * Get mock services data for display when database is not ready
     */
    private function getMockServices()
    {
        $currentLang = app()->getLocale();
        
        $mockData = [
            [
                'service_id' => 1,
                'title' => $currentLang === 'ar' ? 'تطوير تطبيقات Laravel' : 'Laravel Application Development',
                'description' => $currentLang === 'ar'
                    ? 'مطور Laravel محترف مع خبرة 5+ سنوات في تطوير تطبيقات الويب المعقدة. متخصص في APIs، Real-time applications، و Microservices.'
                    : 'Professional Laravel developer with 5+ years of experience in complex web applications. Specialized in APIs, Real-time applications, and Microservices.',
                'hourly_rate' => 120.00,
                'service_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80',
                'expert_name' => $currentLang === 'ar' ? 'أحمد محمد' : 'Ahmed Mohammed',
                'expert_image' => 'https://ui-avatars.com/api/?name=Ahmed&background=4F46E5&color=fff',
                'company_name' => $currentLang === 'ar' ? 'شركة التقنية المتقدمة' : 'Advanced Tech Company',
                'company_id' => 1,
                'industry' => $currentLang === 'ar' ? 'تطوير البرمجيات' : 'Software Development',
                'company_logo' => 'https://ui-avatars.com/api/?name=ATC&background=8B5CF6&color=fff',
                'avg_rating' => 4.8,
                'skills_list' => 'Laravel, PHP, MySQL, REST API, Vue.js',
            ],
            [
                'service_id' => 2,
                'title' => $currentLang === 'ar' ? 'تصميم واجهات المستخدم' : 'UI/UX Design',
                'description' => $currentLang === 'ar'
                    ? 'مصمم UI/UX محترف متخصص في تصميم واجهات مستخدم حديثة وجذابة. خبرة في Figma، Adobe XD، و Prototyping.'
                    : 'Professional UI/UX designer specialized in modern and attractive user interfaces. Experience in Figma, Adobe XD, and Prototyping.',
                'hourly_rate' => 80.00,
                'service_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&q=80',
                'expert_name' => $currentLang === 'ar' ? 'سارة أحمد' : 'Sara Ahmed',
                'expert_image' => 'https://ui-avatars.com/api/?name=Sara&background=d946ef&color=fff',
                'company_name' => $currentLang === 'ar' ? 'شركة التصميم الإبداعي' : 'Creative Design Company',
                'company_id' => 2,
                'industry' => $currentLang === 'ar' ? 'التصميم' : 'Design',
                'company_logo' => 'https://ui-avatars.com/api/?name=CDC&background=d946ef&color=fff',
                'avg_rating' => 4.9,
                'skills_list' => 'Figma, Adobe XD, UI/UX Design, Prototyping',
            ],
            [
                'service_id' => 3,
                'title' => $currentLang === 'ar' ? 'تطوير تطبيقات React Native' : 'React Native Development',
                'description' => $currentLang === 'ar'
                    ? 'مطور React Native محترف لبناء تطبيقات جوال عالية الجودة لنظامي iOS و Android. خبرة في State Management و API Integration.'
                    : 'Professional React Native developer for building high-quality mobile apps for both iOS and Android. Experience in State Management and API Integration.',
                'hourly_rate' => 150.00,
                'service_image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=600&q=80',
                'expert_name' => $currentLang === 'ar' ? 'خالد علي' : 'Khaled Ali',
                'expert_image' => 'https://ui-avatars.com/api/?name=Khaled&background=0d9488&color=fff',
                'company_name' => $currentLang === 'ar' ? 'شركة التطبيقات الذكية' : 'Smart Apps Company',
                'company_id' => 3,
                'industry' => $currentLang === 'ar' ? 'تطوير البرمجيات' : 'Software Development',
                'company_logo' => 'https://ui-avatars.com/api/?name=SAC&background=0d9488&color=fff',
                'avg_rating' => 4.7,
                'skills_list' => 'React Native, JavaScript, Redux, Firebase',
            ],
            [
                'service_id' => 4,
                'title' => $currentLang === 'ar' ? 'استشارات قاعدة البيانات' : 'Database Consulting',
                'description' => $currentLang === 'ar'
                    ? 'خبير في قواعد البيانات متخصص في MySQL، PostgreSQL، و MongoDB. خدمات التحسين، التصميم، و Migration.'
                    : 'Database expert specialized in MySQL, PostgreSQL, and MongoDB. Optimization, design, and migration services.',
                'hourly_rate' => 200.00,
                'service_image' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=600&q=80',
                'expert_name' => $currentLang === 'ar' ? 'محمد حسن' : 'Mohammed Hassan',
                'expert_image' => 'https://ui-avatars.com/api/?name=Mohammed&background=2980b9&color=fff',
                'company_name' => $currentLang === 'ar' ? 'شركة البيانات الكبيرة' : 'Big Data Company',
                'company_id' => 4,
                'industry' => $currentLang === 'ar' ? 'قواعد البيانات' : 'Database',
                'company_logo' => 'https://ui-avatars.com/api/?name=BDC&background=2980b9&color=fff',
                'avg_rating' => 4.6,
                'skills_list' => 'MySQL, PostgreSQL, MongoDB, Database Optimization',
            ],
            [
                'service_id' => 5,
                'title' => $currentLang === 'ar' ? 'تطوير واجهات Vue.js' : 'Vue.js Frontend Development',
                'description' => $currentLang === 'ar'
                    ? 'مطور Vue.js محترف لبناء واجهات مستخدم تفاعلية وسريعة. خبرة في Vue 3، Composition API، و Pinia.'
                    : 'Professional Vue.js developer for building interactive and fast user interfaces. Experience in Vue 3, Composition API, and Pinia.',
                'hourly_rate' => 100.00,
                'service_image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=600&q=80',
                'expert_name' => $currentLang === 'ar' ? 'فاطمة إبراهيم' : 'Fatima Ibrahim',
                'expert_image' => 'https://ui-avatars.com/api/?name=Fatima&background=8e44ad&color=fff',
                'company_name' => $currentLang === 'ar' ? 'شركة الواجهات الحديثة' : 'Modern Frontend Company',
                'company_id' => 5,
                'industry' => $currentLang === 'ar' ? 'تطوير البرمجيات' : 'Software Development',
                'company_logo' => 'https://ui-avatars.com/api/?name=MFC&background=8e44ad&color=fff',
                'avg_rating' => 4.5,
                'skills_list' => 'Vue.js, JavaScript, TypeScript, Pinia',
            ],
            [
                'service_id' => 6,
                'title' => $currentLang === 'ar' ? 'اختبار البرمجيات' : 'Software Testing',
                'description' => $currentLang === 'ar'
                    ? 'مختبر برمجيات محترف متخصص في Automated Testing، Manual Testing، و Performance Testing. خبرة في Jest، Cypress، و Selenium.'
                    : 'Professional software tester specialized in Automated Testing, Manual Testing, and Performance Testing. Experience in Jest, Cypress, and Selenium.',
                'hourly_rate' => 70.00,
                'service_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80',
                'expert_name' => $currentLang === 'ar' ? 'نورا سعيد' : 'Nora Saeed',
                'expert_image' => 'https://ui-avatars.com/api/?name=Nora&background=f39c12&color=fff',
                'company_name' => $currentLang === 'ar' ? 'شركة الجودة البرمجية' : 'Software Quality Company',
                'company_id' => 6,
                'industry' => $currentLang === 'ar' ? 'الاختبار' : 'Testing',
                'company_logo' => 'https://ui-avatars.com/api/?name=SQC&background=f39c12&color=fff',
                'avg_rating' => 4.4,
                'skills_list' => 'QA Testing, Jest, Cypress, Selenium',
            ],
        ];
        
        // Convert to collection and format skills
        $services = collect($mockData)->map(function($item) {
            $item = (object) $item;
            $item->skills_array = !empty($item->skills_list) 
                ? explode(', ', $item->skills_list) 
                : [];
            return $item;
        });
        
        return $services;
    }
    
    /**
     * Get mock industries data
     */
    private function getMockIndustries()
    {
        $currentLang = app()->getLocale();
        
        return collect([
            $currentLang === 'ar' ? 'تطوير البرمجيات' : 'Software Development',
            $currentLang === 'ar' ? 'التصميم' : 'Design',
            $currentLang === 'ar' ? 'قواعد البيانات' : 'Database',
            $currentLang === 'ar' ? 'الاختبار' : 'Testing',
        ])->sort()->values();
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
}
