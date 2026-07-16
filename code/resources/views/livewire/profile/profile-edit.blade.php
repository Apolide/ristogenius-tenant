<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Profilo Aziendale</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-textmuted" href="javascript:void(0);">Profilo</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="mt-5"></div>
        <div class="box">
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success !mb-5" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <form id="editForm-{{ $profileId }}" wire:submit.prevent="update">
                    <input hidden wire:model="profileId" type="hidden" id="profileId" name="profileId">

                    <div class="w-full mb-5">
                        <label for="image" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Logo</label>
                        <input
                            type="file"
                            wire:model="image"
                            id="image"
                            class="filepond basic-filepond"
                            data-allow-reorder="true"
                            data-max-file-size="10MB"
                            data-max-files="1"
                            accept="image/png, image/jpeg"
                        >
                        <div wire:loading wire:target="image" class="mt-2 text-sm text-textmuted">Caricamento logo...</div>
                        @error('image')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($image)
                        <div class="w-full mb-5">
                            <img src="{{ $image->temporaryUrl() }}" alt="Anteprima logo" class="max-h-40 border border-defaultborder dark:border-defaultborder/10 rounded-sm">
                        </div>
                    @elseif ($profileImage)
                        <div class="w-full mb-5">
                            <img src="{{ $profileImage }}" alt="Logo attività" class="max-h-40 border border-defaultborder dark:border-defaultborder/10 rounded-sm">
                        </div>
                    @endif

                    <div class="w-full mb-5">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome Attività</label>
                        <input disabled wire:model="name" type="text" id="name" class="form-control" placeholder="Nome attività">
                    </div>

                    <div class="w-full mb-5">
                        <label for="company_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome Azienda</label>
                        <textarea wire:model="company_name" id="company_name" class="form-control" placeholder="Nome Azienda"></textarea>
                        @error('company_name')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full mb-5">
                        <label for="city" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Città</label>
                        <textarea wire:model="city" id="city" class="form-control" placeholder="Città"></textarea>
                        @error('city')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full mb-5">
                        <label for="province" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Provincia</label>
                        <textarea wire:model="province" id="province" class="form-control" placeholder="Provincia"></textarea>
                        @error('province')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full mb-5">
                        <label for="postcode" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CAP</label>
                        <textarea wire:model="postcode" id="postcode" class="form-control" placeholder="CAP"></textarea>
                        @error('postcode')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full mb-5">
                        <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Indirizzo</label>
                        <textarea wire:model="address" id="address" class="form-control" placeholder="Indirizzo"></textarea>
                        @error('address')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <button wire:target="update" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">Salva</button>

                    <button wire:click="abort()" type="button" class="ti-btn ti-btn-light mr-3">
                        Annulla
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
