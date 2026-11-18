<?php

namespace App\Services\PublicOrders;

use App\Models\Customer;
use App\Models\CustomerOrderProposal;
use App\Models\MessageOutbox;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\User;
use App\Services\CustomerLanguageService;
use App\Services\Notification\NotificationService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;

class OrderNotifications
{
    public function record(string $key, string $aggregateId, string $email, string $name, string $subject, string $message, ?string $url = null, string $language = 'it', array $context = [], array $parameters = [], bool $translateMessage = true): void
    {
        $language = $this->language($language);
        MessageOutbox::firstOrCreate(['deduplication_key' => $key], [
            'event_type' => $key, 'aggregate_type' => 'customer_order', 'aggregate_id' => $aggregateId, 'status' => 'pending', 'attempts' => 0, 'available_at' => now(),
            'payload' => ['message_case' => 'customer_order', 'deliveries' => [[
                'recipient' => ['email' => $email], 'language' => $language, 'name' => $name,
                'subject' => __($subject, $parameters, $language), 'message' => $translateMessage ? __($message, $parameters, $language) : $message, 'url' => $url,
            ] + $context]],
        ]);
    }

    public function proposalSent(CustomerOrderProposal $proposal, string $token): void
    {
        $order = $proposal->order;
        $url = route('public-orders.proposal', ['token' => $token]);
        $this->record('proposal:'.$proposal->id.':sent', $order->id, $order->customer_email, $order->customer_name ?? '',
            'public_orders.notifications.change_proposal_reference', $proposal->message, $url, $order->customer_language ?: ($order->customer?->lang ?? 'it'), [
                'summary' => $proposal->payload['summary'], 'previous_summary' => $proposal->payload['before'],
                'accept_url' => $url.'?choice=accept', 'reject_url' => $url.'?choice=reject',
                'expires_at' => $proposal->expires_at->format('d/m/Y H:i'),
            ], ['reference' => $order->reference], translateMessage: false);
    }

    public function proposalResponded(CustomerOrderProposal $proposal, Order $order): void
    {
        $text = match ($proposal->status) {
            'accepted' => 'public_orders.notifications.customer_proposal_accepted',
            'rejected' => 'public_orders.notifications.customer_proposal_rejected',
            default => 'public_orders.notifications.customer_proposal_withdrawn',
        };
        $context = ['summary' => app(OrderSummaryService::class)->snapshot($order)];
        $parameters = ['reference' => $order->reference];
        $this->record('proposal:'.$proposal->id.':'.$proposal->status.':customer', $order->id, $order->customer_email, $order->customer_name ?? '',
            'public_orders.notifications.order_update_reference', $text, language: $order->customer_language ?: ($order->customer?->lang ?? 'it'), context: $context, parameters: $parameters);
        if (in_array($proposal->status, ['accepted', 'rejected'])) {
            $response = $proposal->status === 'accepted' ? 'public_orders.notifications.staff_proposal_accepted' : 'public_orders.notifications.staff_proposal_rejected';
            app(NotificationService::class)->forPermissionTranslated(Permissions::CUSTOMER_ORDERS, 'customer_order.proposal_'.$proposal->status,
                'public_orders.notifications.proposal_response_reference', $response, $parameters, route('customers-orders.show', $order));
            foreach ($this->staff(Permissions::CUSTOMER_ORDERS) as $user) {
                $this->record('proposal:'.$proposal->id.':'.$proposal->status.':staff:'.$user->id, $order->id, $user->email, $user->name,
                    'public_orders.notifications.proposal_response_reference', $response, route('customers-orders.show', $order), $user->lang ?? 'it', $context, $parameters);
            }
        }
    }

    public function status(Order $order, string $status): void
    {
        if (! $order->customer_email) {
            return;
        }
        $language = $this->language($order->customer_language ?: $order->customer?->lang);
        $labels = ['paid' => 'cash_desk.paid', 'received' => 'orders.awaiting_approval', 'accepted' => 'orders.accepted', 'preparing' => 'public_orders.notifications.preparing', 'ready' => 'public_orders.notifications.ready', 'out_for_delivery' => 'orders.out_for_delivery', 'completed' => 'orders.completed', 'rejected' => 'public_orders.notifications.rejected', 'cancelled' => 'cash_desk.cancelled'];
        $this->record('order:'.$order->id.':'.$status, $order->id, $order->customer_email, $order->customer_name ?? '',
            'public_orders.notifications.order_reference', 'public_orders.notifications.your_order_reference_is_status', language: $language,
            context: ['summary' => app(OrderSummaryService::class)->snapshot($order)],
            parameters: ['reference' => $order->reference, 'status' => __($labels[$status] ?? $status, [], $language)]);
    }

    public function courseRejected(OrderCourse $course): void
    {
        $customer = Customer::find($course->customer_id);
        if ($customer?->email) {
            $this->record('course:'.$course->id.':rejected', $course->order_id, $customer->email, $course->customer_name ?? '',
                'public_orders.notifications.table_order_not_confirmed', 'public_orders.notifications.table_course_rejected_message', language: $course->customer_language ?: ($customer->lang ?? 'it'));
        }
    }

    public function courseAccepted(OrderCourse $course): void
    {
        $customer = Customer::find($course->customer_id);
        if ($customer?->email) {
            $this->record('course:'.$course->id.':accepted', $course->order_id, $customer->email, $course->customer_name ?? '',
                'public_orders.notifications.table_order_accepted', 'public_orders.notifications.your_course_has_been_sent_to_the_departments', language: $course->customer_language ?: ($customer->lang ?? 'it'),
                context: ['summary' => app(OrderSummaryService::class)->snapshot($course->order, $course)]);
        }
    }

    public function incomingCourse(OrderCourse $course): void
    {
        app(NotificationService::class)->forPermissionTranslated(Permissions::ORDERS, 'table_order.received', 'public_orders.notifications.new_table_order',
            'public_orders.notifications.table_course_review_message',
            ['table' => $course->order->tables->pluck('name')->implode(', '), 'course' => $course->number, 'customer' => $course->customer_name],
            route('orders.show', $course->order_id));
        foreach ($this->staff(Permissions::ORDERS) as $user) {
            $this->record('table-incoming:'.$course->id.':'.$user->id, $course->order_id, $user->email, $user->name,
                'public_orders.notifications.new_table_order', 'public_orders.notifications.a_new_course_has_arrived_for_review', route('orders.show', $course->order_id), $user->lang ?? 'it');
        }
    }

    public function incoming(Order $order): void
    {
        app(NotificationService::class)->forPermissionTranslated(Permissions::CUSTOMER_ORDERS, 'customer_order.received', 'public_orders.notifications.new_order_reference',
            $order->fulfillment === 'delivery' ? 'public_orders.notifications.delivery_customer' : 'public_orders.notifications.takeaway_customer',
            ['reference' => $order->reference, 'customer' => $order->customer_name], route('customers-orders.show', $order));
        foreach ($this->staff(Permissions::CUSTOMER_ORDERS) as $user) {
            $this->record('incoming:'.$order->id.':'.$user->id, $order->id, $user->email, $user->name, 'public_orders.notifications.new_order_reference',
                'public_orders.notifications.new_customer_order_received', route('customers-orders.show', $order), $user->lang ?? 'it',
                ['summary' => app(OrderSummaryService::class)->snapshot($order)], ['reference' => $order->reference]);
        }
    }

    private function staff(string $permission)
    {
        return User::where('enabled', true)->whereNotNull('email')->get()
            ->filter(fn (User $user) => app(Permissions::class)->can($user, $permission));
    }

    private function language(?string $language): string
    {
        $language = strtolower($language ?? 'it');

        return array_key_exists($language, app(CustomerLanguageService::class)->backend()) ? $language : 'it';
    }
}
