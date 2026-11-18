@component('layouts.customer-booking', ['title' => $title, 'fullWidth' => true])
<x-public.page :language="$language ?? app()->getLocale()" :language-urls="$languageUrls ?? []">
    {{ $slot }}
</x-public.page>
@endcomponent
