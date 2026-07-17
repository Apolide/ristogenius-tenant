<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <div><h5 class="page-title text-[1.3125rem] font-medium">{{ __('bookings.waitlist_page.title') }}</h5></div>
        <div class="flex gap-3">
            <a href="{{ route('bookings.index') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon" title="Prenotazioni"><i class="las text-3xl la-calendar"></i></a>
            <button type="button" wire:click="$refresh" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Aggiorna lista"><i class="las text-3xl la-redo-alt"></i></button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif
    @error('notification')<div class="alert alert-danger mb-5">{{ $message }}</div>@enderror

    <div class="box">
        <div class="box-header border-none"><div class="box-title">{{ __('bookings.waitlist_page.manage') }}</div></div>
        <div class="box-body">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div class="relative w-full"><label class="block text-sm mb-1">{{ __('bookings.waitlist_page.search') }}</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="{{ __('bookings.waitlist_page.search_placeholder') }}" class="form-control w-[500px] max-w-full">@include('livewire.bookings.partials.customer-suggestions', ['field' => 'search'])</div>
                <button wire:click="toggleForm" class="ti-btn ti-btn-info">{{ $showForm ? __('customers.cancel') : __('bookings.waitlist_page.add_customer') }}</button>
            </div>

            @if($showForm)
                <form wire:submit="createWaitlistBooking" class="box border mb-5">
                    <div class="box-header"><h3 class="box-title">{{ __('bookings.waitlist_page.new_customer') }}</h3></div>
                    <div class="box-body !py-3">
                        <div class="grid grid-cols-2 sm:grid-cols-1 gap-x-3 gap-y-2">
                            <div><label class="form-label !mb-1">{{ __('bookings.form.firstname') }} *</label><input type="text" wire:model="firstname" class="form-control">@error('firstname')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">{{ __('bookings.form.lastname') }}</label><input type="text" wire:model="lastname" class="form-control">@error('lastname')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">Email</label><input type="email" wire:model="email" class="form-control">@error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">{{ __('bookings.form.phone') }}</label><input type="text" wire:model="phone" class="form-control" inputmode="tel">@error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">{{ __('bookings.form.people') }} *</label><input type="number" wire:model="pax" class="form-control" min="1">@error('pax')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label !mb-1">{{ __('bookings.form.notes') }}</label><input type="text" wire:model="note" class="form-control" maxlength="5000">@error('note')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
                        </div>
                        @if($customer_id)<p class="mt-2 text-sm text-success"><i class="las la-check-circle"></i> {{ __('bookings.waitlist_page.existing') }}</p>@endif
                        <div class="mt-3"><button type="submit" class="ti-btn ti-btn-primary">{{ __('bookings.waitlist_page.add') }}</button></div>
                    </div>
                </form>
            @endif

            <div wire:poll.15s class="table-responsive rounded-md bg-defaultbackground">
                <table class="table table-bordered min-w-full bg-defaultbackground"><thead><tr><th>{{ __('bookings.waitlist_page.actions') }}</th><th>{{ __('bookings.waitlist_page.customer') }}</th><th>{{ __('bookings.waitlist_page.people') }}</th><th>{{ __('bookings.waitlist_page.added_at') }}</th><th>{{ __('bookings.waitlist_page.wait') }}</th><th>{{ __('bookings.waitlist_page.status') }}</th></tr></thead><tbody>
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
                            <td>@if($entry->status === 'notified')<span class="badge bg-info/10 text-info">{{ __('bookings.waitlist_page.notified') }} {{ $entry->notified_at?->format('H:i') }}</span>@else<span class="badge bg-warning/10 text-warning">{{ __('bookings.waitlist_page.waiting') }}</span>@endif</td>
                        </tr>
                    @empty<tr><td colspan="6" class="text-center text-textmuted py-5">{{ __('bookings.waitlist_page.empty') }}</td></tr>@endforelse
                </tbody></table>
            </div>
            <div class="mt-3">{{ $entries->links() }}</div>
        </div>
    </div>
</div></div>
