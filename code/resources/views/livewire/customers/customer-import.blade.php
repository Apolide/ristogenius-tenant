<div>
    @if ($isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">IMPORTA CLIENTI DA CSV</div>
            </div>
            <div class="box-body">
                <form wire:submit.prevent="import">
                    <div class="w-full mb-5">
                        <label for="file" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">File CSV</label>
                        <input id="file" type="file" wire:model="file" class="filepond basic-filepond" data-allow-reorder="true" data-max-file-size="10MB" data-max-files="1" accept=".csv,text/csv,application/vnd.ms-excel">
                        @error('file') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <button wire:target="import" wire:loading.attr="disabled" wire:loading.class.add="ti-btn-secondary-full ti-btn-disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
                        <span wire:loading.remove>Sincronizza</span>
                        <span wire:loading class="ti-spinner text-white" role="status" aria-label="loading"></span>
                        <span wire:loading>Caricamento...</span>
                    </button>

                    <button wire:click="abort" wire:loading.remove wire:loading.class.add="!hidden" type="button" class="ti-btn ti-btn-light mr-3">Annulla</button>
                </form>
            </div>
        </div>

        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">DOCUMENTAZIONE</div>
            </div>
            <div class="box-body">
                <h4>Importazione clienti tramite CSV</h4>
                <p>Questa schermata prepara il caricamento massivo dei clienti. La funzionalita di importazione verra implementata successivamente.</p>
                <div class="m-5"></div>
                <h4>Campi consigliati</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Descrizione</th>
                            <th>Esempio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>firstname</code></td>
                            <td>Nome del cliente.</td>
                            <td>Mario</td>
                        </tr>
                        <tr>
                            <td><code>lastname</code></td>
                            <td>Cognome del cliente.</td>
                            <td>Rossi</td>
                        </tr>
                        <tr>
                            <td><code>phone</code></td>
                            <td>Telefono in formato internazionale quando possibile.</td>
                            <td>+393331112222</td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td>Indirizzo email del cliente.</td>
                            <td>mario.rossi@example.com</td>
                        </tr>
                        <tr>
                            <td><code>birthdate</code></td>
                            <td>Data di nascita in formato ISO.</td>
                            <td>1985-04-20</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
