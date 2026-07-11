<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <h5 class="page-title text-[1.3125rem] font-medium">{{ $booking ? 'Modifica prenotazione' : 'Nuova prenotazione' }}</h5>
        <div class="flex gap-2"><a href="{{ route('bookings.index') }}" class="ti-btn ti-btn-light"><i class="las la-arrow-left"></i> Indietro</a>@unless($booking)<a href="{{ route('bookings.walk-in') }}" class="ti-btn ti-btn-info">Walk In</a>@endunless</div>
    </div>
    <div class="box"><div class="box-body">
        @if ($errors->any()) <div class="alert alert-danger mb-5">{{ $errors->first() }}</div> @endif
        <form wire:submit="save">
            <p class="text-textmuted mb-2">Digita almeno 4 caratteri in uno dei campi cliente per cercare un cliente esistente.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="relative"><label class="block text-sm font-medium">Prefisso e telefono</label><div class="flex gap-2">
                    <select wire:model="phone_prefix" class="form-control max-w-[260px]" autocomplete="tel-country-code" required><option value="">Prefisso</option>@foreach($countries as $country)<option value="{{ $country['prefix'] }}">{{ $country['name'] }} {{ $country['flag'] }} {{ $country['prefix'] }}</option>@endforeach</select>
                    <input wire:model.live.debounce.800ms="phone" class="form-control" inputmode="tel" autocomplete="tel-national" maxlength="25" required>
                </div>@include('livewire.bookings.partials.customer-suggestions', ['field' => 'phone'])</div>
                <div class="relative"><label class="block text-sm font-medium">Email</label><input type="email" wire:model.live.debounce.800ms="email" class="form-control">@include('livewire.bookings.partials.customer-suggestions', ['field' => 'email'])</div>
                <div class="relative"><label class="block text-sm font-medium">Nome</label><input wire:model.live.debounce.800ms="firstname" class="form-control" required>@include('livewire.bookings.partials.customer-suggestions', ['field' => 'firstname'])</div>
                <div class="relative"><label class="block text-sm font-medium">Cognome</label><input wire:model.live.debounce.800ms="lastname" class="form-control" required>@include('livewire.bookings.partials.customer-suggestions', ['field' => 'lastname'])</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                <div><label class="block text-sm font-medium">Data</label><input type="date" min="{{ now()->toDateString() }}" wire:model.live="booking_date" class="form-control" required></div>
                <div><label class="block text-sm font-medium">Orario</label><select wire:model="booking_time" class="form-control" required><option value="">Seleziona orario</option>@foreach($slots as $value => $slot)<option value="{{ $value }}">{{ $slot['label'] }}</option>@endforeach</select></div>
                <div><label class="block text-sm font-medium">Numero persone</label><input type="number" min="1" wire:model="pax" class="form-control" required></div>
                <div><label class="block text-sm font-medium">Lingua del cliente</label><select wire:model="lang" class="form-control" required>@foreach($languages as $code => $language)<option value="{{ $code }}">{{ $language['flag'] }} {{ strtoupper($code) }} — {{ $language['label'] }}</option>@endforeach</select></div>
                <div><label class="block text-sm font-medium">Stato</label><select wire:model="status" class="form-control">@foreach($statuses as $value => $status)<option value="{{ $value }}">{{ $status['label'] }}</option>@endforeach</select></div>
            </div>
            <div class="mt-3"><label class="block text-sm font-medium">Note</label><textarea wire:model="note" class="form-control"></textarea></div>
            <div class="mt-4 flex flex-wrap items-center gap-3"><button type="button" wire:click="openTablePicker" class="ti-btn ti-btn-info"><i class="las la-chair"></i> Assegna tavoli</button>@if(count($selectedTablesIds))<span class="badge bg-success/10 text-success">{{ count($selectedTablesIds) }} {{ count($selectedTablesIds) === 1 ? 'tavolo selezionato' : 'tavoli selezionati' }}</span>@else<span class="text-sm text-textmuted">Nessun tavolo selezionato.</span>@endif</div>
            <div class="flex gap-3 mt-5"><button class="ti-btn ti-btn-primary h-12" type="submit">Salva prenotazione</button>@if($booking)<button type="button" wire:click="delete" wire:confirm="Eliminare la prenotazione?" class="ti-btn ti-btn-danger-full h-12">Elimina</button>@endif</div>
        </form>
        @if($tablePickerOpen) @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'confirmTables', 'closeAction' => 'closeTablePicker']) @endif
    </div></div>
</div></div>
