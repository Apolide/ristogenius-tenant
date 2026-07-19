<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">{{ __('customers.title') }}</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                {{ __('customers.home') }}
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="{{ route('customers.index') }}">{{ __('customers.title') }}</a>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex xl:my-auto right-content align-items-center gap-3 md:gap-5">
                <div class="pe-1 xl:mb-0">
                    <button wire:click="$dispatch('click-create-customer')" type="button" title="{{ __('customers.actions.create') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon">
                        <i class="las text-3xl la-plus"></i>
                    </button>
                </div>
                <div class="pe-1 xl:mb-0">
                    <button wire:click="$dispatch('click-import-customer')" type="button" title="{{ __('customers.actions.import') }}" class="ti-btn ti-btn-danger-full text-white ti-btn-icon">
                        <i class="las text-3xl la-file-import"></i>
                    </button>
                </div>
                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('customers.index') }}" title="{{ __('customers.actions.refresh') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                        <i class="las text-3xl la-redo-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger !mb-5" role="alert">{{ $errors->first() }}</div>
        @endif

        <livewire:customers.customer-import />
        <livewire:customers.customer-create />
        <livewire:customers.customer-delete />

        @if ($isVisible)
            <div class="box">
                <div class="box-body">
                    <div class="grid md:grid-cols-3 gap-3 mt-3 pb-3">
                        <div>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('customers.index.search') }}" class="form-control">
                        </div>
                        <button type="button" class="ti-btn ti-btn-secondary" wire:click="$toggle('showAdvancedFilters')">
                            {{ __('customers.index.advanced_filters') }}
                        </button>
                        <button type="button" wire:click="resetFilters" class="ti-btn ti-btn-danger">
                            {{ __('customers.index.reset_filters') }}
                        </button>
                    </div>

                    @if ($showAdvancedFilters)
                        <div class="border border-defaultborder rounded p-4 mt-3 bg-defaultbackground">
                            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-4 mb-2 mt-3">
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="provinceSelected">{{ __('customers.filters.province') }}</label>
                                    <select wire:model.live.debounce.300ms="provinceSelected" id="provinceSelected" class="form-control">
                                        <option value="">{{ __('customers.filters.all_f') }}</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="comuneSelected">{{ __('customers.filters.municipality') }}</label>
                                    <select wire:model.live.debounce.300ms="comuneSelected" id="comuneSelected" class="form-control">
                                        <option value="">{{ __('customers.filters.all_m') }}</option>
                                        @foreach ($comuniList as $comune)
                                            <option value="{{ $comune->id }}">{{ $comune->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="bookingStatus">{{ __('customers.filters.booking_status') }}</label>
                                    <select wire:model.live.debounce.300ms="bookingStatus" id="bookingStatus" class="form-control">
                                        <option value="">{{ __('customers.filters.all_m') }}</option>
                                        @foreach (['pending','booking_sent','accepted','denied','canceled','no-show','seated','waiting','finalized'] as $status)<option value="{{ $status }}">{{ __('customers.statuses.'.$status) }}</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="noShowCount">{{ __('customers.filters.no_show') }}</label>
                                    <input type="number" wire:model.live.debounce.300ms="noShowCount" id="noShowCount" class="form-control" placeholder="{{ __('customers.filters.example') }}">
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="lastVisitStart">{{ __('customers.filters.visit_from') }}</label>
                                    <input type="date" wire:model.live.debounce.300ms="lastVisitStart" id="lastVisitStart" class="form-control">
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="lastVisitEnd">{{ __('customers.filters.visit_to') }}</label>
                                    <input type="date" wire:model.live.debounce.300ms="lastVisitEnd" id="lastVisitEnd" class="form-control">
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="lastVisitAgo">{{ __('customers.filters.not_visited_since') }}</label>
                                    <select wire:model.live="lastVisitAgo" id="lastVisitAgo" class="form-control">
                                        <option value="">{{ __('customers.filters.none') }}</option>
                                        @foreach ([1,2,3,6,9,12] as $months)<option value="{{ $months }}">{{ trans_choice('customers.filters.month', $months, ['count'=>$months]) }}</option>@endforeach
                                        <option value="12+">{{ __('customers.filters.over_year') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="lastVisitWithin">{{ __('customers.filters.visited_within') }}</label>
                                    <select wire:model.live="lastVisitWithin" id="lastVisitWithin" class="form-control">
                                        <option value="">{{ __('customers.filters.none') }}</option>
                                        @foreach ([1,2,3,6,9,12] as $months)<option value="{{ $months }}">{{ trans_choice('customers.filters.month', $months, ['count'=>$months]) }}</option>@endforeach
                                        <option value="12+">{{ __('customers.filters.over_year') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="birthMonth">{{ __('customers.filters.birth_month') }}</label>
                                    <select wire:model.live.debounce.300ms="birthMonth" id="birthMonth" class="form-control">
                                        <option value="">{{ __('customers.filters.all_m') }}</option>
                                        @foreach (__('customers.months') as $month => $label)
                                            <option value="{{ $month + 1 }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-semibold text-sm" for="birthDay">{{ __('customers.filters.birth_day') }}</label>
                                    <select wire:model.live.debounce.300ms="birthDay" id="birthDay" class="form-control">
                                        <option value="">{{ __('customers.filters.all_m') }}</option>
                                        @for ($day = 1; $day <= 31; $day++)
                                            <option value="{{ $day }}">{{ $day }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-2 mt-3">
                                <div class="flex items-center mt-3 mb-2">
                                    <input type="checkbox" wire:model.live.debounce.300ms="onlyEmail" id="ck_email" class="ti-switch">
                                    <label for="ck_email" class="ltr:ml-3 rtl:mr-3 mb-1 block font-semibold text-sm cursor-pointer">E-Mail</label>
                                </div>
                                <div class="flex items-center mt-3 mb-2">
                                    <input type="checkbox" wire:model.live.debounce.300ms="onlyWhatsapp" id="ck_whatsapp" class="ti-switch">
                                    <label for="ck_whatsapp" class="ltr:ml-3 rtl:mr-3 mb-1 block font-semibold text-sm cursor-pointer">WhatsApp</label>
                                </div>
                                <div class="flex items-center mt-3 mb-2">
                                    <input type="checkbox" wire:model.live.debounce.300ms="onlyTelegram" id="ck_telegram" class="ti-switch">
                                    <label for="ck_telegram" class="ltr:ml-3 rtl:mr-3 mb-1 block font-semibold text-sm cursor-pointer">Telegram</label>
                                </div>
                                <div class="flex items-center mt-3 mb-2">
                                    <input type="checkbox" wire:model.live.debounce.300ms="onlyFidelity" id="ck_fidelity" class="ti-switch">
                                    <label for="ck_fidelity" class="ltr:ml-3 rtl:mr-3 mb-1 block font-semibold text-sm cursor-pointer">Fidelity</label>
                                </div>
                                <div class="flex items-center mt-3 mb-2">
                                    <input type="checkbox" wire:model.live.debounce.300ms="onlyBlacklisted" id="ck_blacklisted" class="ti-switch">
                                    <label class="ltr:ml-3 rtl:mr-3 mb-1 block font-semibold text-sm cursor-pointer" for="ck_blacklisted">Blacklist</label>
                                </div>
                                <div class="flex items-center mt-3 mb-2">
                                    <input type="checkbox" wire:model.live.debounce.300ms="onlyConsentMarketing" id="ck_consent_marketing" class="ti-switch">
                                    <label class="ltr:ml-3 rtl:mr-3 mb-1 block font-semibold text-sm cursor-pointer" for="ck_consent_marketing">Marketing</label>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button" wire:click="resetFilters" class="ti-btn ti-btn-danger">{{ __('customers.index.reset_filters') }}</button>
                                <button type="button" wire:click="sendToMarketing" class="ti-btn ti-btn-primary">{{ __('customers.index.send_marketing') }}</button>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered whitespace-nowrap min-w-full">
                            <thead>
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <th class="border-b dark:border-defaultborder/10 text-start">{{ __('customers.index.customer') }}</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">{{ __('customers.info') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($customers as $customer)
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname) ?: '-' }}<br>
                                            @if ($customer->phone)
                                                <a class="text-primary" href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a><br>
                                            @endif
                                            @if ($customer->email)
                                                {{ $customer->email }}<br>
                                            @endif
                                            <div class="flex space-x-5 mt-3">
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <a href="{{ route('customers.show', $customer->id) }}" class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-info text-white hover:bg-info">
                                                        <i class="las text-3xl la-eye"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">{{ __('customers.actions.view') }}</span>
                                                    </a>
                                                </div>
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <a href="{{ route('customers.edit', $customer->id) }}" class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                        <i class="las text-3xl la-pen"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">{{ __('customers.actions.edit') }}</span>
                                                    </a>
                                                </div>
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-delete-customer', { id: '{{ $customer->id }}' })" class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-danger text-white hover:bg-danger">
                                                        <i class="las text-3xl la-trash"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">{{ __('customers.actions.delete') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            <div class="mb-1">{{ __('customers.index.last_visit') }}: {{ $customer->last_action_at?->format('d-m-Y') ?? '-' }}</div>
                                            <div class="mb-1">Telegram:
                                                @if ($customer->telegramid)
                                                    <span class="badge bg-success/10 !text-success">{{ __('customers.yes') }}</span>
                                                @else
                                                    <span class="badge bg-danger/10 !text-danger">{{ __('customers.no') }}</span>
                                                @endif
                                            </div>
                                            <div class="mb-1">Marketing:
                                                @if ($customer->consent_marketing)
                                                    <span class="badge bg-success/10 !text-success">{{ __('customers.yes') }}</span>
                                                @else
                                                    <span class="badge bg-danger/10 !text-danger">{{ __('customers.no') }}</span>
                                                @endif
                                            </div>
                                            @if ($customer->blacklisted)
                                                <div class="mb-1">Blacklist: <span class="badge bg-danger/10 !text-danger">{{ __('customers.yes') }}</span></div>
                                            @endif
                                            <div class="mb-1">{{ __('customers.language') }}: {{ strtoupper($customer->lang ?? 'it') }}</div>
                                            @if ($customer->region || $customer->province || $customer->comune)
                                                <div class="mb-1">
                                                    {{ __('customers.index.area') }}:
                                                    {{ collect([$customer->region?->name, $customer->province?->name, $customer->comune?->name])->filter()->join(' / ') }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td colspan="2" class="text-center text-sm text-gray-500">{{ __('customers.index.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="box-footer">
                        {{ $customers->links('vendor.livewire.tailwind') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
