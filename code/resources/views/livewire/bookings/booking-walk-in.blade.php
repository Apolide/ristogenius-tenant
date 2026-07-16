<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb"><h5 class="page-title text-[1.3125rem] font-medium">Nuovo Walk In</h5><a href="{{ route('bookings.index') }}" class="ti-btn ti-btn-light"><i class="las la-arrow-left"></i> Indietro</a></div>
    <div class="box"><div class="box-header"><div><div class="box-title">CREA NUOVO WALK IN</div><p class="text-textmuted">Occupa lo slot senza registrare dati personali del cliente.</p></div></div><div class="box-body">
        @if($errors->any())<div class="alert alert-danger mb-5">{{ $errors->first() }}</div>@endif
        <form wire:submit="save"><div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div><label class="block text-sm font-medium">Data</label><input type="date" min="{{ now()->toDateString() }}" wire:model.live="booking_date" class="form-control" required></div>
            <div><label class="block text-sm font-medium">Orario</label><select wire:model.live="booking_time" class="form-control" required><option value="">Seleziona orario</option>@foreach($slots as $value => $slot)<option value="{{ $value }}">{{ $slot['label'] }}</option>@endforeach</select>@error('booking_time')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium">Numero persone</label><input type="number" min="1" wire:model="pax" class="form-control" required></div>
            <div><label class="block text-sm font-medium">Lingua del cliente</label><select wire:model="lang" class="form-control" required>@foreach($languages as $code => $language)<option value="{{ $code }}">{{ $language['flag'] }} {{ strtoupper($code) }} — {{ $language['label'] }}</option>@endforeach</select></div>
        </div><div class="mt-3"><label class="block text-sm font-medium">Assegnazione tavoli</label><div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="openTablePicker" class="ti-btn ti-btn-primary"><i class="las la-chair"></i> Seleziona tavoli</button>
            @if($booking_time === '')<small class="text-textmuted">Seleziona prima un orario.</small>@elseif(count($selectedTablesIds))<span class="badge bg-success/10 text-success">{{ count($selectedTablesIds) }} {{ count($selectedTablesIds) === 1 ? 'tavolo selezionato' : 'tavoli selezionati' }}</span>@else<small class="text-textmuted">Nessun tavolo selezionato.</small>@endif
        </div></div>
        <div class="mt-3"><label class="block text-sm font-medium">Note</label><textarea wire:model="note" class="form-control"></textarea></div><button type="submit" class="ti-btn ti-btn-primary mt-5">Salva Walk In</button></form>
    </div></div>
    <div class="box"><div class="box-header"><div class="box-title">Prenotazioni per fascia oraria ({{ \Carbon\Carbon::parse($booking_date)->format('d/m/Y') }})</div></div><div class="box-body">@include('livewire.bookings.partials.timeslot-stats')</div></div>
    @if($tablePickerOpen) @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'confirmTables', 'closeAction' => 'closeTablePicker']) @endif
</div></div>
