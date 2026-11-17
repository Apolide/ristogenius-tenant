<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    <style>@media print{body *{visibility:hidden}.qr-print-area,.qr-print-area *{visibility:visible}.qr-print-area{position:absolute;top:0;left:0;width:100%}.qr-card{break-inside:avoid;border:0!important}.qr-card a,.qr-card button{display:none!important}}.qr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px}.qr-card{padding:16px;border:1px solid #e2e8f0;border-radius:12px;text-align:center;background:white;color:#17243b}.qr-card img{width:100%;max-width:300px;margin:auto}</style>
    <div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('settings.rooms') }}">{{ __('← Sale') }}</a><h1 class="o-title">{{ __('QR per ordini al tavolo') }}</h1><select class="o-input" wire:model.live="roomId" aria-label="{{ __('Sala') }}"><option value="">{{ __('Tutte le sale') }}</option>@foreach($allRooms as $room)<option value="{{ $room->id }}">{{ $room->name }}</option>@endforeach</select><button class="ti-btn ti-btn-primary-full" onclick="window.print()">{{ __('Stampa QR') }}</button></div>
    @include('orders.errors')
    <div class="qr-print-area">
        @foreach($rooms as $room)<section class="mb-5"><h2 class="text-xl font-semibold mb-4">{{ $room->name }}</h2><div class="qr-grid">@foreach($room->tables as $table)
            @php($url=app(\App\Services\PublicOrders\TableQrService::class)->url($table))
            <article class="qr-card" wire:key="qr-{{ $table->id }}-{{ $table->ordering_token }}"><img alt="QR tavolo {{ $table->name }}" src="data:image/svg+xml;base64,{{ base64_encode(app(\App\Services\PublicOrders\TableQrService::class)->svg($table)) }}"><a class="block text-primary break-all text-xs mb-3" href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a><a class="ti-btn ti-btn-light" href="{{ route('settings.rooms.order-qr-download',$table) }}">{{ __('Scarica SVG') }}</a><button class="ti-btn ti-btn-light" x-data @click="navigator.clipboard.writeText(@js($url))">{{ __('Copia link') }}</button><button class="ti-btn ti-btn-danger" wire:click="regenerate('{{ $table->id }}')" wire:confirm="{{ __('Il vecchio QR smetterà di funzionare. Rigenerare?') }}">{{ __('Rigenera') }}</button></article>
        @endforeach</div></section>@endforeach
    </div>
</div></div>
