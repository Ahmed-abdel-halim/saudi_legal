<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RequestController extends Controller
{
    /**
     * Display a listing of available project requests.
     */
    public function browse(Request $request)
    {
        $currentLang = app()->getLocale();

        // Get filter parameters
        $filterSearch = $request->get('search', '');
        $filterMaxRate = $request->get('max_rate', '');

        try {
            // Try to fetch from database
            $requests = $this->fetchRequestsFromDatabase($filterSearch, $filterMaxRate);
        } catch (\Exception $e) {
            // If database tables don't exist, use mock data
            $requests = $this->getMockRequests();

            // Apply filters to mock data
            if (!empty($filterSearch)) {
                $searchTerm = strtolower($filterSearch);
                $requests = $requests->filter(function ($request) use ($searchTerm) {
                    return str_contains(strtolower($request->title), $searchTerm)
                        || str_contains(strtolower($request->scope_description), $searchTerm)
                        || str_contains(strtolower($request->skills_list ?? ''), $searchTerm);
                })->values();
            }

            if (!empty($filterMaxRate)) {
                $requests = $requests->filter(function ($request) use ($filterMaxRate) {
                    return $request->max_hourly_rate <= $filterMaxRate;
                })->values();
            }
        }

        return view('requests.browse', [
            'requests' => $requests,
            'filterSearch' => $filterSearch,
            'filterMaxRate' => $filterMaxRate,
            'currentLang' => $currentLang,
        ]);
    }

    /**
     * Fetch requests from database
     */
    private function fetchRequestsFromDatabase($filterSearch, $filterMaxRate)
    {
        // Build the query
        $query = DB::table('projects as p')
            ->join('companies as c', 'p.requester_company_id', '=', 'c.company_id')
            ->where('p.status', 'posted')
            ->select([
                'p.project_id',
                'p.title',
                'p.scope_description',
                'p.requested_duration_hours',
                'p.max_hourly_rate',
                'p.created_at',
                'c.name as requester_name',
                DB::raw("(SELECT GROUP_CONCAT(IFNULL(s.name_ar, s.name) SEPARATOR ', ') 
                         FROM project_required_skills prs 
                         JOIN skills s ON prs.skill_id = s.skill_id 
                         WHERE prs.project_id = p.project_id) AS skills_list")
            ]);

        // Apply search filter
        if (!empty($filterSearch)) {
            $searchTerm = "%{$filterSearch}%";
            $query->where(function ($q) use ($searchTerm) {
                $q->where('p.title', 'LIKE', $searchTerm)
                    ->orWhere('p.scope_description', 'LIKE', $searchTerm);
            });
        }

        // Apply max rate filter
        if (!empty($filterMaxRate)) {
            $query->where('p.max_hourly_rate', '<=', $filterMaxRate);
        }

        // Order by creation date
        $query->orderBy('p.created_at', 'desc');

        // Get results
        $requests = $query->get();

        // Filter by skills_list if search term is provided (post-query filtering)
        if (!empty($filterSearch) && $requests->isNotEmpty()) {
            $searchTerm = strtolower($filterSearch);
            $requests = $requests->filter(function ($request) use ($searchTerm) {
                $skillsList = strtolower($request->skills_list ?? '');
                return str_contains($skillsList, $searchTerm)
                    || str_contains(strtolower($request->title), $searchTerm)
                    || str_contains(strtolower($request->scope_description), $searchTerm);
            })->values();
        }

        // Format skills list for each request
        $requests = $requests->map(function ($request) {
            $request->skills_array = !empty($request->skills_list)
                ? explode(', ', $request->skills_list)
                : [];
            return $request;
        });

        return $requests;
    }

    /**
     * Get mock requests data for display when database is not ready
     */
    private function getMockRequests()
    {
        $currentLang = app()->getLocale();

        $mockData = [
            [
                'project_id' => 1,
                'title' => $currentLang === 'ar' ? 'تطوير نظام إدارة محتوى' : 'Content Management System Development',
                'scope_description' => $currentLang === 'ar'
                    ? 'نحتاج إلى مطور Laravel محترف لتطوير نظام إدارة محتوى متكامل مع واجهة إدارية حديثة. المشروع يتطلب خبرة في Laravel 10+, Vue.js, و MySQL.'
                    : 'We need a professional Laravel developer to build a comprehensive content management system with a modern admin interface. The project requires expertise in Laravel 10+, Vue.js, and MySQL.',
                'requested_duration_hours' => 120,
                'max_hourly_rate' => 150.00,
                'created_at' => now()->subDays(2),
                'requester_name' => $currentLang === 'ar' ? 'شركة التقنية المتقدمة' : 'Advanced Tech Company',
                'skills_list' => $currentLang === 'ar' ? 'Laravel, PHP, Vue.js, MySQL, REST API' : 'Laravel, PHP, Vue.js, MySQL, REST API',
            ],
            [
                'project_id' => 2,
                'title' => $currentLang === 'ar' ? 'تصميم واجهة مستخدم متجاوبة' : 'Responsive UI/UX Design',
                'scope_description' => $currentLang === 'ar'
                    ? 'مطلوب مصمم UI/UX محترف لتصميم واجهة مستخدم حديثة ومتجاوبة لتطبيق جوال. يجب أن يكون التصميم متوافقاً مع معايير Material Design.'
                    : 'We need a professional UI/UX designer to create a modern and responsive user interface for a mobile application. The design must comply with Material Design standards.',
                'requested_duration_hours' => 80,
                'max_hourly_rate' => 100.00,
                'created_at' => now()->subDays(5),
                'requester_name' => $currentLang === 'ar' ? 'شركة التطبيقات الذكية' : 'Smart Apps Company',
                'skills_list' => $currentLang === 'ar' ? 'Figma, Adobe XD, UI/UX Design, Prototyping' : 'Figma, Adobe XD, UI/UX Design, Prototyping',
            ],
            [
                'project_id' => 3,
                'title' => $currentLang === 'ar' ? 'مراجعة وتحسين أداء قاعدة البيانات' : 'Database Performance Review & Optimization',
                'scope_description' => $currentLang === 'ar'
                    ? 'نحتاج إلى خبير في قواعد البيانات لمراجعة وتحسين أداء قاعدة بيانات MySQL كبيرة الحجم. المشروع يتضمن تحليل الاستعلامات البطيئة وإنشاء الفهارس المناسبة.'
                    : 'We need a database expert to review and optimize the performance of a large MySQL database. The project involves analyzing slow queries and creating appropriate indexes.',
                'requested_duration_hours' => 40,
                'max_hourly_rate' => 200.00,
                'created_at' => now()->subDays(1),
                'requester_name' => $currentLang === 'ar' ? 'شركة البيانات الكبيرة' : 'Big Data Company',
                'skills_list' => $currentLang === 'ar' ? 'MySQL, Database Optimization, Query Analysis, Indexing' : 'MySQL, Database Optimization, Query Analysis, Indexing',
            ],
            [
                'project_id' => 4,
                'title' => $currentLang === 'ar' ? 'تطوير API للتكامل مع أنظمة خارجية' : 'API Development for External Integration',
                'scope_description' => $currentLang === 'ar'
                    ? 'مطلوب مطور لإنشاء RESTful API آمن ومستقر للتكامل مع أنظمة خارجية متعددة. يجب أن يدعم المصادقة والتفويض والوثائق الكاملة.'
                    : 'We need a developer to create a secure and stable RESTful API for integration with multiple external systems. Must support authentication, authorization, and complete documentation.',
                'requested_duration_hours' => 60,
                'max_hourly_rate' => 180.00,
                'created_at' => now()->subDays(3),
                'requester_name' => $currentLang === 'ar' ? 'شركة التكامل التقني' : 'Tech Integration Company',
                'skills_list' => $currentLang === 'ar' ? 'REST API, Node.js, JWT, Swagger, Postman' : 'REST API, Node.js, JWT, Swagger, Postman',
            ],
            [
                'project_id' => 5,
                'title' => $currentLang === 'ar' ? 'اختبار وتأكيد الجودة للواجهة الأمامية' : 'Frontend Testing & Quality Assurance',
                'scope_description' => $currentLang === 'ar'
                    ? 'نحتاج إلى مختبر برمجيات محترف لإجراء اختبارات شاملة على واجهة مستخدم React. يتضمن الاختبار الوظيفي واختبار الأداء واختبار التوافق مع المتصفحات.'
                    : 'We need a professional QA tester to perform comprehensive testing on a React user interface. Includes functional testing, performance testing, and browser compatibility testing.',
                'requested_duration_hours' => 50,
                'max_hourly_rate' => 90.00,
                'created_at' => now()->subHours(12),
                'requester_name' => $currentLang === 'ar' ? 'شركة الجودة البرمجية' : 'Software Quality Company',
                'skills_list' => $currentLang === 'ar' ? 'QA Testing, Jest, Cypress, React Testing' : 'QA Testing, Jest, Cypress, React Testing',
            ],
        ];

        // Convert to collection and format skills
        $requests = collect($mockData)->map(function ($item) {
            $item = (object) $item;
            $item->skills_array = !empty($item->skills_list)
                ? explode(', ', $item->skills_list)
                : [];
            return $item;
        });

        return $requests;
    }
}
