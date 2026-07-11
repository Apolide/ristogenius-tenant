<div class="table-responsive"><table class="ti-custom-table w-full"><thead><tr><th>Fascia oraria</th><th>Pax</th><th>Prenotazioni</th></tr></thead><tbody>
@forelse($timeslotStats as $slot)<tr><td>{{ $slot['time'] }}</td><td>{{ $slot['pax'] }}</td><td>{{ $slot['bookings'] }}</td></tr>@empty<tr><td colspan="3" class="text-center">Nessuna prenotazione per questa data.</td></tr>@endforelse
</tbody></table></div>
