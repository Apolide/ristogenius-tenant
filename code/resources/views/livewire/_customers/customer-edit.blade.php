<div>

    @if($contactId && $isVisible)

        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">MODIFICA {{$name}}</div>
                        {{-- <p class="text-xs text-gray-500 font-normal">Gestisci i tuoi prodotti qui.</p> --}}
                    </div>
                    
        
                </div>
            </div>

            <div class="box-body">
                <form id="editForm-{{$contactId}}" wire:submit.prevent="update">
                    <input hidden wire:model="contactId" type="hidden" id="contactId" name="contactId" value="{{$contactId}}">

                    <div class="w-full mb-5">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                        <input disabled wire:model="name" type="text" name="name" id="name" class="form-control" placeholder="Scegli un nome per il contatto">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="system_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome di sistema</label>
                        <input disabled wire:model="system_name" type="text" name="system_name" id="system_name" class="form-control" placeholder="Nome di sistema">
                        @error('system_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input required wire:model="email" type="email" name="email" id="email" class="form-control" placeholder="Email">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full mb-5">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Telefono</label>
                        <input required wire:model="phone" type="text" name="phone" id="phone" class="form-control" placeholder="Telefono">
                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
        
                    <div class="w-full mb-5">
                        <label for="company_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ragione sociale</label>
                        <input required wire:model="company_name" type="text" name="company_name" id="company_name" class="form-control" placeholder="Ragione sociale">
                        @error('company_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="piva" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Partita iva</label>
                        <input required wire:model="piva" type="text" name="piva" id="piva" class="form-control" placeholder="Partita iva">
                        @error('piva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full mb-5">
                        @if (!$this->riferimento_mandato)
                            <button wire:click="generateRandomString" wire:loading.attr="disabled" type="button" class="ti-btn ti-btn-primary ti-btn-wave me-[0.375rem]">Genera riferimento</button>

                        @endif
                        <label for="riferimento_mandato" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Riferimento mandato</label>
                        <input required wire:model="riferimento_mandato" type="text" name="riferimento_mandato" id="riferimento_mandato" class="form-control" placeholder="Riferimento mandato">
                        @error('riferimento_mandato') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
        
          
        
                    <div class="grid md:grid-cols-3 grid-cols-1 gap-5">
                        <div class="w-full mb-5">
                            <label for="region" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Regione @if(!$region_id)<span class="text-danger">*</span>@else<i class="las la-check text-success"></i>@endif</label>
                            <select required wire:model.live="region_id" id="region" class="form-control">
                                <option value="">-- Seleziona Regione --</option>
                                @foreach($regions as $region)
                                    <option @if($region_id == $region["id"]) selected @endif value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                                @endforeach
                            </select>
                            @error('region_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
        
                        @if(!empty($provinces))
                            <div class="w-full mb-5">
                                <label for="province" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Provincia @if(!$province_id)<span class="text-danger">*</span>@else<i class="las la-check text-success"></i>@endif</label>
                                <select @if ($province_id) disabled @else required @endif wire:model.live="province_id" id="province" class="form-control">
                                    <option value="">-- Seleziona Provincia --</option>
                                    @foreach($provinces as $province)
                                        <option @if($province_id == $province["id"]) selected @endif value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('province_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                
                        @if(!empty($comunis))
                            <div class="w-full mb-5">
                                <label for="comuni" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Comune @if(!$comuni_id)<span class="text-danger">*</span>@else<i class="las la-check text-success"></i>@endif</label>
                                <select @if ($comuni_id) disabled @else required @endif wire:model.live="comuni_id" id="comuni" class="form-control">
                                    <option value="">-- Seleziona Comune --</option>
                                    @foreach($comunis as $comuni)
                                        <option @if($comuni_id == $comuni["id"]) selected @endif value="{{ $comuni['id'] }}">{{ $comuni['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('comuni_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
        
        
                    <div class="w-full mb-5">
                        <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Indirizzo</label>
                        <input required wire:model="address" type="text" name="address" id="address" class="form-control" placeholder="Indirizzo">
                        @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="legal_officer" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rappresentante legale</label>
                        <input required wire:model="legal_officer" type="text" name="legal_officer" id="legal_officer" class="form-control" placeholder="Rappresentante legale">
                        @error('legal_officer') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="legal_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Indirizzo reappresentante legale</label>
                        <input required wire:model="legal_address" type="text" name="legal_address" id="legal_address" class="form-control" placeholder="Indirizzo reappresentante legale">
                        @error('legal_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="legal_phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Telefono reappresentante legale</label>
                        <input required wire:model="legal_phone" type="text" name="legal_phone" id="legal_phone" class="form-control" placeholder="Telefono reappresentante legale">
                        @error('legal_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
        
                    <div class="w-full mb-5">
                        <label for="legal_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email reappresentante legale</label>
                        <input required wire:model="legal_email" type="email" name="legal_email" id="legal_email" class="form-control" placeholder="Email reappresentante legale">
                        @error('legal_email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <button wire:target="update" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">Salva</button>

                    <button wire:click="abort()" type="button" class="ti-btn ti-btn-light mr-3">
                        Annulla
                    </button>

                
            </div>
        </div>

    @endif
    {{-- @script
    <script>
    
      document.getElementById('submitEditBtn-'+'{{$contactId}}').addEventListener('click', function() {
        const form = document.getElementById('editForm-'+'{{$contactId}}');
        const formData = new FormData(form);
        console.log(formData.getAll());
        $wire.dispatch('product-updated' , { id: '{{$contactId}}', data : formData.getAll() });
      });
    
    </script>
    @endscript --}}
    


</div>






