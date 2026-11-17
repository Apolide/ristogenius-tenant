<?php

namespace App\Livewire\CustomerOrders;

use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use App\Services\PublicOrders\AvailabilityService;
use App\Services\PublicOrders\OrderProposalService;
use App\Services\PublicOrders\OrderSummaryService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProposalEditor extends Component
{
    #[Locked]
    public string $orderId;

    #[Locked]
    public string $fingerprint;

    #[Locked]
    public int $displayedTotal = 0;

    public array $lines = [];

    public string $date = '';

    public string $time = '';

    public string $notes = '';

    public string $message = '';

    public string $search = '';

    public string $selectedProduct = '';

    public ?int $ingredientLine = null;

    public array $added = [];

    public array $removed = [];

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CUSTOMER_ORDERS);
    }

    public function mount(Order $order): void
    {
        $service = app(OrderProposalService::class);
        $service->assertEditable($order);
        $this->orderId = $order->id;
        $this->fingerprint = $service->fingerprint($order);
        $this->lines = $service->initialLines($order);
        $this->date = $order->requested_at?->toDateString() ?? today()->toDateString();
        $this->time = $order->requested_at?->format('H:i') ?? '';
        $this->notes = $order->customer_notes ?? '';
    }

    public function addProduct(): void
    {
        $this->validate(['selectedProduct' => 'required|string']);
        $parts = explode(':', $this->selectedProduct, 2);
        abort_unless(count($parts) === 2, 404);
        $order = Order::findOrFail($this->orderId);
        $line = ['original_item_id' => null, 'category_id' => $parts[0], 'product_id' => $parts[1], 'quantity' => 1, 'notes' => '', 'added' => [], 'removed' => []];
        app(OrderProposalService::class)->priceLines($order, [$line]);
        if (count($this->lines) >= 100) {
            $this->addError('lines', __('Massimo 100 righe.'));

            return;
        }
        $this->lines[] = $line;
        $this->selectedProduct = '';
    }

    public function removeLine(int $index): void
    {
        abort_unless(isset($this->lines[$index]), 404);
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->ingredientLine = null;
    }

    public function editIngredients(int $index): void
    {
        abort_unless(isset($this->lines[$index]), 404);
        $this->ingredientLine = $index;
        $this->added = $this->lines[$index]['added'];
        $this->removed = $this->lines[$index]['removed'];
    }

    public function saveIngredients(): void
    {
        abort_unless($this->ingredientLine !== null && isset($this->lines[$this->ingredientLine]), 404);
        $line = $this->lines[$this->ingredientLine];
        $line['added'] = $this->added;
        $line['removed'] = $this->removed;
        app(OrderProposalService::class)->priceLines(Order::findOrFail($this->orderId), [$line]);
        $this->lines[$this->ingredientLine] = $line;
        $this->ingredientLine = null;
    }

    public function sendProposal(): void
    {
        $this->resetValidation();
        app(OrderProposalService::class)->create($this->orderId, $this->lines, ['date' => $this->date, 'time' => $this->time, 'notes' => $this->notes], $this->message, $this->displayedTotal, $this->fingerprint);
        session()->flash('order_notice', __('Proposta inviata al cliente via email. L’ordine resta invariato fino alla conferma.'));
        $this->redirectRoute('customers-orders.show', ['order' => $this->orderId]);
    }

    public function render()
    {
        $order = Order::findOrFail($this->orderId);
        $service = app(OrderProposalService::class);
        $priced = ['lines' => [], 'subtotal' => 0];
        $preview = null;
        try {
            $priced = $service->priceLines($order, $this->lines);
            $quote = $service->quote($order, $this->lines, ['date' => $this->date, 'time' => $this->time, 'notes' => $this->notes]);
            $this->displayedTotal = $quote['total'];
            $preview = app(OrderSummaryService::class)->proposed($order, $quote);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->displayedTotal = 0;
            $previewError = $e->validator->errors()->first();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->displayedTotal = 0;
            $previewError = __('Un prodotto non è più disponibile. Riapri la proposta.');
        }
        $categories = DigitalMenuCategory::where('is_enabled', true)->whereHas('menu', fn ($q) => $q->where('is_visible', true)->where('is_published', true))
            ->with(['menu', 'products' => fn ($q) => $q->where('is_active', true)->where('disallow_takeaway', false)->wherePivot('is_visible', true)])->get();
        $choices = [];
        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                if ($this->search !== '' && mb_stripos($product->translatedName(), $this->search) === false) {
                    continue;
                }
                $choices[$category->id.':'.$product->id] = $category->menu->translatedName().' · '.$product->translatedName().' · '.number_format($product->pivot->menu_price ?? $product->price, 2, ',', '.').' €';
            }
        }
        $ingredientProduct = $this->ingredientLine !== null ? DigitalMenuProduct::with('ingredients')->find($this->lines[$this->ingredientLine]['product_id'] ?? null) : null;

        return view('livewire.customer-orders.proposal-editor', [
            'order' => $order, 'priced' => $priced, 'preview' => $preview, 'previewError' => $previewError ?? null,
            'choices' => $choices, 'slots' => app(AvailabilityService::class)->slots($this->date, $order->fulfillment),
            'ingredientProduct' => $ingredientProduct, 'ingredients' => $ingredientProduct ? DigitalMenuIngredient::all()->sortBy(fn ($i) => $i->translatedName()) : collect(),
        ])->title(__('Proponi modifica · ').$order->reference);
    }
}
