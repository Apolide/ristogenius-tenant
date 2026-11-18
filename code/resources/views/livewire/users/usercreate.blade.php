<div>

    <div class="box">
        <div class="box-header border-none">
            <div class="flex justify-between">
                <div>
                    <div class="box-title pb-0">{{ __('users.new_user') }}</div>
                    <p class="text-xs text-gray-500 font-normal">{{ __('users.user_management_table') }}</p>
                </div>

            </div>

        </div>


        <div class="box-body">

                
            <form wire:submit.prevent="submit">


                <div class="w-full mb-5">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('interface.name') }}</label>
                    <input required wire:model="name" type="text" name="name" id="name" class="form-control" placeholder="{{ __('users.name_placeholder') }}">
                </div>

                <div class="w-full mb-5">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('interface.email') }}</label>
                    <input required wire:model="email" type="email" name="email" id="email" class="form-control" placeholder="{{ __('users.email_placeholder') }}">
                </div>

                <div class="w-full mb-5">
                    <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('users.role') }}</label>
                    <select required wire:model="role" id="role" class="ti-form-select rounded-sm !py-2 !px-3">
                        <option value="">{{ __('users.select_role') }}</option>
                        @foreach ($role_list as $singleRole)
                            <option value="{{$singleRole}}">{{$singleRole}}</option>
                        @endforeach
                    </select>
                </div>

                
                <button wire:target="submit" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave  me-[0.375rem]">
                    {{ __('interface.save') }}
                </button>
            </form>


        </div>
    </div>
    
</div>
