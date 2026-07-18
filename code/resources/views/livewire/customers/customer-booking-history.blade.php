<div class="mt-8">
    <h4 class="text-base font-semibold mb-2">{{ __('customers.history.title', ['count' => $customerBookings->count()]) }}</h4>
    <div class="flex flex-wrap gap-2 mb-4">
        <span class="badge bg-success/10 text-success">{{ __('customers.history.finalized') }}: {{ $stats['finalized'] }}</span>
        <span class="badge bg-danger/10 text-danger">{{ __('customers.history.canceled') }}: {{ $stats['canceled'] }}</span>
        <span class="badge bg-danger/10 text-danger">{{ __('customers.history.denied') }}: {{ $stats['denied'] }}</span>
        <span class="badge bg-warning/10 text-warning">{{ __('customers.history.no_show') }}: {{ $stats['no_show'] }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered min-w-full">
            <thead>
                <tr>
                    <th>{{ __('customers.history.date') }}</th><th>{{ __('customers.history.time') }}</th><th>{{ __('customers.history.pax') }}</th><th>{{ __('customers.history.status') }}</th><th>{{ __('customers.history.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerBookings as $item)
                    @php($itemStatus = ['label' => __('customers.statuses.'.($item->trashed() ? 'deleted' : $item->status)), 'color' => $item->trashed() ? 'danger' : ($statusLabels[$item->status]['color'] ?? 'secondary')])
                    <tr>
                        <td>{{ $item->booking_date->format('d/m/Y') }}</td>
                        <td>{{ substr((string) $item->booking_time, 0, 5) }}</td>
                        <td>{{ $item->pax }}</td>
                        <td><span class="badge bg-{{ $itemStatus['color'] }}/10 text-{{ $itemStatus['color'] }}">{{ $itemStatus['label'] }}</span></td>
                        <td>{{ $item->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ __('customers.history.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
