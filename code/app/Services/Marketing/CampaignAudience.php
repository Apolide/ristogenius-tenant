<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CampaignAudience
{
    public function query(array $filters = [], bool $eligible = true): Builder
    {
        $visitKeys = ['lastVisitStart', 'lastVisitEnd', 'lastVisitAgo', 'lastVisitWithin'];
        $query = app(\App\Services\Customer\CustomerService::class)->query(array_diff_key($filters, array_flip($visitKeys)));
        $last = "(SELECT MAX(booking_date) FROM bookings WHERE bookings.customer_id=customers.id AND bookings.status IN ('finalized','seated') AND bookings.deleted_at IS NULL)";
        if (! empty($filters['lastVisitStart'])) {
            $query->whereRaw($last.' >= ?', [$filters['lastVisitStart']]);
        }
        if (! empty($filters['lastVisitEnd'])) {
            $query->whereRaw($last.' <= ?', [$filters['lastVisitEnd']]);
        }
        if (! empty($filters['lastVisitAgo'])) {
            $query->where(fn ($q) => $q->whereRaw($last.' IS NULL')->orWhereRaw($last.' <= ?', [now()->subMonths((int) $filters['lastVisitAgo'])->toDateString()]));
        }
        if (! empty($filters['lastVisitWithin'])) {
            $query->whereRaw($last.' >= ?', [now()->subMonths((int) $filters['lastVisitWithin'])->toDateString()]);
        }
        if ($eligible) {
            $query->where('consent_marketing', true)->where('blacklisted', false)->whereNotNull('email')->where('email', '<>', '')->whereNotExists(function ($q) {
                $q->selectRaw('1')->from('marketing_preferences')->whereColumn('marketing_preferences.customer_id', 'customers.id')->whereNotNull('unsubscribed_at');
            });
        }

        return $query;
    }

    public function selected(\App\Models\MarketingCampaign $campaign): Builder
    {
        return $this->query()->whereIn('customers.id', $campaign->recipients()->reorder()->select('customers.id'));
    }

    public function allowed(?Customer $customer): bool
    {
        return $customer && $customer->consent_marketing && ! $customer->blacklisted && filter_var($customer->email, FILTER_VALIDATE_EMAIL) && ! \App\Models\MarketingPreference::where('customer_id', $customer->id)->whereNotNull('unsubscribed_at')->exists();
    }
}
