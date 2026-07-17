<div class="table-responsive"><table class="ti-custom-table w-full"><thead><tr><th>{{ __('bookings.slot') }}</th><th>Pax</th><th>{{ __('bookings.title') }}</th></tr></thead><tbody>
@forelse($timeslotStats as $slot)<tr><td>{{ $slot['time'] }}</td><td>{{ $slot['pax'] }}</td><td>{{ $slot['bookings'] }}</td></tr>@empty<tr><td colspan="3" class="text-center">{{ __('bookings.empty') }}</td></tr>@endforelse
</tbody></table></div>
