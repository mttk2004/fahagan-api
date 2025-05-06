<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $token;

    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build(): ForgotPasswordMail
    {
        return $this->view('emails.forgot_password')
            ->with([
                'url' => url('reset-password?token='.$this->token.'&email='.$this->email),
            ]);
    }
}
