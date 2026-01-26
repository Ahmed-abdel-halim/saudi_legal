<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to Radiif</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #047857; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Radiif</h1>
        </div>
        <div style="padding: 30px;">
            <h2 style="margin-top: 0; color: #047857;">Hello {{ $user->name }},</h2>
            <p>You have been invited to join the team at <strong>{{ $user->company->name ?? 'Radiif' }}</strong>.</p>
            <p>To get started, please accept this invitation and complete your profile setup.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $activationUrl }}" style="background-color: #047857; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">Accept Invitation</a>
            </div>
            
            <p style="font-size: 14px; color: #666;">If the button above doesn't work, copy and paste this link into your browser:</p>
            <p style="font-size: 13px; color: #888; word-break: break-all;">{{ $activationUrl }}</p>
        </div>
        <div style="background-color: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;">
            &copy; {{ date('Y') }} Radiif. All rights reserved.
        </div>
    </div>
</body>
</html>
