<?php

namespace App\Livewire\CashDesk;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public string $orderId;

    #[Locked]
    public int $displayedTotal = 0;

    #[Locked]
    public int $displayedBalance = 0;

    public string $method = 'cash';

    public string $amount = '';

    public string $tip = '0.00';

    public string $payer = '';

    public string $voucherIssuer = '';

    public string $voucherReference = '';

    public string $discount = '0.00';

    public string $discountReason = '';

    public bool $percentage = false;

    public int $auxiliaryVat = 10;

    public int $shares = 2;

    #[Locked]
    public string $paymentKey;

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
    }

    public function mount(Order $order): void
    {
        $this->orderId = $order->id;
        $this->paymentKey = (string) \Illuminate\Support\Str::uuid();
        $this->auxiliaryVat = (int) $order->auxiliary_vat;
    }

    public function pay(): void
    {
        $this->validate(['amount' => ['nullable', 'regex:/^\d{1,7}([.,]\d{1,2})?$/'], 'tip' => ['required', 'regex:/^\d{1,6}([.,]\d{1,2})?$/']]);
        app(OrderService::class)->pay($this->orderId, $this->method, $this->displayedTotal,
            $this->amount === '' ? null : $this->money($this->amount), $this->money($this->tip), $this->paymentKey,
            $this->payer ?: null, $this->voucherIssuer ?: null, $this->voucherReference ?: null, $this->displayedBalance);
        $this->paymentKey = (string) \Illuminate\Support\Str::uuid();
        $this->reset('amount', 'tip', 'payer', 'voucherIssuer', 'voucherReference');
        session()->flash('order_notice', 'Pagamento registrato.');
    }

    private function money(string $value): int
    {
        return OrderService::cents(str_replace(',', '.', $value));
    }

    public function split(): void
    {
        $this->validate(['shares' => 'integer|min:2|max:100']);
        $balance = Order::findOrFail($this->orderId)->balanceCents();
        $this->amount = number_format(intdiv($balance + $this->shares - 1, $this->shares) / 100, 2, '.', '');
    }

    public function applyDiscount(): void
    {
        $this->validate(['discount' => ['required', 'regex:/^\d{1,7}([.,]\d{1,2})?$/']]);
        app(OrderService::class)->discount($this->orderId, $this->money($this->discount), $this->discountReason, $this->displayedTotal, $this->percentage, $this->auxiliaryVat);
        session()->flash('order_notice', 'Sconto e aliquota aggiornati.');
    }

    public function cancel(): void
    {
        app(OrderService::class)->cancel($this->orderId);
        session()->flash('order_notice', 'Ordine annullato.');
    }

    public function render()
    {
        $order = Order::with(['courses.items.modifiers', 'courses.items.ticket', 'customer', 'payments', 'events.user'])->findOrFail($this->orderId);
        $this->displayedTotal = $order->totalCents();
        $this->displayedBalance = $order->balanceCents();

        return view('livewire.cash-desk.show', ['activeShift' => \App\Models\CashShift::where('active_key', 'main')->first(), 'order' => $order, 'totals' => app(\App\Services\Orders\CashTotalsService::class)->snapshot($order), 'balance' => $order->balanceCents()])->title('Cassa · '.$order->table_name);
    }
}
