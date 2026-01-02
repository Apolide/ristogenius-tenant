<div class="lg:col-span-8 col-span-12">
    <div class="card bg-white dark:bg-bodybg !shadow-none rounded-sm">
        <div class="box-body px-[3rem] py-[2.4rem]">
            <form wire:submit.prevent="submit">
                <div class="grid grid-cols-12 gap-x-6 gap-y-6 mt-1 mb-3">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="form-group">
                            <label for="cusName" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input required wire:model.lazy="firstname" type="text" class="form-control" id="cusName" placeholder="Inserisci il tuo nome">
                            @error('firstname')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="form-group">
                            <label for="cusName" class="form-label">Cognome <span class="text-danger">*</span></label>
                            <input required wire:model.lazy="lastname" type="text" class="form-control" id="cusName" placeholder="Inserisci il tuo cognome">
                            @error('lastname')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-x-6 gap-y-6 mt-1 mb-3">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="form-group">
                            <label for="cusEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input required wire:model.lazy="email" type="text" class="form-control" id="cusEmail" placeholder="Inserisci la tua email">
                            @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="form-group">
                            <label for="cusName" class="form-label">Telefono <span class="text-danger">*</span></label>
                            <input required wire:model.lazy="phone" type="text" class="form-control" id="cusName" placeholder="Inserisci il tuo telefono">
                            @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cusMessage" class="form-label">Messaggio <span class="text-danger">*</span></label>
                    <textarea required wire:model.lazy="body" rows="4" class="form-control" id="cusMessage" placeholder="Scrivi il tuo messaggio qui..."></textarea>
                    @error('body')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if (session()->has('success_contacts_message'))
                    <div class="w-full mt-3 mb-3 text-green-600">
                        {{ session('success_contacts_message') }}
                    </div>
                @endif

                <div class="form-group mb-2 pt-1">
                    <button wire:target="submit" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full">Invia messaggio</button>
                </div>
            </form>
        </div>
    </div>
</div>




