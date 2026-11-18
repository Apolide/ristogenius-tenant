<div>
  @if($isVisible)
  <div class="box">
      <div class="box-header border-none">
          <div class="flex justify-between">
              <div>
                  <div class="box-title pb-0">{{ __('contacts.new_contact') }}</div>
                  {{-- <p class="text-xs text-gray-500 font-normal">Gestisci i tuoi prodotti qui.</p> --}}
              </div>
              

          </div>
      </div> 
      <div class="box-body">
        <form id="createForm" wire:submit.prevent="save">
            <div class="w-full mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('interface.name') }}</label>
                <input required wire:model="name" type="text" name="name" id="name" class="form-control" placeholder="{{ __('contacts.choose_a_contact_name') }}">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="system_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.system_name') }}</label>
                <input required wire:model="system_name" type="text" name="system_name" id="system_name" class="form-control" placeholder="{{ __('contacts.system_name') }}">
                @error('system_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input required wire:model="email" type="email" name="email" id="email" class="form-control" placeholder="Email">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.phone') }}</label>
                <input required wire:model="phone" type="text" name="phone" id="phone" class="form-control" placeholder="{{ __('contacts.phone') }}">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="company_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.company_name') }}</label>
                <input required wire:model="company_name" type="text" name="company_name" id="company_name" class="form-control" placeholder="{{ __('contacts.company_name') }}">
                @error('company_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="piva" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.vat_number') }}</label>
                <input required wire:model="piva" type="text" name="piva" id="piva" class="form-control" placeholder="{{ __('contacts.vat_number') }}">
                @error('piva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>


  

            <div class="grid md:grid-cols-3 grid-cols-1 gap-5">
                <div class="w-full mb-5">
                    <label for="region" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.region') }} @if(!$region_id)<span class="text-danger">*</span>@else<i class="las la-check text-success"></i>@endif</label>
                    <select required wire:model.live="region_id" id="region" class="form-control">
                        <option value="">{{ __('contacts.select_region') }}</option>
                        @foreach($regions as $region)
                            <option value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                        @endforeach
                    </select>
                    @error('region_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                @if(!empty($provinces))
                    <div class="w-full mb-5">
                        <label for="province" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.province') }} @if(!$province_id)<span class="text-danger">*</span>@else<i class="las la-check text-success"></i>@endif</label>
                        <select @if ($province_id) disabled @else required @endif wire:model.live="province_id" id="province" class="form-control">
                            <option value="">{{ __('contacts.select_province') }}</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                            @endforeach
                        </select>
                        @error('province_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif
        
                @if(!empty($comunis))
                    <div class="w-full mb-5">
                        <label for="comuni" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.municipality') }} @if(!$comuni_id)<span class="text-danger">*</span>@else<i class="las la-check text-success"></i>@endif</label>
                        <select @if ($comuni_id) disabled @else required @endif wire:model.live="comuni_id" id="comuni" class="form-control">
                            <option value="">{{ __('contacts.select_municipality') }}</option>
                            @foreach($comunis as $comuni)
                                <option value="{{ $comuni['id'] }}">{{ $comuni['name'] }}</option>
                            @endforeach
                        </select>
                        @error('comuni_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>


            <div class="w-full mb-5">
                <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.address') }}</label>
                <input required wire:model="address" type="text" name="address" id="address" class="form-control" placeholder="{{ __('contacts.address') }}">
                @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="legal_officer" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.legal_representative') }}</label>
                <input required wire:model="legal_officer" type="text" name="legal_officer" id="legal_officer" class="form-control" placeholder="{{ __('contacts.legal_representative') }}">
                @error('legal_officer') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="legal_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.legal_representative_address') }}</label>
                <input required wire:model="legal_address" type="text" name="legal_address" id="legal_address" class="form-control" placeholder="{{ __('contacts.legal_representative_address') }}">
                @error('legal_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="legal_phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.legal_representative_phone') }}</label>
                <input required wire:model="legal_phone" type="text" name="legal_phone" id="legal_phone" class="form-control" placeholder="{{ __('contacts.legal_representative_phone') }}">
                @error('legal_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full mb-5">
                <label for="legal_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('contacts.legal_representative_email') }}</label>
                <input required wire:model="legal_email" type="email" name="legal_email" id="legal_email" class="form-control" placeholder="{{ __('contacts.legal_representative_email') }}">
                @error('legal_email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>


            <button wire:target="save" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
              {{ __('interface.save') }}
            </button>
            <button wire:click="abort()" type="button" class="ti-btn ti-btn-light mr-3">
              {{ __('contacts.cancel') }}
            </button>

            
        </form>
      </div>
      @endif

</div>

