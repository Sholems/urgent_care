<?php

namespace App\Mail;

use App\Models\AppUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AppUser $user)
    {
    }

    public function build(): self
    {
        return $this->subject('Welcome to Lizelsundria Urgent Care')
            ->view('emails.welcome')
            ->with([
                'user' => $this->user,
            ]);
    }
}

