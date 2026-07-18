<div>
    @if ($isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">{{ __('customers.import.title') }}</div>
            </div>
            <div class="box-body">
                <form wire:submit.prevent="import">
                    <div class="w-full mb-5">
                        <label for="file" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.import.file') }}</label>
                        <input id="file" type="file" wire:model="file" class="filepond basic-filepond" data-allow-reorder="true" data-max-file-size="10MB" data-max-files="1" accept=".csv,text/csv,application/vnd.ms-excel">
                        @error('file') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <button wire:target="import" wire:loading.attr="disabled" wire:loading.class.add="ti-btn-secondary-full ti-btn-disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
                        <span wire:loading.remove>{{ __('customers.import.sync') }}</span>
                        <span wire:loading class="ti-spinner text-white" role="status" aria-label="{{ __('customers.import.loading') }}"></span>
                        <span wire:loading>{{ __('customers.import.loading') }}</span>
                    </button>

                    <button wire:click="abort" wire:loading.remove wire:loading.class.add="!hidden" type="button" class="ti-btn ti-btn-light mr-3">{{ __('customers.cancel') }}</button>
                </form>
            </div>
        </div>

        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">{{ __('customers.import.documentation') }}</div>
            </div>
            <div class="box-body">
                <h4>{{ __('customers.import.heading') }}</h4>
                <p>{{ __('customers.import.intro') }}</p>
                <div class="m-5"></div>
                <h4>{{ __('customers.import.recommended') }}</h4>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('customers.import.field') }}</th>
                            <th>{{ __('customers.import.description') }}</th>
                            <th>{{ __('customers.import.example') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>firstname</code></td>
                            <td>{{ __('customers.import.firstname') }}</td>
                            <td>Mario</td>
                        </tr>
                        <tr>
                            <td><code>lastname</code></td>
                            <td>{{ __('customers.import.lastname') }}</td>
                            <td>Rossi</td>
                        </tr>
                        <tr>
                            <td><code>phone</code></td>
                            <td>{{ __('customers.import.phone') }}</td>
                            <td>+393331112222</td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td>{{ __('customers.import.email') }}</td>
                            <td>mario.rossi@example.com</td>
                        </tr>
                        <tr>
                            <td><code>birthdate</code></td>
                            <td>{{ __('customers.import.birthdate') }}</td>
                            <td>1985-04-20</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
