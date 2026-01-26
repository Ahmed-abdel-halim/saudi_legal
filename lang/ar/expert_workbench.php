<?php

return [
    'title' => 'منصة العمل',
    'subtitle' => 'منصة التدقيق السيادية للبيانات',
    'back_to_dashboard' => 'العودة للوحة التحكم',
    
    // Task Queue
    'task_queue' => 'قائمة المهام',
    'pending_tasks' => 'المهام المعلقة',
    'no_tasks' => 'لا توجد مهام متاحة',
    'no_tasks_desc' => 'تم إنجاز جميع المهام! تحقق لاحقاً للمهام الجديدة.',
    'tasks_available' => 'مهمة متاحة',
    'start_task' => 'بدء المهمة',
    
    // Task Interface
    'task_id' => 'رقم المهمة',
    'task_type' => 'نوع المهمة',
    'task_data' => 'البيانات للمراجعة',
    'original_data' => 'البيانات الأصلية',
    'ai_suggestion' => 'اقتراح الذكاء الاصطناعي',
    'your_correction' => 'تصحيحك',
    
    // Task Types
    'task_types' => [
        'text_correction' => 'تصحيح النصوص',
        'data_validation' => 'التحقق من البيانات',
        'classification' => 'التصنيف',
        'translation' => 'الترجمة',
        'sentiment_analysis' => 'تحليل المشاعر',
    ],
    
    // Actions
    'approve' => 'موافقة',
    'reject' => 'رفض',
    'correct' => 'تصحيح وإرسال',
    'skip_task' => 'تخطي المهمة',
    'next_task' => 'المهمة التالية',
    'submit_correction' => 'إرسال التصحيح',
    
    // Correction Form
    'correction_notes' => 'ملاحظات التصحيح',
    'correction_notes_placeholder' => 'اشرح تصحيحك...',
    'confidence_level' => 'مستوى الثقة',
    'confidence_levels' => [
        'low' => 'منخفض',
        'medium' => 'متوسط',
        'high' => 'عالي',
        'certain' => 'مؤكد',
    ],
    
    // Statistics
    'tasks_completed_today' => 'المهام المنجزة اليوم',
    'total_tasks_completed' => 'إجمالي المهام المنجزة',
    'accuracy_rate' => 'معدل الدقة',
    'earnings_today' => 'الأرباح اليوم',
    'avg_time_per_task' => 'متوسط الوقت لكل مهمة',
    'minutes' => 'دقيقة',
    'seconds' => 'ثانية',
    
    // Progress
    'current_session' => 'الجلسة الحالية',
    'session_progress' => 'تقدم الجلسة',
    'tasks_in_session' => 'مهمة في هذه الجلسة',
    'take_break' => 'أخذ استراحة',
    'end_session' => 'إنهاء الجلسة',
    
    // Messages
    'success_submit' => 'تم إرسال المهمة بنجاح. تم كسب +5 ريال!',
    'error_submit' => 'فشل إرسال المهمة. يرجى المحاولة مرة أخرى.',
    'session_ended' => 'انتهت الجلسة. عمل رائع!',
    'break_reminder' => 'لقد كنت تعمل لفترة طويلة. فكر في أخذ استراحة.',
    
    // Guidelines
    'guidelines' => 'الإرشادات',
    'guidelines_title' => 'إرشادات إنجاز المهام',
    'guidelines_list' => [
        'راجع اقتراح الذكاء الاصطناعي بعناية',
        'قدم تصحيحات دقيقة',
        'أضف ملاحظات لشرح تغييراتك',
        'تخطى المهام إذا لم تكن متأكداً',
        'حافظ على معايير الجودة العالية',
    ],
    
    // Quality Score
    'quality_score' => 'درجة الجودة',
    'excellent' => 'ممتاز',
    'good' => 'جيد',
    'needs_improvement' => 'يحتاج تحسين',
];
