<?php

return [
    'title' => 'إعدادات الخبير',
    'subtitle' => 'إدارة إعدادات الحساب والتفضيلات',
    'back_to_dashboard' => 'العودة للوحة التحكم',
    
    // Account Settings
    'account_settings' => 'إعدادات الحساب',
    'profile_settings' => 'إعدادات الملف الشخصي',
    'full_name' => 'الاسم الكامل',
    'email' => 'البريد الإلكتروني',
    'phone' => 'رقم الهاتف',
    'job_title' => 'المسمى الوظيفي',
    'bio' => 'النبذة الشخصية',
    'profile_picture' => 'الصورة الشخصية',
    'change_picture' => 'تغيير الصورة',
    
    // Security Settings
    'security_settings' => 'إعدادات الأمان',
    'change_password' => 'تغيير كلمة المرور',
    'current_password' => 'كلمة المرور الحالية',
    'new_password' => 'كلمة المرور الجديدة',
    'confirm_password' => 'تأكيد كلمة المرور الجديدة',
    'two_factor_auth' => 'المصادقة الثنائية',
    'enable_2fa' => 'تفعيل المصادقة الثنائية',
    'disable_2fa' => 'تعطيل المصادقة الثنائية',
    '2fa_status' => 'حالة المصادقة الثنائية',
    'enabled' => 'مفعّل',
    'disabled' => 'معطّل',
    
    // Notification Settings
    'notification_settings' => 'إعدادات الإشعارات',
    'email_notifications' => 'إشعارات البريد الإلكتروني',
    'new_task_notification' => 'مهمة جديدة متاحة',
    'payment_notification' => 'استلام دفعة',
    'message_notification' => 'رسالة جديدة',
    'system_notification' => 'تحديثات النظام',
    'push_notifications' => 'الإشعارات الفورية',
    'enable_push' => 'تفعيل الإشعارات الفورية',
    
    // Payment Settings
    'payment_settings' => 'إعدادات الدفع',
    'payment_method' => 'طريقة الدفع',
    'bank_account' => 'الحساب البنكي',
    'account_holder_name' => 'اسم صاحب الحساب',
    'bank_name' => 'اسم البنك',
    'iban' => 'رقم الآيبان',
    'swift_code' => 'رمز SWIFT/BIC',
    'minimum_payout' => 'الحد الأدنى للسحب',
    'auto_payout' => 'السحب التلقائي',
    'enable_auto_payout' => 'تفعيل السحب التلقائي عند الوصول للحد الأدنى',
    
    // Privacy Settings
    'privacy_settings' => 'إعدادات الخصوصية',
    'profile_visibility' => 'ظهور الملف الشخصي',
    'visibility_options' => [
        'public' => 'عام - مرئي للجميع',
        'registered' => 'المستخدمين المسجلين فقط',
        'private' => 'خاص - أنا فقط',
    ],
    'show_email' => 'إظهار البريد الإلكتروني في الملف الشخصي',
    'show_phone' => 'إظهار الهاتف في الملف الشخصي',
    'allow_messages' => 'السماح بالرسائل المباشرة',
    
    // Language & Region
    'language_region' => 'اللغة والمنطقة',
    'language' => 'اللغة',
    'languages' => [
        'en' => 'English',
        'ar' => 'العربية',
    ],
    'timezone' => 'المنطقة الزمنية',
    'currency' => 'العملة',
    
    // Actions
    'save_changes' => 'حفظ التغييرات',
    'cancel' => 'إلغاء',
    'reset' => 'إعادة تعيين',
    
    // Messages
    'success_update' => 'تم تحديث الإعدادات بنجاح.',
    'error_update' => 'فشل تحديث الإعدادات.',
    'password_changed' => 'تم تغيير كلمة المرور بنجاح.',
    'password_error' => 'فشل تغيير كلمة المرور.',
    'invalid_current_password' => 'كلمة المرور الحالية غير صحيحة.',
    'password_mismatch' => 'كلمات المرور غير متطابقة.',
    
    // Account Actions
    'account_actions' => 'إجراءات الحساب',
    'deactivate_account' => 'تعطيل الحساب',
    'deactivate_desc' => 'تعطيل حسابك مؤقتاً. يمكنك إعادة تفعيله في أي وقت.',
    'delete_account' => 'حذف الحساب',
    'delete_desc' => 'حذف حسابك وجميع البيانات نهائياً. لا يمكن التراجع عن هذا الإجراء.',
    'confirm_deactivate' => 'هل أنت متأكد من تعطيل حسابك؟',
    'confirm_delete' => 'هل أنت متأكد من حذف حسابك نهائياً؟',
];
