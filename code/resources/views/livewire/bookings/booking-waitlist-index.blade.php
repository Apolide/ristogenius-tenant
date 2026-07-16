<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <div><h5 class="page-title text-[1.3125rem] font-medium">Lista d'Attesa</h5></div>
        <div class="flex gap-3">
            <a href="{{ route('bookings.index') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon" title="Prenotazioni"><i class="las text-3xl la-calendar"></i></a>
            <button type="button" wire:click="$refresh" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Aggiorna lista"><i class="las text-3xl la-redo-alt"></i></button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif
    @error('notification')<div class="alert alert-danger mb-5">{{ $message }}</div>@enderror

    <div class="box">
        <div class="box-header border-none"><div class="box-title">Gestione Lista d'Attesa</div></div>
        <div class="box-body">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div class="relative w-full"><label class="block text-sm mb-1">Cerca cliente</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Nome, cognome, email o telefono" class="form-control w-[500px] max-w-full">@include('livewire.bookings.partials.customer-suggestions', ['field' => 'search'])</div>
                <button wire:click="toggleForm" class="ti-btn ti-btn-info">{{ $showForm ? 'Annulla' : 'Aggiungi cliente' }}</button>
            </div>

            @if($showForm)
                <form wire:submit="createWaitlistBooking" class="box border mb-5">
                    <div class="box-header"><h3 class="box-title">Nuovo Cliente in Attesa</h3></div>
                    <div class="box-body !py-3">
                        <div class="grid grid-cols-2 sm:grid-cols-1 gap-x-3 gap-y-2">
                            <div><label class="form-label !mb-1">Nome *</label><input type="text" wire:model="firstname" class="form-control">@error('firstname')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">Cognome</label><input type="text" wire:model="lastname" class="form-control">@error('lastname')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">Email</label><input type="email" wire:model="email" class="form-control">@error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">Telefono</label><input type="text" wire:model="phone" class="form-control" inputmode="tel">@error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">Numero Persone *</label><input type="number" wire:model="pax" class="form-control" min="1">@error('pax')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">Note</label><input type="text" wire:model="note" class="form-control" maxlength="5000">@error('note')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                        </div>
                        @if($customer_id)<p class="mt-2 text-sm text-success"><i class="las la-check-circle"></i> Cliente esistente selezionato</p>@endif
                        <div class="mt-3"><button type="submit" class="ti-btn ti-btn-primary">Aggiungi</button></div>
                    </div>
                </form>
            @endif

            <div wire:poll.15s class="table-responsive">
                <table class="table table-bordered min-w-full bg-white"><thead><tr><th>Azioni</th><th>Cliente</th><th>Persone</th><th>Inserita alle</th><th>Attesa</th><th>Stato</th></tr></thead><tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td><div class="flex gap-2">
                                <button wire:click="notify('{{ $entry->id }}')" wire:confirm="Avvisare il cliente che il tavolo è pronto?" class="ti-btn ti-btn-icon bg-warning text-white" title="Avvisa cliente"><i class="las la-bell text-2xl"></i></button>
                                <button wire:click="seat('{{ $entry->id }}')" wire:confirm="Confermi che il cliente è stato accomodato?" class="ti-btn ti-btn-icon bg-success text-white" title="Accomoda"><i class="las la-chair text-2xl"></i></button>
                                <button wire:click="cancel('{{ $entry->id }}')" wire:confirm="Rimuovere il cliente dalla lista?" class="ti-btn ti-btn-icon bg-danger text-white" title="Rimuovi"><i class="las la-times text-2xl"></i></button>
                            </div></td>
                            <td><b>{{ $entry->customer->display_name }}</b><br><small>{{ $entry->customer->phone ?: $entry->customer->email }}</small></td>
                            <td>{{ $entry->pax }}</td>
                            <td>{{ $entry->created_at->format('H:i') }}</td>
                            <td>{{ $entry->created_at->diffForHumans(null, true) }}</td>
                            <td>@if($entry->status === 'notified')<span class="badge bg-info/10 text-info">Avvisato {{ $entry->notified_at?->format('H:i') }}</span>@else<span class="badge bg-warning/10 text-warning">In attesa</span>@endif</td>
                        </tr>
                    @empty<tr><td colspan="6" class="text-center text-gray-500 py-5">Nessuno in lista d'attesa per oggi.</td></tr>@endforelse
                </tbody></table>
            </div>
            <div class="mt-3">{{ $entries->links() }}</div>
        </div>
    </div>
</div></div>
