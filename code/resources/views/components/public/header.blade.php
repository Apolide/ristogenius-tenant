<header class="tenant-public-form-header">
        <div class="flex h-full w-full items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3 overflow-hidden">
                @if ($branding['logo_url'])
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['name'] }}" class="tenant-public-form-logo">
                @else
                    <strong>{{ $branding['name'] }}</strong>
                @endif
            </div>
            @if (count($languageUrls) > 1)
                <div>
                    <label for="{{ $languageSelectId ?? 'form-language' }}" class="sr-only">{{ __('public_bookings.language') }}</label>
                    <select
                        id="{{ $languageSelectId ?? 'form-language' }}"
                        onchange="window.location.href = this.value"
                        class="appearance-none rounded-lg border border-gray-300 bg-white px-5 py-2 pr-8 text-sm focus:border-gray-500 focus:outline-none"
                    >
                        @foreach ($languageUrls as $code => $url)
                            @php($meta = app(\App\Services\CustomerLanguageService::class)->meta($code))
                            <option value="{{ $url }}" @selected($language === $code)>{{ $meta['flag'] }} {{ strtoupper($code) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </header>
