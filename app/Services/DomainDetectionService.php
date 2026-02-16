<?php

namespace App\Services;

class DomainDetectionService
{
    /**
     * Domain keywords for intelligent detection
     * Supports Arabic and English
     */
    private array $keywords = [
        'medicine' => [
            // Arabic medical terms
            'طبيب', 'طبية', 'مرض', 'مريض', 'علاج', 'دواء', 'أدوية', 'مستشفى', 
            'صحة', 'صحي', 'ألم', 'وجع', 'عيادة', 'جراحة', 'عملية', 'تشخيص',
            'أعراض', 'حمى', 'سكري', 'ضغط', 'قلب', 'كلى', 'كبد', 'ركبة', 'ظهر',
            'رأس', 'صداع', 'معدة', 'أمعاء', 'سرطان', 'فيروس', 'عدوى', 'لقاح',
            'تطعيم', 'حساسية', 'التهاب', 'كسر', 'جرح', 'نزيف', 'حمل', 'ولادة',
            // English medical terms
            'doctor', 'medical', 'disease', 'patient', 'treatment', 'medicine',
            'medication', 'hospital', 'health', 'pain', 'clinic', 'surgery',
            'diagnosis', 'symptoms', 'fever', 'diabetes', 'pressure', 'heart',
            'kidney', 'liver', 'knee', 'back', 'headache', 'stomach', 'cancer',
            'virus', 'infection', 'vaccine', 'allergy', 'inflammation', 'fracture'
        ],
        
        'law' => [
            // Arabic law terms
            'قانون', 'قانوني', 'محكمة', 'قاضي', 'دعوى', 'قضية', 'حق', 'حقوق',
            'عقد', 'محامي', 'محاماة', 'تقاضي', 'استئناف', 'حكم', 'قرار', 'نزاع',
            'تحكيم', 'شهادة', 'شاهد', 'دليل', 'إثبات', 'براءة', 'إدانة', 'جريمة',
            'جنائي', 'مدني', 'تجاري', 'عمل', 'عقوبة', 'سجن', 'غرامة', 'تعويض',
            'ميراث', 'وصية', 'طلاق', 'نفقة', 'حضانة', 'ملكية', 'إيجار', 'بيع',
            // English law terms
            'law', 'legal', 'court', 'judge', 'lawsuit', 'case', 'right', 'rights',
            'contract', 'lawyer', 'attorney', 'litigation', 'appeal', 'judgment',
            'verdict', 'dispute', 'arbitration', 'testimony', 'witness', 'evidence',
            'proof', 'crime', 'criminal', 'civil', 'commercial', 'penalty', 'fine',
            'compensation', 'inheritance', 'divorce', 'custody', 'property', 'rent'
        ],
        
        'engineering' => [
            // Arabic engineering terms
            'هندسة', 'هندسي', 'مهندس', 'تصميم', 'بناء', 'إنشاء', 'معماري', 'عمارة',
            'جسر', 'طريق', 'مبنى', 'عمارة', 'خرسانة', 'حديد', 'صلب', 'أساس',
            'حمل', 'إجهاد', 'قوة', 'ضغط', 'شد', 'انحناء', 'تصدع', 'تشقق',
            'كهرباء', 'كهربائي', 'دائرة', 'تيار', 'جهد', 'محرك', 'مولد', 'محول',
            'ميكانيكا', 'ميكانيكي', 'آلة', 'محرك', 'تروس', 'احتكاك', 'سرعة',
            'برمجة', 'برمجي', 'كود', 'خوارزمية', 'شبكة', 'سيرفر', 'قاعدة بيانات',
            // English engineering terms
            'engineering', 'engineer', 'design', 'construction', 'building', 'architecture',
            'bridge', 'road', 'concrete', 'steel', 'foundation', 'load', 'stress',
            'force', 'pressure', 'tension', 'bending', 'crack', 'electrical', 'circuit',
            'current', 'voltage', 'motor', 'generator', 'transformer', 'mechanical',
            'machine', 'engine', 'gear', 'friction', 'speed', 'programming', 'code',
            'algorithm', 'network', 'server', 'database', 'structural', 'civil'
        ],
        
        'business' => [
            // Arabic business terms
            'تجارة', 'تجاري', 'شركة', 'مؤسسة', 'أعمال', 'مشروع', 'استثمار', 'رأس مال',
            'ربح', 'خسارة', 'مبيعات', 'تسويق', 'إعلان', 'عميل', 'زبون', 'سوق',
            'منافسة', 'إدارة', 'مدير', 'موظف', 'راتب', 'أجر', 'ميزانية', 'محاسبة',
            'فاتورة', 'ضريبة', 'بنك', 'قرض', 'فائدة', 'سهم', 'بورصة', 'تداول',
            // English business terms
            'business', 'commercial', 'company', 'corporation', 'enterprise', 'project',
            'investment', 'capital', 'profit', 'loss', 'sales', 'marketing', 'advertising',
            'client', 'customer', 'market', 'competition', 'management', 'manager',
            'employee', 'salary', 'wage', 'budget', 'accounting', 'invoice', 'tax',
            'bank', 'loan', 'interest', 'stock', 'trading', 'finance'
        ],
        
        'education' => [
            // Arabic education terms
            'تعليم', 'تعليمي', 'مدرسة', 'جامعة', 'كلية', 'طالب', 'معلم', 'أستاذ',
            'درس', 'محاضرة', 'امتحان', 'اختبار', 'واجب', 'بحث', 'رسالة', 'دراسة',
            'شهادة', 'دبلوم', 'بكالوريوس', 'ماجستير', 'دكتوراه', 'منهج', 'مقرر',
            // English education terms
            'education', 'educational', 'school', 'university', 'college', 'student',
            'teacher', 'professor', 'lesson', 'lecture', 'exam', 'test', 'homework',
            'research', 'thesis', 'study', 'certificate', 'diploma', 'bachelor',
            'master', 'doctorate', 'curriculum', 'course'
        ]
    ];

    /**
     * Detect domain from text content
     * 
     * @param string $text The text to analyze
     * @return string|null The detected domain or null if no clear match
     */
    public function detectDomain(string $text): ?string
    {
        $text = mb_strtolower($text);
        $scores = [];
        
        // Calculate score for each domain
        foreach ($this->keywords as $domain => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower($keyword);
                // Use mb_strpos for proper Arabic support
                if (mb_strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            $scores[$domain] = $score;
        }
        
        // Get domain with highest score
        $maxScore = max($scores);
        
        // Only return domain if we have at least 1 keyword match
        if ($maxScore > 0) {
            return array_search($maxScore, $scores);
        }
        
        // No clear domain detected
        return null;
    }

    /**
     * Get all supported domains
     * 
     * @return array
     */
    public function getSupportedDomains(): array
    {
        return array_keys($this->keywords);
    }

    /**
     * Add custom keywords for a domain
     * 
     * @param string $domain
     * @param array $keywords
     * @return void
     */
    public function addKeywords(string $domain, array $keywords): void
    {
        if (!isset($this->keywords[$domain])) {
            $this->keywords[$domain] = [];
        }
        
        $this->keywords[$domain] = array_merge($this->keywords[$domain], $keywords);
    }
}
