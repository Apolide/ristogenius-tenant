<div>
    @include('livewire.public-bookings.partials.header')
    @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif
    <section class="box"><div class="box-header"><h2 class="box-title">{{ __('public_bookings.title') }}</h2></div><div class="box-body">
        @php($status = $statusLabels[$booking->status] ?? ['color' => 'secondary'])
        <div class="table-responsive"><table class="table table-bordered min-w-full"><tbody>
            <tr><td class="font-bold w-1/3">{{ __('bookings.show.status') }}</td><td><span class="badge bg-{{ $status['color'] }}/10 text-{{ $status['color'] }}">{{ __('bookings.statuses.'.$booking->status) }}</span></td></tr>
            <tr><td class="font-bold">{{ __('bookings.show.datetime') }}</td><td>{{ $booking->booking_date->format('d/m/Y') }} {{ __('bookings.show.at') }} {{ substr((string) $booking->booking_time, 0, 5) }}</td></tr>
            <tr><td class="font-bold">{{ __('bookings.show.people') }}</td><td>{{ $booking->pax }}</td></tr>
            <tr><td class="font-bold">{{ __('bookings.booking_notes') }}</td><td>{{ $booking->note ?: '-' }}</td></tr>
        </tbody></table></div>

        <div class="mt-8"><h3 class="text-base font-semibold mb-3">{{ __('bookings.show.history') }}</h3>
            <div class="table-responsive"><table class="table table-bordered min-w-full"><thead><tr><th>{{ __('bookings.show.date') }}</th><th>{{ __('bookings.show.event') }}</th><th>{{ __('bookings.show.details') }}</th></tr></thead><tbody>
                @forelse($booking->histories as $history)<tr><td>{{ $history->created_at->format('d/m/Y H:i') }}</td><td>{{ $history->event === 'booking_edited_from_customer' ? __('public_bookings.history.customer_edited') : (in_array($history->event, ['booking_canceled', 'booking_canceled_from_customer'], true) ? __('bookings.show.events.booking_canceled') : __("bookings.show.events.{$history->event}")) }}</td><td>{{ $history->description ?: '-' }}</td></tr>
                @empty<tr><td colspan="3" class="text-center">{{ __('bookings.show.no_history') }}</td></tr>@endforelse
            </tbody></table></div>
        </div>
        @unless(in_array($booking->status, ['denied','canceled','finalized','no-show'], true))
            <a href="{{ $editUrl }}" class="ti-btn ti-btn-warning-full mt-6">{{ __('public_bookings.edit') }}</a>
        @endunless
    </div></section>
</div>
