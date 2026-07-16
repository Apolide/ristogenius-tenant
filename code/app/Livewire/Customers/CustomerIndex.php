<?php

namespace App\Livewire\Customers;

use App\Models\Comuni;
use App\Models\Province;
use App\Services\Customer\CustomerService;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'showAdvancedFilters', except: false)]
    public bool $showAdvancedFilters = false;

    #[Url(as: 'onlyBlacklisted', except: false)]
    public bool $onlyBlacklisted = false;

    #[Url(as: 'onlyFidelity', except: false)]
    public bool $onlyFidelity = false;

    #[Url(as: 'onlyTelegram', except: false)]
    public bool $onlyTelegram = false;

    #[Url(as: 'onlyEmail', except: false)]
    public bool $onlyEmail = false;

    #[Url(as: 'onlyWhatsapp', except: false)]
    public bool $onlyWhatsapp = false;

    #[Url(as: 'onlyConsentMarketing', except: false)]
    public bool $onlyConsentMarketing = false;

    #[Url(as: 'provinceSelected', except: '')]
    public string $provinceSelected = '';

    #[Url(as: 'comuneSelected', except: '')]
    public string $comuneSelected = '';

    #[Url(as: 'bookingStatus', except: '')]
    public string $bookingStatus = '';

    #[Url(as: 'noShowCount', except: '')]
    public string $noShowCount = '';

    #[Url(as: 'lastVisitStart', except: null)]
    public ?string $lastVisitStart = null;

    #[Url(as: 'lastVisitEnd', except: null)]
    public ?string $lastVisitEnd = null;

    #[Url(as: 'lastVisitAgo', except: '')]
    public string $lastVisitAgo = '';

    #[Url(as: 'lastVisitWithin', except: '')]
    public string $lastVisitWithin = '';

    #[Url(as: 'birthMonth', except: '')]
    public string $birthMonth = '';

    #[Url(as: 'birthDay', except: '')]
    public string $birthDay = '';

    public bool $isVisible = true;

    public function updating($property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function updatedProvinceSelected(): void
    {
        $this->comuneSelected = '';
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'showAdvancedFilters',
            'onlyBlacklisted',
            'onlyFidelity',
            'onlyTelegram',
            'onlyEmail',
            'onlyWhatsapp',
            'onlyConsentMarketing',
            'provinceSelected',
            'comuneSelected',
            'bookingStatus',
            'noShowCount',
            'lastVisitStart',
            'lastVisitEnd',
            'lastVisitAgo',
            'lastVisitWithin',
            'birthMonth',
            'birthDay',
        ]);

        $this->resetPage();
    }

    public function sendToMarketing(): void
    {
        session()->flash('success', 'La funzionalita marketing sara implementata successivamente.');
    }

    #[On('customer-refresh')]
    public function refreshView(): void
    {
        $this->resetPage();
    }

    #[On('show-listing')]
    public function showListing(): void
    {
        $this->isVisible = true;
    }

    #[On('hide-listing')]
    public function hideListing(): void
    {
        $this->isVisible = false;
    }

    public function render(CustomerService $customerService)
    {
        return view('livewire.customers.customer-index', [
            'customers' => $customerService->getCustomers($this->filters()),
            'provinces' => Province::query()->orderBy('name')->get(),
            'comuniList' => $this->provinceSelected !== ''
                ? Comuni::query()->where('province_id', $this->provinceSelected)->orderBy('name')->get()
                : collect(),
        ])->title('Clienti');
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'onlyBlacklisted' => $this->onlyBlacklisted,
            'onlyFidelity' => $this->onlyFidelity,
            'onlyTelegram' => $this->onlyTelegram,
            'onlyEmail' => $this->onlyEmail,
            'onlyWhatsapp' => $this->onlyWhatsapp,
            'onlyConsentMarketing' => $this->onlyConsentMarketing,
            'provinceSelected' => $this->provinceSelected,
            'comuneSelected' => $this->comuneSelected,
            'bookingStatus' => $this->bookingStatus,
            'noShowCount' => $this->noShowCount,
            'lastVisitStart' => $this->lastVisitStart,
            'lastVisitEnd' => $this->lastVisitEnd,
            'lastVisitAgo' => $this->lastVisitAgo,
            'lastVisitWithin' => $this->lastVisitWithin,
            'birthMonth' => $this->birthMonth,
            'birthDay' => $this->birthDay,
        ];
    }
}
