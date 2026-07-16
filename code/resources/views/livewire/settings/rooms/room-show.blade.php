<div>
    @if ($room && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">DETTAGLIO SALA</div>
                        <p class="text-xs text-gray-500 font-normal">{{ $room->name }}</p>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="table-responsive mb-5">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="text-start">Nome</th>
                                <td>{{ $room->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Attivo</th>
                                <td>{{ $room->active ? 'Si' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Costo servizio</th>
                                <td>{{ number_format((float) $room->service_charge, 2, ',', '.') }} &euro;</td>
                            </tr>
                            <tr>
                                <th class="text-start">Costo servizio %</th>
                                <td>{{ number_format((float) $room->service_charge_percentage, 2, ',', '.') }} %</td>
                            </tr>
                            <tr>
                                <th class="text-start">Ordinamento</th>
                                <td>{{ $room->order }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Capienza</th>
                                <td>{{ $room->capacity }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Fumatori</th>
                                <td>{{ $room->smoking_allowed ? 'Si' : 'No' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button wire:click="back" type="button" class="ti-btn ti-btn-light">
                    Indietro
                </button>
            </div>
        </div>
    @endif
</div>
