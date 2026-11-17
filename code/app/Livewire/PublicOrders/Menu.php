<?php

namespace App\Livewire\PublicOrders;

use App\Models\CustomerOrderNeighborhood;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\OrderCourse;
use App\Models\RoomTable;
use App\Services\CustomerLanguageService;
use App\Services\PublicOrders\AvailabilityService;
use App\Services\PublicOrders\CheckoutService;
use App\Services\PublicOrders\CustomerAccessService;
use App\Services\PublicOrders\OrderingSettings;
use App\Services\TenantBrandingService;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Menu extends Component
{
    #[Locked]
    public string $menuId = '';

    #[Locked]
    public string $language = 'it';

    #[Locked]
    public ?string $tableToken = null;

    #[Locked]
    public string $submissionKey = '';

    #[Locked]
    public int $displayedTotal = 0;

    #[Locked]
    public ?string $lastCourseId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public bool $privacy = false;

    public bool $emailSent = false;

    public array $cart = [];

    public string $type = 'pickup';

    public string $date = '';

    public string $time = '';

    public string $address = '';

    public ?int $neighborhood = null;

    public string $orderNotes = '';

    public bool $cartOpen = false;

    public bool $productModal = false;

    #[Locked]
    public string $categoryId = '';

    #[Locked]
    public string $productId = '';

    #[Locked]
    public ?int $editingLine = null;

    public int $quantity = 1;

    public string $notes = '';

    public array $added = [];

    public array $removed = [];

    public function mount(string $language, DigitalMenu $menu): void
    {
        $this->menuId = $menu->id;
        $this->language = $language;
        $this->tableToken = request()->query('table');
        $this->context();
        $this->type = $this->tableToken ? 'dine_in' : (app(OrderingSettings::class)->get()['pickup_enabled'] ? 'pickup' : 'delivery');
        $this->date = today()->toDateString();
        $this->submissionKey = Str::random(48);
        if (app(CustomerAccessService::class)->current($this->tableToken)) {
            $this->cart = session($this->cartKey(), []);
        }
    }

    private function context(): DigitalMenu
    {
        $menu = DigitalMenu::findOrFail($this->menuId);
        abort_unless($menu->is_published && $menu->is_visible && in_array($this->language, $menu->enabled_languages, true), 404);
        if ($this->tableToken) {
            RoomTable::where('ordering_token', $this->tableToken)->whereHas('room', fn ($q) => $q->where('active', true))->firstOrFail();
        } else {
            abort_unless(app(OrderingSettings::class)->get()['enabled'], 404);
        }
        app()->setLocale($this->language);

        return $menu;
    }

    private function authenticated(): void
    {
        $this->context();
        abort_unless(app(CustomerAccessService::class)->current($this->tableToken), 403, __('Verifica la tua email per continuare.'));
    }

    private function cartKey(): string
    {
        return 'public-order-cart.'.hash('sha256', $this->menuId.'|'.$this->tableToken.'|'.session('customer_order_session'));
    }

    public function requestAccess(): void
    {
        $menu = $this->context();
        $this->resetValidation();
        app(CustomerAccessService::class)->request($menu, $this->language, ['name' => $this->name, 'email' => $this->email, 'phone' => $this->phone, 'privacy' => $this->privacy], $this->tableToken);
        $this->emailSent = true;
    }

    public function logout(): void
    {
        session()->forget('customer_order_session');
        $this->cart = [];
        $this->cartOpen = false;
    }

    public function chooseProduct(string $category, string $product): void
    {
        $this->authenticated();
        $this->reset(['quantity', 'notes', 'added', 'removed', 'editingLine']);
        $this->resetValidation();
        $row = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($category)->products()->findOrFail($product);
        $this->categoryId = $category;
        $this->productId = $row->id;
        $this->productModal = true;
    }

    public function editLine(int $index): void
    {
        $this->authenticated();
        abort_unless(isset($this->cart[$index]), 404);
        $line = $this->cart[$index];
        $this->categoryId = $line['category_id'];
        $this->productId = $line['product_id'];
        $this->quantity = $line['quantity'];
        $this->notes = $line['notes'];
        $this->added = $line['added'];
        $this->removed = $line['removed'];
        $this->editingLine = $index;
        $this->productModal = true;
    }

    public function addLine(): void
    {
        $this->authenticated();
        abort_unless(DigitalMenuCategory::whereKey($this->categoryId)->where('digital_menu_id', $this->menuId)->exists(), 404);
        $line = ['category_id' => $this->categoryId, 'product_id' => $this->productId, 'quantity' => $this->quantity, 'notes' => $this->notes, 'added' => $this->added, 'removed' => $this->removed];
        app(CheckoutService::class)->quote([$line], $this->tableToken ? 'dine_in' : $this->type, $this->language);
        if ($this->editingLine !== null) {
            abort_unless(isset($this->cart[$this->editingLine]), 404);
            $this->cart[$this->editingLine] = $line;
        } else {
            if (count($this->cart) >= 100) {
                $this->addError('cart', __('Massimo 100 righe.'));

                return;
            } $this->cart[] = $line;
        }
        session()->put($this->cartKey(), $this->cart);
        $this->productModal = false;
        $this->cartOpen = true;
    }

    public function removeLine(int $index): void
    {
        $this->authenticated();
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        session()->put($this->cartKey(), $this->cart);
    }

    public function submit(): void
    {
        $this->authenticated();
        $course = app(CheckoutService::class)->submit($this->cart, $this->language, $this->tableToken, [
            'type' => $this->tableToken ? 'dine_in' : $this->type, 'date' => $this->date, 'time' => $this->time, 'address' => $this->address, 'neighborhood' => $this->neighborhood, 'notes' => $this->orderNotes,
        ], $this->submissionKey, $this->displayedTotal);
        $this->lastCourseId = $course->id;
        $this->cart = [];
        session()->forget($this->cartKey());
        $this->submissionKey = Str::random(48);
        $this->cartOpen = false;
        $this->orderNotes = '';
    }

    public function render()
    {
        $menu = $this->context();
        $access = app(CustomerAccessService::class)->current($this->tableToken);
        $menu->load(['categories' => fn ($q) => $q->where('is_enabled', true)->with(['products' => fn ($q) => $q->where('is_active', true)->wherePivot('is_visible', true)->when(! $this->tableToken, fn ($q) => $q->where('disallow_takeaway', false))->with(['recommendations' => fn ($q) => $q->where('is_active', true)->with('recommendedProduct')])])]);
        $quote = ['lines' => [], 'subtotal' => 0];
        $fee = 0;
        $percentage = 0;
        if ($access && $this->cart) {
            try {
                $quote = app(CheckoutService::class)->quote($this->cart, $this->tableToken ? 'dine_in' : $this->type, $this->language);
            } catch (\Illuminate\Validation\ValidationException|\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->addError('cart', __('Un prodotto non è più disponibile. Rimuovilo dal carrello e selezionalo nuovamente.'));
            }
        }
        $settings = app(OrderingSettings::class)->get();
        if ($this->tableToken && $access) {
            $percentage = \App\Services\Orders\OrderService::cents((string) $access->table->room->service_charge_percentage);
            $fee = intdiv($quote['subtotal'] * $percentage + 5000, 10000);
        } elseif ($this->type === 'delivery') {
            $fee = $settings['delivery_free_over_cents'] > 0 && $quote['subtotal'] >= $settings['delivery_free_over_cents'] ? 0 : $settings['delivery_fee_cents'];
        }
        $this->displayedTotal = $quote['subtotal'] + $fee;
        $product = $this->productModal ? DigitalMenuProduct::with('ingredients')->find($this->productId) : null;
        $submissions = $access ? OrderCourse::with(['order', 'items.modifiers', 'items.ticket'])->where('customer_id', $access->customer_id)
            ->when($this->tableToken, fn ($q) => $q->where('order_id', $access->order_id), fn ($q) => $q->whereHas('order', fn ($q) => $q->whereIn('fulfillment', ['pickup', 'delivery'])))->latest()->limit(10)->get() : collect();

        return view('livewire.public-orders.menu', [
            'coupons' => app(\App\Services\Marketing\CouponService::class)->visibleOnMenu($menu, $this->language),
            'menu' => $menu, 'access' => $access, 'ordering' => true, 'quote' => $quote, 'fee' => $fee, 'percentage' => $percentage, 'settings' => $settings,
            'product' => $product, 'ingredients' => $product ? DigitalMenuIngredient::all()->sortBy(fn ($i) => $i->translatedName($this->language)) : collect(),
            'submissions' => $submissions, 'recognizedName' => app(CustomerAccessService::class)->recognizedName(),
            'neighborhoods' => CustomerOrderNeighborhood::where('active', true)->orderBy('name')->get(),
            'slots' => $this->tableToken ? [] : app(AvailabilityService::class)->slots($this->date, $this->type),
            'branding' => app(TenantBrandingService::class)->branding(),
            'languages' => collect($menu->enabled_languages)->mapWithKeys(fn ($code) => [$code => app(CustomerLanguageService::class)->meta($code)])->all(),
        ])->layout('layouts.digital-menu')->title($menu->translatedName($this->language));
    }
}
