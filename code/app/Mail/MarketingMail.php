<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MarketingMail extends Mailable
{
    public function __construct(public array $content) {}

    public function build(): static
    {
        if ($id = $this->content['fidelity_pdf_card_id'] ?? null) {
            if ($card = \App\Models\FidelityCard::find($id)) {
                $this->attachData(app(\App\Services\Fidelity\FidelityPdf::class)->render($card), 'fidelity-card.pdf', ['mime' => 'application/pdf']);
            }
        }

        return $this->subject($this->content['subject'])->view('marketing.email', ['content' => $this->content])->text('marketing.email-text', ['content' => $this->content]);
    }
}
