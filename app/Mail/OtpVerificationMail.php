<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $userName;
    public string $lang;

    public function __construct(string $otpCode, string $userName, string $lang, string $subject)
    {
        $this->otpCode  = $otpCode;
        $this->userName = $userName;
        $this->lang     = $lang;
        $this->subject  = $subject;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        // Modified by Ahmedabdelhalim
        $logoPath = public_path('images/icon.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/favicon-32x32.png');
        }

        return new Content(
            view: 'emails.otp-verification',
            with: [
                'otpCode'  => $this->otpCode,
                'userName' => $this->userName,
                'lang'     => $this->lang,
                'subject'  => $this->subject,
                'logoPath' => $logoPath,
            ]
        );
    }
}
