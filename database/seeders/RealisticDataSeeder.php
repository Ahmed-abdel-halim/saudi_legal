<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\ExpertService;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RealisticDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to truncate tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        ExpertService::truncate();
        Project::truncate();
        Company::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Law Companies & Services
        $lawCompanies = [
            [
                'name' => 'مجموعة الزامل للمحاماة والاستشارات القانونية',
                'industry' => 'law',
                'services' => [
                    ['title' => 'تدقيق البيانات والوثائق القانونية', 'category' => 'Auditing', 'description' => 'خدمة متخصصة في تدقيق البيانات القانونية ومراجعة العقود لضمان الامتثال للأنظمة السعودية.'],
                    ['title' => 'تمثيل قضائي في القضايا التجارية', 'category' => 'Consulting', 'description' => 'تمثيل الشركات في المنازعات التجارية أمام المحاكم.'],
                ]
            ],
            [
                'name' => 'مكتب الشريف للمحاماة',
                'industry' => 'law',
                'services' => [
                    ['title' => 'مراجعة وتدقيق البيانات العقارية', 'category' => 'Auditing', 'description' => 'التأكد من صحة الصكوك والبيانات القانونية للعقارات.'],
                ]
            ],
            [
                'name' => 'شركة العدالة للمحاماة',
                'industry' => 'law',
                'services' => [
                    ['title' => 'تدقيق العقود والامتثال العمالي', 'category' => 'Auditing', 'description' => 'مراجعة عقود الموظفين والتأكد من مطابقتها لنظام العمل.'],
                ]
            ],
        ];

        // 2. Tech Companies
        $techCompanies = [
            [
                'name' => 'حلول التقنية المتقدمة',
                'industry' => 'tech',
                'services' => [
                    ['title' => 'تطوير تطبيقات الجوال (iOS & Android)', 'category' => 'Tech', 'description' => 'بناء تطبيقات متكاملة وسهلة الاستخدام.'],
                    ['title' => 'تحليل البيانات الضخمة وتدقيقها', 'category' => 'Auditing', 'description' => 'تحويل البيانات الخام إلى رؤى تجارية دقيقة.'],
                ]
            ],
            [
                'name' => 'شركة سحابة الرياض للبرمجيات',
                'industry' => 'tech',
                'services' => [
                    ['title' => 'تطوير أنظمة الويب المؤسسية', 'category' => 'Tech', 'description' => 'أنظمة متطورة لإدارة الموارد والعمليات.'],
                ]
            ],
        ];

        // 3. Engineering & Medical
        $otherCompanies = [
            [
                'name' => 'المعماريون العرب للاستشارات الهندسية',
                'industry' => 'other',
                'services' => [
                    ['title' => 'تصميم معماري وتدقيق المخططات', 'category' => 'Auditing', 'description' => 'تصاميم حديثة ومخططات هندسية دقيقة.'],
                ]
            ],
            [
                'name' => 'مستشفى النخبة التخصصي',
                'industry' => 'healthcare',
                'services' => [
                    ['title' => 'تدقيق التقارير الطبية والامتثال الصحي', 'category' => 'Auditing', 'description' => 'مراجعة التقارير الطبية لضمان جودتها وموافقتها للمعايير الصحية.'],
                ]
            ],
        ];

        // 4. Marketing Companies
        $marketingCompanies = [
            [
                'name' => 'وكالة إبداع للتسويق الرقمي',
                'industry' => 'marketing',
                'services' => [
                    ['title' => 'إدارة حملات التواصل الاجتماعي', 'category' => 'Marketing', 'description' => 'نمو ملحوظ وتفاعل حقيقي مع العلامة التجارية.'],
                    ['title' => 'تدقيق المحتوى التسويقي والبراندينج', 'category' => 'Auditing', 'description' => 'التأكد من جودة المحتوى ومناسبته للهوية.'],
                ]
            ],
        ];

        $allSupplierData = array_merge($lawCompanies, $techCompanies, $otherCompanies, $marketingCompanies);

        foreach ($allSupplierData as $idx => $cData) {
            $company = Company::create([
                'name' => $cData['name'],
                'industry' => $cData['industry'],
                'size' => 'medium',
                'is_supplier' => true,
                'is_verified_provider' => true,
                'cr_number' => '1010' . rand(100000, 999999),
            ]);

            $expert = User::create([
                'name' => 'المدير التنفيذي لـ ' . $cData['name'],
                'email' => 'expert' . $idx . '@' . ($idx % 2 == 0 ? 'gmail.com' : 'outlook.com'),
                'password' => Hash::make('password'),
                'company_id' => $company->company_id,
                'role' => 'expert',
                'expert_domain' => $cData['industry'],
                'is_active' => true,
            ]);

            foreach ($cData['services'] as $sData) {
                ExpertService::create([
                    'expert_id' => $expert->id,
                    'title' => $sData['title'],
                    'category' => $sData['category'],
                    'description' => $sData['description'],
                    'price' => rand(100, 1000),
                    'delivery_days' => rand(1, 10),
                    'is_active' => true,
                ]);
            }
        }

        // 5. Requester Companies
        $requesterFinance = Company::create([
            'name' => 'شركة الاستثمار الكبرى',
            'industry' => 'finance',
            'size' => 'large',
            'is_requester' => true,
            'is_verified_provider' => true,
            'cr_number' => '1010' . rand(100000, 999999),
        ]);

        $requesterLaw = Company::create([
            'name' => 'مكتب الاستشارات الدولية',
            'industry' => 'law',
            'size' => 'medium',
            'is_requester' => true,
            'is_verified_provider' => true,
            'cr_number' => '1010' . rand(100000, 999999),
        ]);

        $sampleProjects = [
            [
                'title' => 'مطلوب محامي لتدقيق بيانات عقود توريد',
                'scope_description' => 'نحتاج لمراجعة وتدقيق بيانات 50 عقد توريد للتأكد من توافقها مع نظام المشتريات الجديد.',
                'budget' => 2500,
                'requester_id' => $requesterLaw->company_id,
            ],
            [
                'title' => 'مشروع تطوير متجر إلكتروني متكامل',
                'scope_description' => 'تصميم وبرمجة متجر إلكتروني يدعم الدفع والربط مع شركات الشحن.',
                'budget' => 15000,
                'requester_id' => $requesterFinance->company_id,
            ],
            [
                'title' => 'تحليل وتدقيق البيانات المالية السنوية',
                'scope_description' => 'مراجعة القوائم المالية للسنة الماضية وإصدار تقرير تدقيق شامل.',
                'budget' => 5000,
                'requester_id' => $requesterFinance->company_id,
            ],
            [
                'title' => 'تدقيق المخططات الهندسية لبرج سكني',
                'scope_description' => 'مراجعة المخططات الإنشائية والمعمارية لبرج مكون من 20 دور.',
                'budget' => 8000,
                'requester_id' => $requesterFinance->company_id,
            ],
        ];

        foreach ($sampleProjects as $pData) {
            Project::create([
                'title' => $pData['title'],
                'scope_description' => $pData['scope_description'],
                'budget' => $pData['budget'],
                'requested_duration_hours' => rand(10, 100),
                'max_hourly_rate' => rand(50, 200),
                'status' => 'posted', 
                'requester_company_id' => $pData['requester_id'],
            ]);
        }
    }
}
