<header class="box mb-6"><div class="box-body flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        @if($branding['logo_url'])<img src="{{ $branding['logo_url'] }}" alt="{{ $branding['name'] }}" class="max-h-16 max-w-52">@endif
        <h1 class="text-xl font-semibold">{{ $branding['name'] }}</h1>
    </div>
    <div>
        <label for="booking-language" class="sr-only">{{ __('public_bookings.language') }}</label>
        <select
            id="booking-language"
            onchange="window.location.href = this.value"
            class="appearance-none rounded-lg border border-gray-300 bg-white px-5 py-2 pr-8 text-sm focus:border-gray-500 focus:outline-none"
        >
            @foreach($languages as $code => $meta)
                <option value="{{ $languageUrls[$code] }}" @selected($language === $code)>
                    {{ $meta['flag'] }} {{ strtoupper($code) }}
                </option>
            @endforeach
        </select>
    </div>
</div></header>
