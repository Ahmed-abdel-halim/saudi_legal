<?php

namespace App\Notifications;

use App\Mail\OtpVerificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class SendOtpVerification extends Notification
{
    use Queueable;

    protected string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        $lang    = app()->getLocale();
        $appName = config('app.name', 'Radiif');

        $subject = $lang === 'ar'
            ? "رمز التحقق من البريد الإلكتروني - {$appName}"
            : "Email Verification Code - {$appName}";

        return new OtpVerificationMail($this->otp, $notifiable->name, $lang, $subject);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}

