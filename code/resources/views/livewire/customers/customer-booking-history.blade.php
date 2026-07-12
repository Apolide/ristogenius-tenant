<div class="mt-8">
    <h4 class="text-base font-semibold mb-2">Storico prenotazioni cliente ({{ $customerBookings->count() }})</h4>
    <div class="flex flex-wrap gap-2 mb-4">
        <span class="badge bg-success/10 text-success">Consumate: {{ $stats['finalized'] }}</span>
        <span class="badge bg-danger/10 text-danger">Cancellate/eliminate: {{ $stats['canceled'] }}</span>
        <span class="badge bg-danger/10 text-danger">Rifiutate: {{ $stats['denied'] }}</span>
        <span class="badge bg-warning/10 text-warning">No show: {{ $stats['no_show'] }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered min-w-full">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Orario</th>
                    <th>Pax</th>
                    <th>Stato</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerBookings as $item)
                    @php($itemStatus = $item->trashed() ? ['label' => 'Eliminata', 'color' => 'danger'] : ($statusLabels[$item->status] ?? ['label' => $item->status, 'color' => 'secondary']))
                    <tr>
                        <td>{{ $item->booking_date->format('d/m/Y') }}</td>
                        <td>{{ substr((string) $item->booking_time, 0, 5) }}</td>
                        <td>{{ $item->pax }}</td>
                        <td><span class="badge bg-{{ $itemStatus['color'] }}/10 text-{{ $itemStatus['color'] }}">{{ $itemStatus['label'] }}</span></td>
                        <td>{{ $item->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Nessuna prenotazione per questo cliente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
