<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $email
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Reset Kata Sandi — Arsip Grogol',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-reset-password',
            with: [
                'otp' => $this->otp,
                'email' => $this->email,
            ],
        );
    }
}
