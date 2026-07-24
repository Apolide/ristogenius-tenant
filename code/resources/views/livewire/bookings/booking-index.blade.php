<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <div><h5 class="page-title text-[1.3125rem] font-medium">{{ __('bookings.title') }}</h5></div>
        <div class="flex gap-3">
            <a href="{{ route('bookings.waitlist') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Lista d'attesa"><i class="las text-3xl la-hourglass-half"></i></a>
            <a href="{{ route('bookings.walk-in') }}" class="ti-btn ti-btn-success-full text-white ti-btn-icon" title="Nuovo Walk In"><i class="las text-3xl la-walking"></i></a>
            <a href="{{ route('bookings.create') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon" title="Nuova prenotazione"><i class="las text-3xl la-plus"></i></a>
            <button type="button" wire:click="$refresh" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Aggiorna prenotazioni"><i class="las text-3xl la-redo-alt"></i></button>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif
    <div class="box"><div wire:poll.15s class="box-body">
        <div class="grid grid-cols-3 gap-3">
            <div class="flex justify-start"><button wire:click="previousDay" class="ti-btn ti-btn-primary me-[0.375rem]"><i class="las la-arrow-left text-3xl"></i><span class="hidden md:block">{{ __('bookings.back') }}</span></button></div>
            <div class="flex justify-center gap-2">
                <input type="date" wire:model.live="date" id="selectedDate" class="form-control" style="max-width:190px" onchange="this.blur()">
                <button type="button" wire:click="selectToday" class="ti-btn ti-btn-primary" title="{{ __('bookings.today') }}">{{ __('bookings.today') }}</button>
            </div>
            <div class="flex justify-end"><button wire:click="nextDay" class="ti-btn ti-btn-primary me-[0.375rem]"><span class="hidden md:block">{{ __('bookings.next') }}</span><i class="las la-arrow-right text-3xl"></i></button></div>
        </div>

        <div x-data="{ open: $wire.entangle('timeslotAccordionOpen').live }" class="mb-1 mt-5">
            <div class="overflow-hidden bg-white border -mt-px first:rounded-t-sm last:rounded-b-sm dark:bg-bodybg dark:border-white/10">
                <button type="button" class="bg-primary/10 text-primary group py-4 px-5 inline-flex items-center justify-between gap-x-3 w-full text-start transition hover:text-success dark:text-gray-200 dark:hover:text-white/80" @click="open = !open" :aria-expanded="open.toString()">
                    <span class="inline-flex items-center gap-2"><i class="las la-clock text-xl"></i> {{ __('bookings.timeslots') }}</span>
                    <svg x-show="!open" class="w-3 h-3" viewBox="0 0 16 16" fill="none"><path d="M2 5L8.16086 10.6869C8.35239 10.8637 8.64761 10.8637 8.83914 10.6869L15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <svg x-show="open" x-cloak class="w-3 h-3" viewBox="0 0 16 16" fill="none"><path d="M2 11L8.16086 5.31305C8.35239 5.13625 8.64761 5.13625 8.83914 5.31305L15 11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak class="w-full overflow-hidden">
                    <div class="box-header"><div>
                        {{-- <p style="font-size:.8rem">Prenotazioni per fascia oraria ({{ \Carbon\Carbon::parse($date)->format('d/m/Y') }})</p>
                        <p style="font-size:.7rem" class="text-textmuted font-normal mb-0">Utile per gestire il carico di lavoro.</p> --}}
                    </div>
                </div>
                    <div class="box-body"><div class="table-responsive"><table class="ti-custom-table w-full"><thead><tr><th>{{ __('bookings.slot') }}</th><th>{{ __('bookings.booked') }}</th><th>{{ __('bookings.title') }}</th></tr></thead><tbody>
                        @forelse($timeslotStats as $slot)
                            @if($slot['bookings'] !== 0)
                                <tr>
                                    <td>{{ $slot['time'] }}</td><td>{{ $slot['pax'] }}</td>
                                    <td>{{ $slot['bookings'] }}</td>
                                </tr>
                            @endif
                            @empty<tr><td colspan="3" class="text-center">{{ __('bookings.closed') }}</td></tr>
                        @endforelse
                    </tbody></table></div></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-5 gap-2 mb-1 mt-2">
            <input wire:model.live.debounce.300ms="search" class="form-control md:col-span-1 col-span-5 h-12" placeholder="{{ __('bookings.search') }}">
            <a href="{{ route('bookings.rooms', ['date' => $date, 'meal' => $meal]) }}" class="ti-btn ti-btn-primary col-span-1 h-12">Sala</a>
            <button wire:click="toggleShowAllToday" class="ti-btn ti-btn-primary col-span-2 md:col-span-1 h-12">{{ __('bookings.all_day') }}</button>
            <button wire:click="selectMeal('pranzo')" class="ti-btn h-12 {{ $meal === 'pranzo' ? 'ti-btn-primary' : 'ti-btn-light' }}">{{ __('bookings.lunch') }}</button>
            <button wire:click="selectMeal('cena')" class="ti-btn h-12 {{ $meal === 'cena' ? 'ti-btn-primary' : 'ti-btn-light' }}">{{ __('bookings.dinner') }}</button>
        </div>
        <div class="flex flex-wrap gap-0 md:gap-2 mb-1">
            <div class="px-1 md:px-3 py-2"><strong class="text-xs md:text-sm"><i class="las la-users"></i> Pax: {{ $totalPax }}</strong></div>
            <div class="px-1 md:px-3 py-2"><strong class="text-xs md:text-sm"><i class="las la-users"></i> In arrivo: {{ $arrivingPax }}</strong></div>
            <div class="px-1 md:px-3 py-2"><strong class="text-xs md:text-sm"><i class="las la-users"></i> Servendo: {{ $servingPax }}</strong></div>
        </div>
        <div class="table-responsive mt-4"><table class="table table-bordered whitespace-nowrap min-w-full"><thead><tr><th>{{ __('bookings.customer_booking') }}</th><th>{{ __('bookings.tables_stay') }}</th></tr></thead><tbody>
        @forelse($bookings as $booking)
            @php
                $status = $statuses[$booking->status] ?? ['label' => $booking->status, 'color' => 'secondary'];
                $language = app(\App\Services\CustomerLanguageService::class)->meta($booking->language);
                $eventForm = $booking->eventFormSubmission?->form;
                $eventTitle = $eventForm?->title($booking->language) ?: $eventForm?->title();
            @endphp
            <tr><td><p>@if($booking->customer)<a href="{{ route('customers.show', $booking->customer) }}" class="text-primary"><b>{{ $booking->customer->display_name ?: trim($booking->customer->firstname.' '.$booking->customer->lastname) }}</b></a>@else<b><i class="las la-walking"></i> Walk In</b>@endif <small title="{{ $language['label'] }}">{{ $language['flag'] }} {{ strtoupper($booking->language) }}</small></p><p><i class="las la-users"></i> {{ $booking->pax }} <i class="las la-clock"></i> {{ substr($booking->booking_time,0,5) }} @if($eventForm)<span class="badge bg-info/10 text-info" title="{{ $eventTitle }}">{{ __('bookings.show.event') }}</span> @endif<span class="badge bg-{{ $status['color'] }}/10 text-{{ $status['color'] }}">{{ __('bookings.statuses.'.$booking->status) }}</span></p>
                <div class="flex gap-2 mt-2"><a href="{{ route('bookings.show',$booking) }}" class="ti-btn ti-btn-icon bg-info text-white"><i class="las la-cog text-2xl"></i></a>
                @if(in_array($booking->status,['pending','waiting']))<button wire:click="openAction('accept','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-success text-white"><i class="las la-check text-2xl"></i></button><button wire:click="openAction('deny','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-danger text-white"><i class="las la-times text-2xl"></i></button>@endif
                @if($booking->status === 'accepted')<button wire:click="openAction('tables','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-green-500 text-white"><i class="las la-chair text-2xl"></i></button>@if($booking->booking_date->isToday() || $booking->booking_date->isPast())<button wire:click="openAction('seat','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-green-500 text-white"><i class="las la-user-check text-2xl"></i></button><button wire:click="openAction('no-show','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-red-500 text-white"><i class="las la-user-slash text-2xl"></i></button>@endif @endif
                @if($booking->status === 'seated')<button wire:click="openAction('finalize','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-green-500 text-white"><i class="las la-check-double text-2xl"></i></button>@endif
                @unless($booking->isWalkIn())<a href="{{ route('bookings.edit',$booking) }}" class="ti-btn ti-btn-icon bg-orange-500 text-white"><i class="las la-edit text-2xl"></i></a>@endunless @if($booking->note)<button wire:click="openAction('note','{{ $booking->id }}')" class="ti-btn ti-btn-icon bg-info text-white"><i class="las la-comment text-2xl"></i></button>@endif</div></td>
                <td>@foreach($booking->tables as $table)<span class="badge bg-secondary/10 text-black">{{ $table->name }}</span> @endforeach @if($booking->status === 'seated') @php $p = $this->progress($booking, $maxSitting); @endphp<div class="progress mt-3"><div class="progress-bar {{ $p < 60 ? '!bg-success' : ($p < 85 ? '!bg-warning' : '!bg-danger') }}" style="width:{{ $p }}%"></div></div><small>{{ $p }}%</small>@endif</td></tr>
        @empty<tr><td colspan="2" class="text-center py-5">{{ __('bookings.empty') }}</td></tr>@endforelse
        </tbody></table></div>
    </div></div>
    @if($modal === 'tables')
        @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'saveTables', 'closeAction' => 'closeModal'])
    @elseif($modal)<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="bg-white rounded p-5 w-[500px] max-w-[95vw]">
        @if($modal === 'note')<h2 class="text-xl font-bold mb-3">Note prenotazione</h2><p>{{ $bookings->firstWhere('id',$selectedBookingId)?->note }}</p>
        @else<h2 class="text-xl font-bold mb-3">Conferma operazione</h2><p class="mb-4">Confermi il cambio di stato della prenotazione?</p><button wire:click="changeStatus('{{ ['accept'=>'accepted','deny'=>'denied','no-show'=>'no-show','seat'=>'seated','finalize'=>'finalized'][$modal] }}')" class="ti-btn ti-btn-primary">Conferma</button>@endif
        <button wire:click="closeModal" class="ti-btn ti-btn-secondary mt-4">Chiudi</button></div></div>@endif
</div></div>
