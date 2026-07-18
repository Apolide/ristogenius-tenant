<div>
    @if ($room && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">{{ __('room_settings.rooms.title') }}</div>
                        <p class="text-xs text-gray-500 font-normal">{{ $room->name }}</p>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="table-responsive mb-5">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.name') }}</th>
                                <td>{{ $room->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.active') }}</th>
                                <td>{{ $room->active ? __('room_settings.yes') : __('room_settings.no') }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.service_charge') }}</th>
                                <td>{{ number_format((float) $room->service_charge, 2, ',', '.') }} &euro;</td>
                            </tr>
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.service_charge_percentage') }}</th>
                                <td>{{ number_format((float) $room->service_charge_percentage, 2, ',', '.') }} %</td>
                            </tr>
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.order') }}</th>
                                <td>{{ $room->order }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.capacity') }}</th>
                                <td>{{ $room->capacity }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">{{ __('room_settings.rooms.smoking') }}</th>
                                <td>{{ $room->smoking_allowed ? __('room_settings.yes') : __('room_settings.no') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button wire:click="back" type="button" class="ti-btn ti-btn-light">
                    {{ __('room_settings.cancel') }}
                </button>
            </div>
        </div>
    @endif
</div>
