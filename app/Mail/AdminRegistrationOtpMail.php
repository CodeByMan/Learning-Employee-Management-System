<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminRegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $otp,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your Employee LEMS registration code')
            ->view('emails.registration-otp');
    }
}
