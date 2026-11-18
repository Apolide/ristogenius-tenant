<?php

namespace Tests\Feature\Fiscal;

use App\Livewire\CashDesk\FiscalDocuments;
use App\Livewire\Settings\Fiscal\Settings;
use App\Models\FiscalCashier;
use App\Models\FiscalProfile;
use App\Models\FiscalStore;
use App\Models\Order;
use App\Models\User;
use App\Services\Fiscal\FiscalDocumentService;
use App\Services\Fiscal\FiscalWorkstationService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FiscalWorkstationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private FiscalProfile $profile;

    private FiscalStore $store;

    private FiscalCashier $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['fiscal.openapi.token' => 'sandbox-test-secret']);
        Role::findOrCreate('admin', 'web');
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
        app(Permissions::class)->ensurePermissionsExist();
        $this->profile = FiscalProfile::create(['fiscal_id' => '01114601006', 'name' => 'Test', 'email' => 'company@example.test',
            'address' => [], 'remote_id' => 'profile1', 'enabled' => true]);
        $this->store = $this->newStore('store1', 'register1');
        $this->cashier = $this->newCashier($this->admin, 'admin-cashier');
    }

    private function newStore(string $remote, string $register): FiscalStore
    {
        return FiscalStore::create(['fiscal_profile_id' => $this->profile->id, 'remote_id' => $remote, 'label' => $remote, 'active' => true,
            'status' => 'ONLINE', 'pem_serial' => 'PEM-'.$remote, 'operational_status' => 'OPEN',
            'registers' => [['cr_id' => $register, 'label' => $register, 'status' => 'ONLINE']]]);
    }

    private function newCashier(User $user, string $remote): FiscalCashier
    {
        return FiscalCashier::create(['fiscal_profile_id' => $this->profile->id, 'user_id' => $user->id, 'name' => $user->name,
            'email' => $user->email, 'remote_id' => $remote, 'status' => 'active']);
    }

    private function operator(): array
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CASH_REGISTER);

        return [$user, $this->newCashier($user, 'staff-cashier')];
    }

    private function order(string $method = 'card'): Order
    {
        $order = Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 1, 'cover_price_cents' => 1000]);
        app(OrderService::class)->pay($order->id, $method, 1000);

        return $order->fresh();
    }

    private function rejected(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected rejected workstation selection');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    public function test_unique_workstation_is_selected_and_irrelevant_payment_fields_are_hidden(): void
    {
        Livewire::test(FiscalDocuments::class, ['order' => $this->order()])
            ->assertSet('storeId', $this->store->id)->assertSet('registerId', 'register1')->assertSet('manualMethod', '')
            ->assertDontSee('Incassi registrati come manuali')->assertDontSee('Numero buoni pasto utilizzati');
        Http::assertNothingSent();
    }

    public function test_admin_remembers_selection_across_orders_and_component_instances(): void
    {
        $other = $this->newStore('store2', 'register2');
        Livewire::test(FiscalDocuments::class, ['order' => $this->order()])->set('storeId', $other->id)
            ->assertSet('registerId', 'register2')->call('saveWorkstation')->assertHasNoErrors();
        Livewire::test(FiscalDocuments::class, ['order' => $this->order()])->assertSet('storeId', $other->id)->assertSet('registerId', 'register2');
        $this->assertSame($other->id, $this->cashier->fresh()->default_store_id);
        $this->assertDatabaseHas('fiscal_operations', ['action' => 'workstation_preferences', 'user_id' => $this->admin->id, 'status' => 'succeeded']);
        Http::assertNothingSent();
    }

    public function test_a_store_change_with_multiple_registers_requires_an_explicit_choice(): void
    {
        $other = $this->newStore('store2', 'register2');
        $other->update(['registers' => [['cr_id' => 'register2', 'label' => 'Seconda', 'status' => 'ONLINE'], ['cr_id' => 'register3', 'label' => 'Terza', 'status' => 'ONLINE']]]);
        Livewire::test(FiscalDocuments::class, ['order' => $this->order()])->set('storeId', $other->id)->assertSet('registerId', '')
            ->set('registerId', 'register3')->call('saveWorkstation')->assertHasNoErrors();
        $this->assertSame('register3', $this->cashier->fresh()->default_register_id);
    }

    public function test_admin_assigns_a_locked_workstation_and_staff_cannot_bypass_it(): void
    {
        $other = $this->newStore('store2', 'register2');
        [$user, $cashier] = $this->operator();
        app(FiscalWorkstationService::class)->assign($cashier->id, $this->store->id, 'register1', '', false);
        $order = $this->order();
        $this->actingAs($user);
        Livewire::test(FiscalDocuments::class, ['order' => $order])->assertSet('storeId', $this->store->id)->assertSet('registerId', 'register1')
            ->assertDontSee('Ricorda questa postazione')->set('storeId', $other->id)->set('registerId', 'register2')->call('issue')->assertHasErrors('fiscal');
        $this->rejected(fn () => app(FiscalDocumentService::class)->issue($order->id, $other->id, 'register2'));
        $this->rejected(fn () => app(FiscalDocumentService::class)->close($other->id));
        Http::assertNothingSent();
    }

    public function test_staff_cannot_change_preferences_or_grant_switch_permission(): void
    {
        [$user, $cashier] = $this->operator();
        $this->actingAs($user);
        foreach (['remember', 'assign'] as $action) {
            try {
                if ($action === 'remember') {
                    app(FiscalWorkstationService::class)->remember($this->store->id, 'register1');
                } else {
                    app(FiscalWorkstationService::class)->assign($cashier->id, $this->store->id, 'register1', '', true);
                }
                $this->fail('Expected forbidden preference change');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        $this->assertFalse($cashier->fresh()->allow_workstation_switch);
        Http::assertNothingSent();
    }

    public function test_staff_can_switch_and_remember_only_when_admin_allows_it(): void
    {
        $other = $this->newStore('store2', 'register2');
        [$user, $cashier] = $this->operator();
        app(FiscalWorkstationService::class)->assign($cashier->id, $this->store->id, 'register1', '', true);
        $order = $this->order();
        $this->actingAs($user);
        Livewire::test(FiscalDocuments::class, ['order' => $order])->assertSee('Ricorda questa postazione')
            ->set('storeId', $other->id)->call('saveWorkstation')->assertHasNoErrors();
        Livewire::test(FiscalDocuments::class, ['order' => $order])->assertSet('storeId', $other->id)->assertSet('registerId', 'register2');
        $this->assertTrue($cashier->fresh()->allow_workstation_switch);
        Http::assertNothingSent();
    }

    public function test_multiple_workstations_without_an_assignment_do_not_pick_arbitrarily(): void
    {
        $this->newStore('store2', 'register2');
        [$user] = $this->operator();
        $order = $this->order();
        $this->actingAs($user);
        Livewire::test(FiscalDocuments::class, ['order' => $order])->assertSet('storeId', '')->assertSet('registerId', '')->call('issue')->assertHasErrors('fiscal');
        Http::assertNothingSent();
    }

    public function test_unique_workstation_can_be_used_by_locked_staff_without_manual_configuration(): void
    {
        [$user] = $this->operator();
        $order = $this->order();
        $this->actingAs($user);
        Livewire::test(FiscalDocuments::class, ['order' => $order])->assertSet('storeId', $this->store->id)->assertSet('registerId', 'register1');
        app(FiscalWorkstationService::class)->assertSelection($this->store->id, 'register1');
        $this->assertFalse(app(FiscalWorkstationService::class)->defaults()['can_switch']);
    }

    public function test_wrong_register_and_foreign_company_store_cannot_be_saved(): void
    {
        $other = $this->newStore('store2', 'register2');
        $this->rejected(fn () => app(FiscalWorkstationService::class)->assign($this->cashier->id, $other->id, 'register1', '', false));
        $foreign = FiscalProfile::create(['environment' => 'another-environment', 'fiscal_id' => '11122211133', 'name' => 'Other', 'email' => 'other@example.test', 'address' => []]);
        $other->update(['fiscal_profile_id' => $foreign->id]);
        $this->rejected(fn () => app(FiscalWorkstationService::class)->remember($other->id, 'register2'));
        $this->assertNull($this->cashier->fresh()->default_store_id);
        Http::assertNothingSent();
    }

    public function test_unavailable_assigned_workstation_is_not_silently_replaced(): void
    {
        $other = $this->newStore('store2', 'register2');
        app(FiscalWorkstationService::class)->assign($this->cashier->id, $this->store->id, 'register1', '', false);
        $this->store->update(['active' => false, 'status' => 'OFFLINE']);
        Livewire::test(FiscalDocuments::class, ['order' => $this->order()])->assertSet('storeId', $this->store->id)->assertSet('registerId', 'register1');
        $this->assertNotSame($other->id, app(FiscalWorkstationService::class)->defaults()['store_id']);
        $this->rejected(fn () => app(FiscalWorkstationService::class)->remember($this->store->id, 'register1'));
    }

    public function test_admin_manual_default_is_only_shown_for_orders_with_manual_payments(): void
    {
        app(FiscalWorkstationService::class)->assign($this->cashier->id, $this->store->id, 'register1', 'cash', false);
        Livewire::test(FiscalDocuments::class, ['order' => $this->order('manual')])->assertSet('manualMethod', 'cash')->assertSee('Incassi registrati come manuali');
        Livewire::test(FiscalDocuments::class, ['order' => $this->order('card')])->assertSet('manualMethod', '')->assertDontSee('Incassi registrati come manuali');
        $this->assertSame('manual', $this->order('manual')->payments()->first()->method);
        Http::assertNothingSent();
    }

    public function test_explicit_remember_saves_manual_default_but_never_changes_payment_history(): void
    {
        $order = $this->order('manual');
        Livewire::test(FiscalDocuments::class, ['order' => $order])->set('manualMethod', 'card')->call('saveWorkstation')->assertHasNoErrors();
        Livewire::test(FiscalDocuments::class, ['order' => $this->order('manual')])->assertSet('manualMethod', 'card');
        $this->assertSame('manual', $order->payments()->first()->method);
        $this->assertSame('card', $this->cashier->fresh()->default_manual_method);
        Http::assertNothingSent();
    }

    public function test_preferences_are_isolated_per_operator(): void
    {
        $other = $this->newStore('store2', 'register2');
        [$user, $cashier] = $this->operator();
        app(FiscalWorkstationService::class)->remember($other->id, 'register2');
        app(FiscalWorkstationService::class)->assign($cashier->id, $this->store->id, 'register1', 'card', false);
        $this->actingAs($user);
        $this->assertSame($this->store->id, app(FiscalWorkstationService::class)->defaults()['store_id']);
        $this->assertSame('card', app(FiscalWorkstationService::class)->defaults()['manual_method']);
        $this->actingAs($this->admin);
        $this->assertSame($other->id, app(FiscalWorkstationService::class)->defaults()['store_id']);
        $this->assertSame('', app(FiscalWorkstationService::class)->defaults()['manual_method']);
    }

    public function test_settings_save_assignment_selects_the_only_register_and_records_permission(): void
    {
        $other = $this->newStore('store2', 'register2');
        Livewire::test(Settings::class)->set('workstations.'.$this->cashier->id.'.store_id', $other->id)
            ->assertSet('workstations.'.$this->cashier->id.'.register_id', 'register2')
            ->set('workstations.'.$this->cashier->id.'.manual_method', 'card')
            ->set('workstations.'.$this->cashier->id.'.allow_switch', true)->call('saveWorkstation', $this->cashier->id)->assertHasNoErrors();
        $this->assertSame($other->id, $this->cashier->fresh()->default_store_id);
        $this->assertSame('card', $this->cashier->fresh()->default_manual_method);
        $this->assertTrue($this->cashier->fresh()->allow_workstation_switch);
        Http::assertNothingSent();
    }
}
