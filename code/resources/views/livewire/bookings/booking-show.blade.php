<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <div><h5 class="page-title text-[1.3125rem] font-medium">{{ __('bookings.title') }}</h5><nav class="text-xs"><a class="text-primary" href="/">Home</a> / {{ __('bookings.detail') }}</nav></div>
        <a href="{{ route('bookings.show', $booking) }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Aggiorna"><i class="las text-xl la-redo-alt"></i></a>
    </div>

    @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif
    <div class="box">
        <div class="box-header border-none"><div class="box-title pb-0">{{ __('bookings.detail_heading') }}</div></div>
        <div class="box-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                <a href="{{ route('bookings.index', ['date' => $booking->booking_date->toDateString()]) }}" class="ti-btn ti-btn-light w-full h-12"><i class="las la-arrow-left"></i> {{ __('bookings.back') }}</a>
                @unless($booking->isWalkIn())<a href="{{ route('bookings.edit', $booking) }}" class="ti-btn ti-btn-warning-full text-white w-full h-12"><i class="las la-edit text-xl"></i> {{ __('bookings.edit') }}</a>@endunless
                @if($booking->status === 'pending')
                    <div class="grid grid-cols-2 gap-2"><button wire:click="accept" wire:confirm="{{ __('bookings.actions.accept_confirm') }}" type="button" class="ti-btn ti-btn-success-full w-full h-12">{{ __('bookings.actions.accept') }}</button><button wire:click="deny" wire:confirm="{{ __('bookings.actions.deny_confirm') }}" type="button" class="ti-btn ti-btn-danger-full w-full h-12">{{ __('bookings.actions.deny') }}</button></div>
                @elseif($booking->status === 'accepted')
                    <button wire:click="showPickTable" type="button" class="ti-btn ti-btn-info-full w-full h-12"><i class="las la-chair text-xl"></i> {{ __('bookings.form.assign_tables') }}</button>
                @else
                    <span></span>
                @endif
            </div>

            @php($status = $statusLabels[$booking->status] ?? ['label' => $booking->status, 'color' => 'secondary'])
            <div class="table-responsive"><table class="table table-bordered min-w-full"><tbody>
                <tr><td class="font-bold w-1/3">{{ __('bookings.show.status') }}</td><td><span class="badge bg-{{ $status['color'] }}/10 text-{{ $status['color'] }}">{{ __('bookings.statuses.'.$booking->status) }}</span></td></tr>
                <tr><td class="font-bold">{{ __('bookings.show.datetime') }}</td><td>{{ $booking->booking_date->format('d/m/Y') }} {{ __('bookings.show.at') }} {{ substr((string)$booking->booking_time, 0, 5) }}</td></tr>
                <tr><td class="font-bold">{{ __('bookings.show.people') }}</td><td>{{ $booking->pax }}</td></tr>
                <tr><td class="font-bold">{{ __('bookings.show.tables') }}</td><td>@forelse($booking->tables as $table)<span class="badge bg-secondary/10 text-black mr-1">{{ $table->name }} · {{ $table->room?->name }}</span>@empty {{ __('bookings.show.unassigned') }} @endforelse</td></tr>
                <tr><td class="font-bold">Cliente</td><td>
                    @if($booking->customer)
                        <div><span title="{{ $bookingLanguage['label'] }}" aria-label="Lingua: {{ $bookingLanguage['label'] }}" class="mr-1">{{ $bookingLanguage['flag'] }}</span><a class="text-primary" href="{{ route('customers.show', $booking->customer) }}"><b>{{ $booking->customer->display_name ?: trim($booking->customer->firstname.' '.$booking->customer->lastname) }}</b></a></div>
                        @if($booking->customer->phone)<div><a class="text-primary" href="tel:{{ $booking->customer->phone }}"><i class="las la-phone"></i> {{ $booking->customer->phone }}</a></div>@endif
                        @if($booking->customer->email)<div><a class="text-primary" href="mailto:{{ $booking->customer->email }}"><i class="las la-envelope"></i> {{ $booking->customer->email }}</a></div>@endif
                        @if($booking->customer->note)<div class="mt-2 text-textmuted"><b>Note cliente:</b> {{ $booking->customer->note }}</div>@endif
                    @else <b>Walk In</b> @endif
                </td></tr>
                <tr><td class="font-bold">{{ __('bookings.booking_notes') }}</td><td>{{ $booking->note ?: '-' }}</td></tr>
                @if($booking->restaurant_note)<tr><td class="font-bold">{{ __('bookings.form.restaurant_note') }}</td><td>{{ $booking->restaurant_note }}</td></tr>@endif
                <tr><td class="font-bold">Origine</td><td><span class="badge bg-primary/10 text-primary">{{ ucfirst($booking->source) }}</span></td></tr>
                <tr><td class="font-bold">Inserita il</td><td>{{ $booking->created_at?->format('d/m/Y H:i:s') }}</td></tr>
            </tbody></table></div>

            @if($customFormFields->isNotEmpty())
                <div class="mt-8">
                    <h4 class="text-base font-semibold mb-3">Campi personalizzati del form</h4>
                    @if($sourceForm)<p class="text-sm text-textmuted mb-3">Form: {{ $sourceForm->title($booking->language) }}</p>@endif
                    <div class="table-responsive"><table class="table table-bordered min-w-full"><tbody>
                        @foreach($customFormFields as $field)
                            <tr><td class="font-bold w-1/3">{{ $field['label'] }}</td><td>
                                @if(is_array($field['value']))
                                    {{ $field['value'] !== [] ? implode(', ', $field['value']) : '-' }}
                                @elseif(is_bool($field['value']))
                                    {{ $field['value'] ? 'Sì' : 'No' }}
                                @else
                                    {{ filled($field['value']) ? $field['value'] : '-' }}
                                @endif
                            </td></tr>
                        @endforeach
                    </tbody></table></div>
                </div>
            @endif

            <div class="mt-8">
                <h4 class="text-base font-semibold mb-3">{{ __('bookings.show.history') }}</h4>
                <div class="table-responsive"><table class="table table-bordered min-w-full"><thead><tr><th>{{ __('bookings.show.date') }}</th><th>{{ __('bookings.show.event') }}</th><th>{{ __('bookings.show.author') }}</th><th>{{ __('bookings.show.details') }}</th></tr></thead><tbody>
                    @forelse($booking->histories as $history)<tr><td>{{ $history->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $history->event === 'booking_edited_from_customer' ? __('public_bookings.history.customer_edited') : ($history->event === 'booking_proposal' ? __('message_channel_cases.cases.booking_proposal') : (in_array($history->event, ['booking_canceled', 'booking_canceled_from_customer'], true) ? __('bookings.show.events.booking_canceled') : __("bookings.show.events.{$history->event}"))) }}</td><td>{{ $history->actor ?: '-' }}</td><td>{{ $history->description ?: '-' }}
                        @if($history->changes)<div class="text-xs text-textmuted mt-1">@foreach($history->changes as $field => $change)<div><b>{{ $field }}</b>: {{ is_array($change['from'] ?? null) ? implode(', ', $change['from']) : ($change['from'] ?? '-') }} → {{ is_array($change['to'] ?? null) ? implode(', ', $change['to']) : ($change['to'] ?? '-') }}</div>@endforeach</div>@endif
                    </td></tr>@empty<tr><td colspan="4" class="text-center">{{ __('bookings.show.no_history') }}</td></tr>@endforelse
                </tbody></table></div>
            </div>

            @if($booking->customer)
                <livewire:customers.customer-booking-history :customer-id="$booking->customer_id" :exclude-booking-id="$booking->id" :key="'booking-customer-history-'.$booking->id" />
            @endif
        </div>
    </div>

    @if($showPickTableModal) @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'saveSelectedTables', 'closeAction' => 'closePickTableModal']) @endif
</div></div>
