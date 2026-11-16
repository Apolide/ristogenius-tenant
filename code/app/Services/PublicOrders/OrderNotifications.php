<?php

namespace App\Services\PublicOrders;

use App\Models\Customer;
use App\Models\MessageOutbox;
use App\Models\Order;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;

class OrderNotifications
{
    public function record(string $key, string $aggregateId, string $email, string $name, string $subject, string $message, ?string $url = null, string $language = 'it', array $context = []): void
    {
        MessageOutbox::firstOrCreate(['deduplication_key' => $key], [
            'event_type' => $key, 'aggregate_type' => 'customer_order', 'aggregate_id' => $aggregateId, 'status' => 'pending', 'attempts' => 0, 'available_at' => now(),
            'payload' => ['message_case' => 'customer_order', 'deliveries' => [['recipient' => ['email' => $email], 'language' => $language, 'name' => $name, 'subject' => $subject, 'message' => $message, 'url' => $url] + $context]],
        ]);
    }

    public function proposalSent(\App\Models\CustomerOrderProposal $proposal, string $token): void
    {
        $order = $proposal->order;
        $url = route('public-orders.proposal', ['token' => $token]);
        $this->record('proposal:'.$proposal->id.':sent', $order->id, $order->customer_email, $order->customer_name ?? '',
            'Proposta di modifica · '.$order->reference, $proposal->message, $url, $order->customer?->lang ?? 'it', [
                'summary' => $proposal->payload['summary'], 'previous_summary' => $proposal->payload['before'],
                'accept_url' => $url.'?choice=accept', 'reject_url' => $url.'?choice=reject',
                'expires_at' => $proposal->expires_at->format('d/m/Y H:i'),
            ]);
    }

    public function proposalResponded(\App\Models\CustomerOrderProposal $proposal, Order $order): void
    {
        $text = match ($proposal->status) {
            'accepted' => 'Hai accettato la proposta. L’ordine aggiornato è confermato dal ristorante.',
            'rejected' => 'Hai rifiutato la proposta. L’ordine originale non è stato modificato; contatta il ristorante per concordare come procedere.',
            default => 'Il ristorante ha ritirato la proposta. L’ordine originale non è stato modificato.',
        };
        $context = ['summary' => app(OrderSummaryService::class)->snapshot($order)];
        $this->record('proposal:'.$proposal->id.':'.$proposal->status.':customer', $order->id, $order->customer_email, $order->customer_name ?? '',
            'Aggiornamento ordine '.$order->reference, $text, language: $order->customer?->lang ?? 'it', context: $context);
        if (in_array($proposal->status, ['accepted', 'rejected'])) {
            app(\App\Services\Notification\NotificationService::class)->forPermission(
                Permissions::CUSTOMER_ORDERS, 'customer_order.proposal_'.$proposal->status,
                'Risposta alla proposta · '.$order->reference,
                'Il cliente ha '.($proposal->status === 'accepted' ? 'accettato' : 'rifiutato').' la proposta di modifica.',
                route('customers-orders.show', $order)
            );
            foreach (User::where('enabled', true)->whereNotNull('email')->where(fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', 'admin'))->orWhereHas('permissions', fn ($p) => $p->where('name', Permissions::CUSTOMER_ORDERS)))->get() as $user) {
                $this->record('proposal:'.$proposal->id.':'.$proposal->status.':staff:'.$user->id, $order->id, $user->email, $user->name,
                    'Risposta alla proposta · '.$order->reference, 'Il cliente ha '.($proposal->status === 'accepted' ? 'accettato' : 'rifiutato').' la proposta di modifica.', route('customers-orders.show', $order), context: $context);
            }
        }
    }

    public function status(Order $order, string $status): void
    {
        if (! $order->customer_email) {
            return;
        }
        $labels = ['paid' => 'pagato', 'received' => 'ricevuto', 'accepted' => 'accettato', 'preparing' => 'in preparazione', 'ready' => 'pronto', 'out_for_delivery' => 'in consegna', 'completed' => 'completato', 'rejected' => 'rifiutato', 'cancelled' => 'annullato'];
        $this->record('order:'.$order->id.':'.$status, $order->id, $order->customer_email, $order->customer_name ?? '', 'Ordine '.$order->reference, 'Il tuo ordine '.$order->reference.' è '.($labels[$status] ?? $status).'.', language: $order->customer?->lang ?? 'it', context: ['summary' => app(OrderSummaryService::class)->snapshot($order)]);
    }

    public function courseRejected(\App\Models\OrderCourse $course): void
    {
        $customer = Customer::find($course->customer_id);
        if ($customer?->email) {
            $this->record('course:'.$course->id.':rejected', $course->order_id, $customer->email, $course->customer_name ?? '', 'Ordine al tavolo non confermato', 'Il ristorante non ha confermato questa uscita. Rivolgiti al personale per assistenza.');
        }
    }

    public function courseAccepted(\App\Models\OrderCourse $course): void
    {
        $customer = Customer::find($course->customer_id);
        if ($customer?->email) {
            $this->record('course:'.$course->id.':accepted', $course->order_id, $customer->email, $course->customer_name ?? '', 'Ordine al tavolo accettato', 'La tua uscita è stata inviata ai reparti.', context: ['summary' => app(OrderSummaryService::class)->snapshot($course->order, $course)]);
        }
    }

    public function incomingCourse(\App\Models\OrderCourse $course): void
    {
        app(\App\Services\Notification\NotificationService::class)->forPermission(
            Permissions::ORDERS, 'table_order.received', 'Nuovo ordine dal tavolo',
            'Tavolo '.$course->order->tables->pluck('name')->implode(', ').' · Uscita '.$course->number.' di '.$course->customer_name.' da verificare e accettare.',
            route('orders.show', $course->order_id)
        );
        foreach (User::where('enabled', true)->whereNotNull('email')->where(fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', 'admin'))->orWhereHas('permissions', fn ($p) => $p->where('name', Permissions::ORDERS)))->get() as $user) {
            $this->record('table-incoming:'.$course->id.':'.$user->id, $course->order_id, $user->email, $user->name, 'Nuovo ordine dal tavolo', 'È arrivata una nuova uscita da verificare.', route('orders.show', $course->order_id));
        }
    }

    public function incoming(Order $order): void
    {
        app(\App\Services\Notification\NotificationService::class)->forPermission(
            Permissions::CUSTOMER_ORDERS, 'customer_order.received', 'Nuovo ordine '.$order->reference,
            ($order->fulfillment === 'delivery' ? 'Consegna' : 'Asporto').' · '.$order->customer_name,
            route('customers-orders.show', $order)
        );
        foreach (User::where('enabled', true)->whereNotNull('email')->where(fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', 'admin'))->orWhereHas('permissions', fn ($p) => $p->where('name', Permissions::CUSTOMER_ORDERS)))->get() as $user) {
            $this->record('incoming:'.$order->id.':'.$user->id, $order->id, $user->email, $user->name, 'Nuovo ordine '.$order->reference, 'È arrivato un nuovo ordine cliente.', route('customers-orders.show', $order), context: ['summary' => app(OrderSummaryService::class)->snapshot($order)]);
        }
    }
}
