<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CustomerOrderMail extends Mailable
{
    public function __construct(public array $delivery) {}

    public function build(): static
    {
        return $this->subject($this->delivery['subject'])->view('public-orders.email', ['delivery' => $this->delivery]);
    }
}
