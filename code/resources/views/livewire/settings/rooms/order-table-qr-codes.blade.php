<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    <style>@media print{body *{visibility:hidden}.qr-print-area,.qr-print-area *{visibility:visible}.qr-print-area{position:absolute;top:0;left:0;width:100%}.qr-card{break-inside:avoid;border:0!important}.qr-card a,.qr-card button{display:none!important}}.qr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px}.qr-card{padding:16px;border:1px solid #e2e8f0;border-radius:12px;text-align:center;background:white;color:#17243b}.qr-card img{width:100%;max-width:300px;margin:auto}</style>
    @include('orders.workspace-styles')
<x-orders.workspace :title="__('QR per ordini al tavolo')" icon="la-qrcode">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('settings.rooms') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Sale') }}</a><button type="button" class="ti-btn ti-btn-primary-full !m-0" onclick="window.print()"><i class="las la-print" aria-hidden="true"></i> {{ __('Stampa QR') }}</button></x-slot:actions>
    <x-slot:stats>{{ __('QR per ordini al tavolo') }}</x-slot:stats>

    <x-slot:controls><select class="form-control" wire:model.live="roomId" aria-label="{{ __('Sala') }}"><option value="">{{ __('Tutte le sale') }}</option>@foreach($allRooms as $room)<option value="{{ $room->id }}">{{ $room->name }}</option>@endforeach</select></x-slot:controls>
@include('orders.errors')
    <div class="qr-print-area">
        @foreach($rooms as $room)<section class="mb-5"><h2 class="text-xl font-semibold mb-4">{{ $room->name }}</h2><div class="qr-grid">@foreach($room->tables as $table)
            @php($url=app(\App\Services\PublicOrders\TableQrService::class)->url($table))
            <article class="qr-card" wire:key="qr-{{ $table->id }}-{{ $table->ordering_token }}"><img alt="QR tavolo {{ $table->name }}" src="data:image/svg+xml;base64,{{ base64_encode(app(\App\Services\PublicOrders\TableQrService::class)->svg($table)) }}"><a class="block text-primary break-all text-xs mb-3" href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a><a class="ti-btn ti-btn-light" href="{{ route('settings.rooms.order-qr-download',$table) }}"><i class="las la-download" aria-hidden="true"></i> {{ __('Scarica SVG') }}</a><button class="ti-btn ti-btn-light" x-data @click="navigator.clipboard.writeText(@js($url))"><i class="las la-copy" aria-hidden="true"></i> {{ __('Copia link') }}</button><button class="ti-btn ti-btn-danger" wire:click="regenerate('{{ $table->id }}')" wire:confirm="{{ __('Il vecchio QR smetterà di funzionare. Rigenerare?') }}"><i class="las la-sync" aria-hidden="true"></i> {{ __('Rigenera') }}</button></article>
        @endforeach</div></section>@endforeach
    </div>
</x-orders.workspace>
</div></div>
