<header class="box mb-6"><div class="box-body flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        @if($branding['logo_url'])<img src="{{ $branding['logo_url'] }}" alt="{{ $branding['name'] }}" class="max-h-16 max-w-52">@endif
        <h1 class="text-xl font-semibold">{{ $branding['name'] }}</h1>
    </div>
    <nav class="flex gap-2" aria-label="{{ __('public_bookings.language') }}">
        @foreach($languages as $code => $meta)
            <a href="{{ $languageUrls[$code] }}" class="ti-btn {{ $language === $code ? 'ti-btn-primary' : 'ti-btn-light' }} ti-btn-sm" hreflang="{{ $code }}">{{ $meta['flag'] }} {{ strtoupper($code) }}</a>
        @endforeach
    </nav>
</div></header>
