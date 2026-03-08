<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Login Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.two-factor-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}