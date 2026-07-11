<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <div><h5 class="page-title text-[1.3125rem] font-medium">Prenotazioni</h5><nav class="text-xs"><a class="text-primary" href="/">Home</a> / Dettaglio prenotazione</nav></div>
        <a href="{{ route('bookings.show', $booking) }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Aggiorna"><i class="las text-xl la-redo-alt"></i></a>
    </div>

    @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif
    <div class="box">
        <div class="box-header border-none"><div class="box-title pb-0">DETTAGLIO PRENOTAZIONE</div></div>
        <div class="box-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                <a href="{{ route('bookings.index', ['date' => $booking->booking_date->toDateString()]) }}" class="ti-btn ti-btn-light w-full h-12"><i class="las la-arrow-left"></i> Indietro</a>
                @unless($booking->isWalkIn())<a href="{{ route('bookings.edit', $booking) }}" class="ti-btn ti-btn-warning-full text-white w-full h-12"><i class="las la-edit text-xl"></i> Modifica</a>@endunless
                <button wire:click="showPickTable" type="button" class="ti-btn ti-btn-info-full w-full h-12"><i class="las la-chair text-xl"></i> Assegna tavoli</button>
            </div>

            @php($status = $statusLabels[$booking->status] ?? ['label' => $booking->status, 'color' => 'secondary'])
            <div class="table-responsive"><table class="table table-bordered min-w-full"><tbody>
                <tr><td class="font-bold w-1/3">Stato</td><td><span class="badge bg-{{ $status['color'] }}/10 text-{{ $status['color'] }}">{{ $status['label'] }}</span></td></tr>
                <tr><td class="font-bold">Data e ora</td><td>{{ $booking->booking_date->format('d/m/Y') }} alle {{ substr((string)$booking->booking_time, 0, 5) }}</td></tr>
                <tr><td class="font-bold">Persone</td><td>{{ $booking->pax }}</td></tr>
                <tr><td class="font-bold">Tavoli</td><td>@forelse($booking->tables as $table)<span class="badge bg-secondary/10 text-black mr-1">{{ $table->name }} · {{ $table->room?->name }}</span>@empty Non assegnati @endforelse</td></tr>
                <tr><td class="font-bold">Cliente</td><td>
                    @if($booking->customer)
                        <div><span title="{{ $bookingLanguage['label'] }}" aria-label="Lingua: {{ $bookingLanguage['label'] }}" class="mr-1">{{ $bookingLanguage['flag'] }}</span><a class="text-primary" href="{{ route('customers.show', $booking->customer) }}"><b>{{ $booking->customer->display_name ?: trim($booking->customer->firstname.' '.$booking->customer->lastname) }}</b></a></div>
                        @if($booking->customer->phone)<div><a class="text-primary" href="tel:{{ $booking->customer->phone }}"><i class="las la-phone"></i> {{ $booking->customer->phone }}</a></div>@endif
                        @if($booking->customer->email)<div><a class="text-primary" href="mailto:{{ $booking->customer->email }}"><i class="las la-envelope"></i> {{ $booking->customer->email }}</a></div>@endif
                        @if($booking->customer->note)<div class="mt-2 text-textmuted"><b>Note cliente:</b> {{ $booking->customer->note }}</div>@endif
                    @else <b>Walk In</b> @endif
                </td></tr>
                <tr><td class="font-bold">Note prenotazione</td><td>{{ $booking->note ?: '-' }}</td></tr>
                <tr><td class="font-bold">Origine</td><td><span class="badge bg-primary/10 text-primary">{{ ucfirst($booking->source) }}</span></td></tr>
                <tr><td class="font-bold">Inserita il</td><td>{{ $booking->created_at?->format('d/m/Y H:i:s') }}</td></tr>
            </tbody></table></div>

            <div class="mt-8">
                <h4 class="text-base font-semibold mb-3">Cronologia modifiche prenotazione</h4>
                <div class="table-responsive"><table class="table table-bordered min-w-full"><thead><tr><th>Data</th><th>Evento</th><th>Autore</th><th>Dettagli</th></tr></thead><tbody>
                    @forelse($booking->histories as $history)<tr><td>{{ $history->created_at->format('d/m/Y H:i:s') }}</td><td>{{ ['created'=>'Inserita','updated'=>'Modificata','status_changed'=>'Cambio stato','tables_changed'=>'Tavoli modificati','deleted'=>'Eliminata'][$history->event] ?? ucfirst($history->event) }}</td><td>{{ $history->actor ?: '-' }}</td><td>{{ $history->description ?: '-' }}
                        @if($history->changes)<div class="text-xs text-textmuted mt-1">@foreach($history->changes as $field => $change)<div><b>{{ $field }}</b>: {{ is_array($change['from'] ?? null) ? implode(', ', $change['from']) : ($change['from'] ?? '-') }} → {{ is_array($change['to'] ?? null) ? implode(', ', $change['to']) : ($change['to'] ?? '-') }}</div>@endforeach</div>@endif
                    </td></tr>@empty<tr><td colspan="4" class="text-center">Nessuna modifica registrata.</td></tr>@endforelse
                </tbody></table></div>
            </div>

            @if($booking->customer)
            <div class="mt-8">
                <h4 class="text-base font-semibold mb-2">Storico prenotazioni cliente ({{ $customerBookings->count() }})</h4>
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="badge bg-success/10 text-success">Consumate: {{ $customerBookings->where('status','finalized')->count() }}</span>
                    <span class="badge bg-danger/10 text-danger">Cancellate/eliminate: {{ $customerBookings->filter(fn($item) => $item->trashed() || $item->status === 'canceled')->count() }}</span>
                    <span class="badge bg-danger/10 text-danger">Rifiutate: {{ $customerBookings->where('status','denied')->count() }}</span>
                    <span class="badge bg-warning/10 text-warning">No show: {{ $customerBookings->where('status','no-show')->count() }}</span>
                </div>
                <div class="table-responsive"><table class="table table-bordered min-w-full"><thead><tr><th>Data</th><th>Orario</th><th>Pax</th><th>Stato</th><th>Note</th></tr></thead><tbody>
                    @forelse($customerBookings as $item) @php($itemStatus = $item->trashed() ? ['label'=>'Eliminata','color'=>'danger'] : ($statusLabels[$item->status] ?? ['label'=>$item->status,'color'=>'secondary']))
                    <tr><td>{{ $item->booking_date->format('d/m/Y') }}</td><td>{{ substr((string)$item->booking_time,0,5) }}</td><td>{{ $item->pax }}</td><td><span class="badge bg-{{ $itemStatus['color'] }}/10 text-{{ $itemStatus['color'] }}">{{ $itemStatus['label'] }}</span></td><td>{{ $item->note ?: '-' }}</td></tr>
                    @empty<tr><td colspan="5" class="text-center">Nessun'altra prenotazione per questo cliente.</td></tr>@endforelse
                </tbody></table></div>
            </div>
            @endif
        </div>
    </div>

    @if($showPickTableModal) @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'saveSelectedTables', 'closeAction' => 'closePickTableModal']) @endif
</div></div>
