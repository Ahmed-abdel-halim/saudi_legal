<!DOCTYPE html>
<html lang="{{ $lang ?? 'ar' }}" dir="{{ ($lang ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'رمز التحقق' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Arial,Tahoma,sans-serif;">

    <!-- Outer Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:40px 16px;">
        <tr>
            <td align="center">

                <!-- Email Card -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.09);">

                    <!-- ===== HEADER ===== -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0f172a 0%,#1a3a6c 100%);padding:28px 40px;text-align:center;">
                            {{-- Modified by Ahmedabdelhalim --}}
                            @if(!empty($logoPath) && file_exists($logoPath))
                                <img src="{{ $message->embed($logoPath) }}"
                                     alt="Radiif"
                                     width="64"
                                     height="64"
                                     style="display:block;margin:0 auto;border-radius:50%;border:3px solid rgba(255,255,255,0.2);">
                            @else
                                <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.15);margin:0 auto;line-height:64px;text-align:center;font-size:28px;font-weight:900;color:#fff;display:block;">R</div>
                            @endif
                            <p style="margin:12px 0 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:1px;">Radiif</p>
                            <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,0.55);letter-spacing:2px;text-transform:uppercase;">
                                @if(($lang ?? 'ar') === 'ar') منصة رديف @else Platform @endif
                            </p>
                        </td>
                    </tr>

                    <!-- ===== BODY ===== -->
                    <tr>
                        <td style="padding:40px 40px 28px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 18px;font-size:20px;font-weight:700;color:#0f172a;">
                                @if(($lang ?? 'ar') === 'ar')
                                    مرحباً {{ $userName }}،
                                @else
                                    Hello {{ $userName }},
                                @endif
                            </p>

                            <!-- Intro Text -->
                            <p style="margin:0 0 28px;font-size:15px;line-height:1.7;color:#475569;">
                                @if(($lang ?? 'ar') === 'ar')
                                    لقد طلبت التحقق من بريدك الإلكتروني. استخدم رمز التحقق أدناه لإتمام العملية:
                                @else
                                    You requested to verify your email address. Use the code below to complete your verification:
                                @endif
                            </p>

                            <!-- OTP Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                                <tr>
                                    <td align="center" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #bbf7d0;border-radius:14px;padding:28px 20px;">
                                        <p style="margin:0 0 8px;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#15803d;font-weight:600;">
                                            @if(($lang ?? 'ar') === 'ar') رمز التحقق @else Verification Code @endif
                                        </p>
                                        <p style="margin:0;font-size:44px;font-weight:900;letter-spacing:12px;color:#166534;font-family:'Courier New',Courier,monospace;">
                                            {{ $otpCode }}
                                        </p>
                                        <p style="margin:10px 0 0;font-size:12px;color:#16a34a;">
                                            ⏱
                                            @if(($lang ?? 'ar') === 'ar')
                                                صالح لمدة <strong>10 دقائق</strong> فقط
                                            @else
                                                Valid for <strong>10 minutes</strong> only
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                                <tr>
                                    <td style="border-top:1px solid #e2e8f0;font-size:0;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Security Notice -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fffbeb;border-radius:10px;border:1px solid #fde68a;margin-bottom:8px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                                            🔒
                                            @if(($lang ?? 'ar') === 'ar')
                                                إذا لم تطلب هذا الرمز، يمكنك تجاهل هذا البريد الإلكتروني بأمان. لن يتم إجراء أي تغييرات على حسابك.
                                            @else
                                                If you did not request this code, you can safely ignore this email. No changes will be made to your account.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- ===== FOOTER ===== -->
                    <tr>
                        <td style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
                                &copy; {{ date('Y') }} Radiif.
                                @if(($lang ?? 'ar') === 'ar') جميع الحقوق محفوظة. @else All rights reserved. @endif
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- / Email Card -->

            </td>
        </tr>
    </table>

</body>
</html>
