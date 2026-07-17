<div class="content">
<div class="main-content">
    <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
        <div class="my-auto">
            <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">{{ __('personnel.title') }}</h5>
            <nav><ol class="flex items-center whitespace-nowrap min-w-0">
                <li class="text-[12px]"><a class="flex items-center text-primary hover:text-primary" href="{{ route('personnel.index') }}">{{ __('personnel.title') }} <i class="ti ti-chevrons-right mx-3 text-textmuted"></i></a></li>
                <li class="text-[12px] text-textmuted">{{ __('personnel.edit') }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('personnel.edit', $userId) }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon"><i class="las text-3xl la-redo-alt"></i></a>
    </div>

    <div class="box">
        <div class="box-header border-none">
            <div class="box-title pb-0">{{ __('personnel.edit_employee') }}</div>
        </div>

        <div class="box-body">
            <form wire:submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="w-full mb-3">
                        <label for="name" class="block mb-2 text-sm font-medium">{{ __('personnel.name') }}</label>
                        <input required wire:model="name" type="text" id="name" class="form-control">
                        @error('name') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="w-full mb-3">
                        <label for="email" class="block mb-2 text-sm font-medium">E-mail</label>
                        <input required wire:model="email" type="email" id="email" class="form-control">
                        @error('email') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="w-full mb-3">
                        <label for="phone" class="block mb-2 text-sm font-medium">{{ __('personnel.phone') }}</label>
                        <input required wire:model="phone" type="tel" id="phone" class="form-control">
                        @error('phone') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="w-full mb-3">
                        <label for="role" class="block mb-2 text-sm font-medium">{{ __('personnel.role') }}</label>
                        <select required wire:model="role" id="role" class="ti-form-select rounded-sm !py-2 !px-3">
                            <option value="">{{ __('personnel.select_role') }}</option>
                            @foreach ($role_list as $singleRole)
                                <option value="{{ $singleRole }}">{{ __("personnel.roles.{$singleRole}") }}</option>
                            @endforeach
                        </select>
                        @error('role') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="w-full mb-3">
                        <label for="lang" class="block mb-2 text-sm font-medium">{{ __('personnel.language') }}</label>
                        <select required wire:model="lang" id="lang" class="ti-form-select rounded-sm !py-2 !px-3">
                            @foreach ($language_list as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('lang') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="w-full mb-5">
                    <input wire:model="receive_whatsapp_notifications" type="checkbox" id="receive-whatsapp" class="ti-switch">
                    <label for="receive-whatsapp" class="text-sm text-gray-500 ltr:ml-3 rtl:mr-3 cursor-pointer">{{ __('personnel.whatsapp') }}</label>
                </div>

                <div class="w-full mb-5">
                    <input wire:model="receive_telegram_notifications" type="checkbox" id="receive-telegram" class="ti-switch">
                    <label for="receive-telegram" class="text-sm text-gray-500 ltr:ml-3 rtl:mr-3 cursor-pointer">{{ __('personnel.telegram') }}</label>
                </div>

                <button wire:target="submit" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
                    <span wire:loading.remove wire:target="submit">{{ __('personnel.save') }}</span>
                    <span wire:loading wire:target="submit">{{ __('personnel.saving') }}</span>
                </button>
                <a href="{{ route('personnel.index') }}" class="ti-btn ti-btn-light ti-btn-wave">{{ __('personnel.cancel') }}</a>
            </form>
        </div>
    </div>
</div>
</div>
