@php
    $primary = data_get($menu->theme, 'primary', '#0f766e');
    $accent = data_get($menu->theme, 'accent', '#d97706');
    $background = data_get($menu->theme, 'background', '#fffaf0');
    $labels = [
        'it' => ['digital' => 'Il nostro menu', 'allergens' => 'Allergeni', 'upsell' => 'Rendilo speciale', 'upsell_text' => 'Scopri una proposta ancora più speciale', 'cross' => 'Perfetto insieme', 'service' => 'Servizio'],
        'en' => ['digital' => 'Our menu', 'allergens' => 'Allergens', 'upsell' => 'Make it special', 'upsell_text' => 'Discover an even more special option', 'cross' => 'Perfect together', 'service' => 'Service'],
        'de' => ['digital' => 'Unsere Speisekarte', 'allergens' => 'Allergene', 'upsell' => 'Mach es besonders', 'upsell_text' => 'Entdecke eine noch besondere Empfehlung', 'cross' => 'Passt perfekt dazu', 'service' => 'Service'],
    ][$language] ?? null;
@endphp

<div class="digital-menu min-h-screen pb-16" style="--dm-primary: {{ $primary }}; --dm-accent: {{ $accent }}; --dm-bg: {{ $background }}; background: var(--dm-bg); color: #24332f">
    <style>
        .digital-menu { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .digital-menu .dm-display { font-family: Georgia, Cambria, "Times New Roman", serif; }
        .digital-menu .dm-hero { background: linear-gradient(125deg, color-mix(in srgb, var(--dm-primary) 88%, #10231e), color-mix(in srgb, var(--dm-primary) 62%, #091714)); }
        .digital-menu .dm-hero-overlay { background: linear-gradient(90deg, rgb(8 24 20 / .86), rgb(8 24 20 / .48) 62%, rgb(8 24 20 / .26)); }
        .digital-menu .dm-nav { background: var(--dm-bg); box-shadow: 0 8px 24px rgb(27 51 43 / .10); }
        .digital-menu .dm-nav-link { background: #fff; border-color: color-mix(in srgb, var(--dm-primary) 22%, #ddd); color: #344943; }
        .digital-menu .dm-nav-link:hover, .digital-menu .dm-nav-link:focus { background: var(--dm-primary); border-color: var(--dm-primary); color: #fff; }
        .digital-menu .dm-category-title { color: color-mix(in srgb, var(--dm-primary) 82%, #101d19); }
        .digital-menu .dm-product-card { background: rgb(255 255 255 / .96); border: 1px solid color-mix(in srgb, var(--dm-primary) 12%, #e5e1d8); box-shadow: 0 10px 30px rgb(38 55 49 / .07); }
        .digital-menu .dm-product-card:hover { box-shadow: 0 14px 38px rgb(38 55 49 / .13); transform: translateY(-2px); }
        .digital-menu .dm-price { background: color-mix(in srgb, var(--dm-accent) 12%, #fff); border-color: color-mix(in srgb, var(--dm-accent) 28%, #fff); color: color-mix(in srgb, var(--dm-accent) 82%, #582800); }
        .digital-menu .dm-upsell { background: linear-gradient(135deg, color-mix(in srgb, var(--dm-accent) 15%, #fff), color-mix(in srgb, var(--dm-accent) 5%, #fff)); border-color: color-mix(in srgb, var(--dm-accent) 34%, #fff); }
        .digital-menu .dm-upsell-title { color: color-mix(in srgb, var(--dm-accent) 80%, #4b2500); }
        .digital-menu .dm-cross-card { background: color-mix(in srgb, var(--dm-primary) 7%, #fff); border-color: color-mix(in srgb, var(--dm-primary) 22%, #fff); }
        .digital-menu .dm-cross-title { color: var(--dm-primary); }
        .digital-menu .dm-muted { color: #66756f; }
        @supports not (color: color-mix(in srgb, #000 50%, #fff)) {
            .digital-menu .dm-category-title, .digital-menu .dm-cross-title { color: var(--dm-primary); }
            .digital-menu .dm-price, .digital-menu .dm-upsell-title { color: var(--dm-accent); }
        }
    </style>

    <header class="dm-hero relative min-h-[21rem] overflow-hidden text-white">
        @if($menu->image_path)
            <img src="{{ Storage::url($menu->image_path) }}" alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            <div class="dm-hero-overlay absolute inset-0"></div>
        @else
            <div class="absolute inset-0 bg-black/10"></div>
        @endif

        <div class="relative mx-auto flex min-h-[21rem] max-w-5xl flex-col px-5 pb-10 sm:px-7">
            <div class="flex h-20 w-full items-center justify-between gap-3">
                <div class="flex min-w-0 items-center overflow-hidden">
                    @if($branding['logo_url'])
                        <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['name'] }}" class="tenant-public-form-logo" style="display:block; width:auto !important; height:auto !important; max-width:11rem !important; max-height:3rem !important; object-fit:contain">
                    @endif
                </div>
                @if(count($languages) > 1)
                    <div>
                        <label for="menu-language" class="sr-only">{{ __('public_bookings.language') }}</label>
                        <select id="menu-language" onchange="window.location.href = this.value" class="appearance-none rounded-lg border border-white/40 bg-white px-5 py-2 pr-8 text-sm font-medium text-gray-800 shadow-sm focus:border-white focus:outline-none">
                            @foreach($languages as $code => $meta)
                                <option value="{{ route('menu.public', ['language' => $code, 'menu' => $menu]) }}" @selected($language === $code)>{{ $meta['flag'] }} {{ strtoupper($code) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-auto max-w-2xl">
                <p class="mb-3 text-xs font-bold uppercase tracking-[.28em] text-white/75">{{ $labels['digital'] }}</p>
                <h1 class="dm-display text-4xl font-semibold leading-tight text-white sm:text-6xl">{{ $menu->translatedName($language) }}</h1>
                @if($menu->translatedDescription($language))
                    <p class="mt-4 max-w-xl text-base leading-7 text-white/85 sm:text-lg">{{ $menu->translatedDescription($language) }}</p>
                @endif
            </div>
        </div>
    </header>

    <nav class="dm-nav sticky top-0 z-30 border-b" style="border-color: color-mix(in srgb, var(--dm-primary) 16%, #ddd)" aria-label="Categorie menu">
        <div class="mx-auto flex max-w-5xl gap-2 overflow-x-auto px-5 py-3 sm:px-7">
            @foreach($menu->categories as $category)
                <a href="#category-{{ $category->id }}" class="dm-nav-link whitespace-nowrap rounded-full border px-4 py-2 text-sm font-semibold transition">{{ $category->translatedName($language) }}</a>
            @endforeach
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-5 sm:px-7">
        @foreach($menu->categories as $category)
            <section id="category-{{ $category->id }}" class="scroll-mt-20 pt-12 sm:pt-16">
                <div class="mb-6 overflow-hidden rounded-2xl @if($category->image_path) relative min-h-48 @endif">
                    @if($category->image_path)
                        <img src="{{ Storage::url($category->image_path) }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/40 to-transparent"></div>
                        <div class="relative flex min-h-48 max-w-2xl flex-col justify-end p-6 text-white sm:p-8">
                            <h2 class="dm-display text-3xl font-semibold text-white sm:text-4xl">{{ $category->translatedName($language) }}</h2>
                            @if(data_get($category->description, $language))<p class="mt-2 leading-6 text-white/85">{{ data_get($category->description, $language) }}</p>@endif
                        </div>
                    @else
                        <div class="flex items-center gap-4"><span class="h-px w-10" style="background: var(--dm-accent)"></span><h2 class="dm-category-title dm-display text-3xl font-semibold sm:text-4xl">{{ $category->translatedName($language) }}</h2></div>
                        @if(data_get($category->description, $language))<p class="dm-muted mt-2 max-w-2xl leading-7">{{ data_get($category->description, $language) }}</p>@endif
                    @endif
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($category->products as $product)
                        <article class="dm-product-card flex min-w-0 flex-col overflow-hidden rounded-2xl transition duration-200">
                            @if($product->image_path)
                                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->translatedName($language) }}" class="h-52 w-full object-cover" loading="lazy">
                            @endif
                            <div class="flex flex-1 flex-col p-5">
                                @php
                                    $badgeData = is_array($product->pivot->badge) ? $product->pivot->badge : json_decode($product->pivot->badge ?? '[]', true);
                                    $badge = data_get($badgeData, $language) ?? data_get($badgeData, 'it');
                                @endphp
                                @if($badge)<span class="mb-3 w-fit rounded-full px-3 py-1 text-[.68rem] font-bold uppercase tracking-wider text-white" style="background: var(--dm-accent)">{{ $badge }}</span>@endif
                                <div class="flex items-start justify-between gap-4">
                                    <h3 class="dm-display min-w-0 text-xl font-semibold leading-snug text-[#21352f]">{{ $product->translatedName($language) }}</h3>
                                    <strong class="dm-price shrink-0 rounded-full border px-3 py-1.5 text-sm">{{ Number::currency($product->pivot->menu_price ?? $product->price, 'EUR', $language) }}</strong>
                                </div>
                                @if($product->translatedDescription($language))<p class="dm-muted mt-3 text-sm leading-6">{{ $product->translatedDescription($language) }}</p>@endif
                                @if($product->allergens)<p class="mt-3 text-xs text-[#8a5b39]"><strong>{{ $labels['allergens'] }}:</strong> {{ implode(', ', $product->allergens) }}</p>@endif

                                @php($upsells = $product->recommendations->where('type', 'upsell'))
                                @if($upsells->isNotEmpty())
                                    <div class="dm-upsell mt-5 rounded-xl border p-4">
                                        <div class="flex items-start gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white text-lg shadow-sm">✨</span><div><h4 class="dm-upsell-title text-sm font-bold">{{ $labels['upsell'] }}</h4><p class="mt-0.5 text-xs text-[#796450]">{{ $labels['upsell_text'] }}</p></div></div>
                                        <div class="mt-3 space-y-2">@foreach($upsells as $recommendation)<div class="flex items-center gap-3 rounded-lg bg-white p-2.5 shadow-sm">@if($recommendation->recommendedProduct->image_path)<img src="{{ Storage::url($recommendation->recommendedProduct->image_path) }}" alt="" class="h-10 w-10 rounded-md object-cover" loading="lazy">@endif<span class="min-w-0 flex-1 text-sm font-semibold">{{ $recommendation->recommendedProduct->translatedName($language) }}</span><strong class="text-sm" style="color: var(--dm-accent)">{{ Number::currency($recommendation->recommendedProduct->price, 'EUR', $language) }}</strong></div>@endforeach</div>
                                    </div>
                                @endif

                                @php($crossSells = $product->recommendations->where('type', 'cross_sell'))
                                @if($crossSells->isNotEmpty())
                                    <div class="mt-5"><h4 class="dm-cross-title mb-2 text-xs font-bold uppercase tracking-wider">{{ $labels['cross'] }}</h4><div class="flex gap-2 overflow-x-auto pb-1">@foreach($crossSells as $recommendation)<div class="dm-cross-card flex min-w-[12rem] items-center gap-2 rounded-xl border p-2.5">@if($recommendation->recommendedProduct->image_path)<img src="{{ Storage::url($recommendation->recommendedProduct->image_path) }}" alt="" class="h-10 w-10 rounded-lg object-cover" loading="lazy">@endif<div class="min-w-0"><span class="block truncate text-xs font-semibold">{{ $recommendation->recommendedProduct->translatedName($language) }}</span><strong class="dm-cross-title text-xs">+ {{ Number::currency($recommendation->recommendedProduct->price, 'EUR', $language) }}</strong></div></div>@endforeach</div></div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if($menu->service_charge > 0)
            <div class="mt-12 flex items-center justify-between rounded-xl border bg-white p-4 text-sm shadow-sm" style="border-color: color-mix(in srgb, var(--dm-primary) 16%, #ddd)"><span class="dm-muted">{{ $labels['service'] }}</span><strong style="color: var(--dm-primary)">{{ Number::currency($menu->service_charge, 'EUR', $language) }}</strong></div>
        @endif
    </main>

    <footer class="dm-muted py-12 text-center text-xs">Powered by RistoPilot</footer>
</div>
