<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Tempo massimo permanenza al tavolo</h5>
            </div>
        </div>

        <div class="box">
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
                @endif

                <form wire:submit.prevent="save" class="space-y-5">
                    <div>
                        <label for="table_stay_minutes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tempo massimo permanenza al tavolo (in minuti)</label>
                        <input id="table_stay_minutes" type="number" min="15" max="480" wire:model="table_stay_minutes" class="form-control" placeholder="90">
                        @error('table_stay_minutes') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">Salva</button>
                        <a href="{{ route('settings.index') }}" class="ti-btn ti-btn-light mr-3">Annulla</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
