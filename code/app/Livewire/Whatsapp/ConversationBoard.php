<?php

namespace App\Livewire\Whatsapp;

use Livewire\Component;
use App\Models\WhatsappConversation;
use App\Services\Whatsapp\WhatsappConversationService;

class ConversationBoard extends Component
{
    public $conversations;
    public $currentConversationId;
    public $messageText = '';

    protected $rules = [
        'messageText' => 'required|string|max:4000',
    ];

    public function mount(): void
    {
        $this->loadConversations();

        if ($this->conversations->isNotEmpty()) {
            $this->currentConversationId = $this->conversations->first()->id;
        }
    }

    public function loadConversations(): void
    {
        $this->conversations = WhatsappConversation::orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->currentConversationId = $conversationId;
    }

    // Proprietà computed: $this->currentConversation nel blade
    public function getCurrentConversationProperty()
    {
        if (! $this->currentConversationId) {
            return null;
        }

        // puoi anche evitare il with e fidarti dell’ordine definito nella relazione
        return WhatsappConversation::with('messages')
            ->find($this->currentConversationId);
    }


    public function sendMessage(WhatsappConversationService $service): void
    {
        $this->validate();

        if (! $this->currentConversationId) {
            return;
        }

        $conversation = WhatsappConversation::findOrFail($this->currentConversationId);

        $service->sendOutgoingMessage($conversation, $this->messageText);

        $this->messageText = '';

        // Ricarica conversazioni e conversazione corrente
        $this->loadConversations();
    }

    public function render()
    {
        return view('livewire.whatsapp.conversation-board');
    }
}
