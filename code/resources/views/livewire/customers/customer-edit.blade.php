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
                    <a href="{{ route('customers.show', $customerId) }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                        <i class="las text-3xl la-redo-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
        @endif

        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">{{ __('customers.form.edit') }}</div>
            </div>
            <div class="box-body">
                <a href="{{ route('customers.index') }}" class="ti-btn ti-btn-light w-full h-12">{{ __('customers.back') }}</a>
                <div class="mb-3"></div>

                <form wire:submit.prevent="update">
                    <input hidden wire:model="customerId" type="hidden" id="customerId" name="customerId">
                    @include('livewire.customers.partials.customer-form')

                    <div class="w-full mb-5">
                        <input wire:model.live="blacklisted" type="checkbox" id="checkbox-blacklisted" class="ti-switch">
                        <label for="checkbox-blacklisted" class="text-sm text-gray-500 ltr:ml-3 rtl:mr-3 dark:text-white/70 cursor-pointer">{{ __('customers.form.blacklisted') }}</label>
                    </div>

                    <button wire:target="update" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">{{ __('customers.save') }}</button>
                    <a href="{{ route('customers.index') }}" class="ti-btn ti-btn-light mr-3">{{ __('customers.back') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>
