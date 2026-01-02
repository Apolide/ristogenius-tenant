<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tenant extends Model
{
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'name',
        'system_name',
        'email',
        'phone',
        'company_name',
        'piva',
        'riferimento_mandato',
        'region_id',
        'province_id',
        'comuni_id',
        'address',
        'legal_officer',
        'legal_address',
        'legal_phone',
        'legal_email',
    ];

    protected $casts = [
        // 'stripe_connect_charges_enabled' => 'boolean',
        // 'stripe_connect_payouts_enabled' => 'boolean',
        // 'stripe_connect_onboarding_completed_at' => 'datetime',
    ];

    // public function subscriptions()
    // {
    //     return $this->hasMany(Tenantsubscription::class);
    // }

    // /**
    //  * Relazione con i fondi del tenant
    //  */
    // public function funds()
    // {
    //     return $this->hasOne(TenantFunds::class);
    // }

    // /**
    //  * Relazione con le transazioni dei fondi
    //  */
    // public function fundTransactions()
    // {
    //     return $this->hasMany(TenantFundTransaction::class);
    // }

    // /**
    //  * Relazione con gli acquisti di fondi
    //  */
    // public function fundPurchases()
    // {
    //     return $this->hasMany(TenantFundPurchase::class);
    // }

    // public function tenantCoupons()
    // {
    //     return $this->hasMany(TenantCoupon::class);
    // }

    // public function customerCoupons()
    // {
    //     return $this->hasMany(CustomerCoupon::class);
    // }
   

    // /**
    //  * Ottiene o crea il record fondi per il tenant
    //  */
    // public function getOrCreateFunds(): TenantFunds
    // {
    //     if (!$this->funds) {
    //         $this->funds()->create([
    //             'tenant_id' => $this->id,
    //             'current_balance' => 0.00,
    //             'total_purchased' => 0.00,
    //             'total_consumed' => 0.00
    //         ]);
    //         $this->load('funds');
    //     }
        
    //     return $this->funds;
    // }

    // /**
    //  * Verifica se il tenant può inviare messaggi (ha fondi sufficienti)
    //  */
    // public function canSendMessage(string $type, string $reason): bool
    // {
    //     $funds = $this->getOrCreateFunds();
    //     $cost = config("messages-costs.$type.$reason") ?? 0.0;
        
    //     return $funds->hasSufficientFunds($cost);
    // }

    // /**
    //  * Verifica se il tenant può utilizzare l'AI
    //  */
    // public function canUseAi(float $estimatedCost): bool
    // {
    //     $funds = $this->getOrCreateFunds();
    //     return $funds->hasSufficientFunds($estimatedCost);
    // }

    // public function fidelityAccounts()
    // {
    //     return $this->hasMany(FidelityAccount::class);
    // }

    // public function fidelityTransactions()
    // {
    //     return $this->hasMany(FidelityPointTransaction::class);
    // }


}