<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MarketingMail extends Mailable
{
    public function __construct(public array $content) {}

    public function build(): static
    {
        return $this->subject($this->content['subject'])->view('marketing.email', ['content' => $this->content])->text('marketing.email-text', ['content' => $this->content]);
    }
}
