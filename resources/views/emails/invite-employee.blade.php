<!DOCTYPE html>
<html lang="{{ $lang ?? 'ar' }}" dir="{{ ($lang ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ ($lang ?? 'ar') === 'ar' ? 'دعوة للانضمام إلى فريق رديف' : 'Invitation to join Radiif team' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Arial,Tahoma,sans-serif;">

    <!-- Outer Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:400px 16px;">
        <tr>
            <td align="center">

                <!-- Email Card -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.09);">

                    <!-- ===== HEADER ===== -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0f172a 0%,#1a3a6c 100%);padding:28px 40px;text-align:center;">
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
                                    مرحباً {{ $user->name }}،
                                @else
                                    Hello {{ $user->name }},
                                @endif
                            </p>

                            <!-- Intro Text -->
                            <p style="margin:0 0 28px;font-size:15px;line-height:1.7;color:#475569;">
                                @if(($lang ?? 'ar') === 'ar')
                                    لقد تمت دعوتك للانضمام إلى فريق العمل في <strong>{{ $user->company->name ?? 'رديف' }}</strong>.
                                    نحن متحمسون لانضمامك إلينا للمساهمة في تطوير المنصة وتحقيق رؤيتنا.
                                @else
                                    You have been invited to join the team at <strong>{{ $user->company->name ?? 'Radiif' }}</strong>.
                                    We are excited to have you join us and contribute to our platform's growth.
                                @endif
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $activationUrl }}" style="background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#ffffff;text-decoration:none;padding:16px 32px;border-radius:12px;font-weight:700;font-size:16px;display:inline-block;box-shadow:0 4px 12px rgba(37,99,235,0.25);">
                                            @if(($lang ?? 'ar') === 'ar') قبول الدعوة وتفعيل الحساب @else Accept Invitation & Activate @endif
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                                <tr>
                                    <td style="border-top:1px solid #e2e8f0;font-size:0;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Link Fallback -->
                            <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;text-align:center;">
                                @if(($lang ?? 'ar') === 'ar')
                                    إذا كان الزر لا يعمل، يمكنك نسخ الرابط التالي ولصقه في متصفحك:
                                @else
                                    If the button doesn't work, copy and paste this link into your browser:
                                @endif
                            </p>
                            <p style="margin:8px 0 0;font-size:12px;color:#3b82f6;word-break:break-all;text-align:center;">
                                {{ $activationUrl }}
                            </p>

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
