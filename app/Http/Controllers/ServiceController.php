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
    private function fetchServicesFromDatabase($filterSearch, $filterIndustries, $filterMinPrice, $filterMaxPrice, $filterRating)
    {
        $companyServices = collect([]);
        
        // Fetch company services (if table exists)
        try {
            $companyServicesQuery = DB::table('services as s')
                ->join('users as u', 's.user_id', '=', 'u.user_id')
                ->join('companies as c', 's.company_id', '=', 'c.company_id')
                ->where('s.is_active', 1)
                ->where('c.status', 'active')
                ->select([
                    's.service_id',
                    's.title',
                    's.description',
                    's.hourly_rate',
                    's.image as service_image',
                    'u.full_name as expert_name',
                    'u.image as expert_image',
                    'c.name as company_name',
                    'c.company_id',
                    'c.industry',
                    'c.logo as company_logo',
                    DB::raw("'company' as service_type"),
                    DB::raw("(SELECT AVG(r.rating) FROM reviews r WHERE r.service_id = s.service_id) as avg_rating"),
                    DB::raw("(SELECT GROUP_CONCAT(IFNULL(sk.name_ar, sk.name) SEPARATOR ', ') 
                             FROM user_skills us 
                             JOIN skills sk ON us.skill_id = sk.skill_id 
                             WHERE us.user_id = s.user_id) AS skills_list")
                ]);
            
            // Apply filters for company services
            if (!empty($filterIndustries)) {
                $companyServicesQuery->whereIn('c.industry', $filterIndustries);
            }
            
            if (!empty($filterMinPrice)) {
                $companyServicesQuery->where('s.hourly_rate', '>=', $filterMinPrice);
            }
            
            if (!empty($filterMaxPrice)) {
                $companyServicesQuery->where('s.hourly_rate', '<=', $filterMaxPrice);
            }
            
            $companyServices = $companyServicesQuery->get();
        } catch (\Exception $e) {
            // Services table doesn't exist or query failed, continue with empty collection
            $companyServices = collect([]);
        }
        
        $expertServices = collect([]);
        
        // Fetch expert services
        try {
            $expertServicesQuery = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.is_active', 1)
                ->select([
                    'es.service_id',
                    'es.title',
                    'es.description',
                    'es.price as hourly_rate',
                    DB::raw("NULL as service_image"),
                    'u.name as expert_name',
                    DB::raw("COALESCE(u.image, CONCAT('https://ui-avatars.com/api/?name=', REPLACE(u.name, ' ', '+'), '&background=4F46E5&color=fff')) as expert_image"),
                    'u.name as company_name',
                    DB::raw("NULL as company_id"),
                    'es.category as industry',
                    DB::raw("CONCAT('https://ui-avatars.com/api/?name=', SUBSTRING(u.name, 1, 1), '&background=8B5CF6&color=fff') as company_logo"),
                    DB::raw("'expert' as service_type"),
                    DB::raw("NULL as avg_rating"),
                    DB::raw("NULL as skills_list")
                ]);
            
            // Apply filters for expert services
            if (!empty($filterIndustries)) {
                $expertServicesQuery->whereIn('es.category', $filterIndustries);
            }
            
            if (!empty($filterMinPrice)) {
                $expertServicesQuery->where('es.price', '>=', $filterMinPrice);
            }
            
            if (!empty($filterMaxPrice)) {
                $expertServicesQuery->where('es.price', '<=', $filterMaxPrice);
            }
            
            $expertServices = $expertServicesQuery->get();
        } catch (\Exception $e) {
            // Expert services table doesn't exist or query failed
            $expertServices = collect([]);
        }
        
        // Merge both collections
        $services = $companyServices->merge($expertServices);
        
        // Apply search and rating filters (post-query filtering)
        if (!empty($filterSearch)) {
            $searchTerm = strtolower($filterSearch);
            $services = $services->filter(function($service) use ($searchTerm) {
                $skillsList = strtolower($service->skills_list ?? '');
                return str_contains(strtolower($service->title), $searchTerm)
                    || str_contains(strtolower($service->description), $searchTerm)
                    || str_contains($skillsList, $searchTerm)
                    || str_contains(strtolower($service->industry ?? ''), $searchTerm);
            })->values();
        }
        
        if (!empty($filterRating)) {
            $services = $services->filter(function($service) use ($filterRating) {
                return ($service->avg_rating ?? 0) >= $filterRating;
            })->values();
        }
        
        // Format skills list for each service
        $services = $services->map(function($service) {
            $service->skills_array = !empty($service->skills_list) 
                ? explode(', ', $service->skills_list) 
                : [];
            return $service;
        });
        
        // Order by rating and price
        $services = $services->sortByDesc('avg_rating')->sortBy('hourly_rate')->values();
        
        return $services;
    }
    
    /**
     * Fetch industries from database (both company and expert services)
     */
    private function fetchIndustriesFromDatabase()
    {
        // Get industries from company services
        $companyIndustries = DB::table('companies as c')
            ->join('services as s', 'c.company_id', '=', 's.company_id')
            ->whereNotNull('c.industry')
            ->where('c.industry', '!=', '')
            ->where('s.is_active', 1)
            ->where('c.status', 'active')
            ->distinct()
            ->pluck('c.industry');
        
        // Get categories from expert services
        $expertCategories = DB::table('expert_services')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->where('is_active', 1)
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
        
        // First, try to fetch from company services
        $service = DB::table('services as s')
            ->join('users as u', 's.user_id', '=', 'u.user_id')
            ->join('companies as c', 's.company_id', '=', 'c.company_id')
            ->where('s.service_id', $id)
            ->where('s.is_active', 1)
            ->select([
                's.*',
                'u.full_name as expert_name',
                'u.image as expert_image',
                'u.job_title as expert_title',
                'u.bio as expert_bio',
                'c.name as company_name',
                'c.company_id',
                'c.industry',
                'c.logo as company_logo',
                'c.description as company_description',
                DB::raw("'company' as service_type"),
                DB::raw("(SELECT AVG(r.rating) FROM reviews r WHERE r.service_id = s.service_id) as avg_rating"),
                DB::raw("(SELECT COUNT(r.review_id) FROM reviews r WHERE r.service_id = s.service_id) as reviews_count"),
                DB::raw("(SELECT GROUP_CONCAT(IFNULL(sk.name_ar, sk.name) SEPARATOR ', ') 
                         FROM user_skills us 
                         JOIN skills sk ON us.skill_id = sk.skill_id 
                         WHERE us.user_id = s.user_id) AS skills_list")
            ])
            ->first();
            
        // If not found in company services, try expert services
        if (!$service) {
            $service = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.service_id', $id)
                ->where('es.is_active', 1)
                ->select([
                    'es.service_id',
                    'es.title',
                    'es.description',
                    'es.price as hourly_rate',
                    'es.delivery_days',
                    DB::raw("NULL as image"),
                    'u.name as expert_name',
                    DB::raw("COALESCE(u.image, CONCAT('https://ui-avatars.com/api/?name=', REPLACE(u.name, ' ', '+'), '&background=4F46E5&color=fff')) as expert_image"),
                    'u.job_title as expert_title',
                    'u.bio as expert_bio',
                    'u.name as company_name',
                    DB::raw("NULL as company_id"),
                    'es.category as industry',
                    DB::raw("CONCAT('https://ui-avatars.com/api/?name=', SUBSTRING(u.name, 1, 1), '&background=8B5CF6&color=fff') as company_logo"),
                    DB::raw("NULL as company_description"),
                    DB::raw("'expert' as service_type"),
                    DB::raw("NULL as avg_rating"),
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
        
        // Try company services first
        $service = DB::table('services as s')
            ->join('users as u', 's.user_id', '=', 'u.user_id')
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

        // If not found, try expert services
        if (!$service) {
            $service = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.service_id', $id)
                ->where('es.is_active', 1)
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
        
        // Try company services first
        $service = DB::table('services as s')
            ->join('users as u', 's.user_id', '=', 'u.user_id')
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

        // If not found, try expert services
        if (!$service) {
            $service = DB::table('expert_services as es')
                ->join('users as u', 'es.expert_id', '=', 'u.id')
                ->where('es.service_id', $id)
                ->where('es.is_active', 1)
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
