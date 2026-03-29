<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('إعادة تعيين كلمة المرور') }}</title>
</head>
<body style="font-family: 'Tajawal', Tahoma, sans-serif; background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
        <!-- Card -->
        <div style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); overflow: hidden;">
            
            <!-- Header -->
            <div style="background-color: #1c2a4f; padding: 40px 30px; text-align: center;">
                <img src="{{ $message->embed(public_path('assets/images/logo.png')) }}" alt="Radiif" style="height: 80px; width: 80px; object-fit: contain; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); margin-bottom: 15px; padding: 5px;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px;">
                    Radiif
                </h1>
                <p style="color: #94a3b8; margin: 8px 0 0 0; font-size: 14px;">منصة رديف</p>
            </div>

            <!-- Content -->
            <div style="padding: 40px 30px;">
                <h2 style="color: #1e293b; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 20px;">
                    {{ app()->getLocale() === 'ar' ? 'مرحباً بك' : 'Hello' }},
                </h2>

                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                    {{ app()->getLocale() === 'ar' 
                        ? 'لقد تلقيت هذه الرسالة لأننا تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك.'
                        : 'You are receiving this email because we received a password reset request for your account.' 
                    }}
                </p>

                <!-- Action Button -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <a href="{{ $url }}" 
                       style="display: inline-block; background-color: #4f46e5; color: #ffffff; font-weight: 700; font-size: 16px; text-decoration: none; padding: 14px 32px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);">
                        {{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Password' }}
                    </a>
                </div>

                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                    {{ app()->getLocale() === 'ar' 
                        ? 'هذا الرابط صالح لمدة 60 دقيقة فقط. إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء آخر.'
                        : 'This password reset link will expire in 60 minutes. If you did not request a password reset, no further action is required.'
                    }}
                </p>

                <!-- Divider -->
                <div style="border-top: 1px solid #e2e8f0; margin: 30px 0;"></div>

                <p style="color: #94a3b8; font-size: 12px; line-height: 1.5; margin: 0;">
                    {{ app()->getLocale() === 'ar' 
                        ? 'إذا كنت تواجه مشكلة في النقر على زر "إعادة تعيين كلمة المرور"، انسخ ولصق الرابط أدناه في متصفحك:'
                        : 'If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:'
                    }}
                    <br><br>
                    <a href="{{ $url }}" style="color: #4f46e5; word-break: break-all;">{{ $url }}</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #94a3b8; font-size: 13px;">
                &copy; {{ date('Y') }} Radiif. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
            </p>
        </div>
    </div>
</body>
</html>
