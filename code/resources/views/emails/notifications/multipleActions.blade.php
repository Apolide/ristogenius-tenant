{{-- Esempio ispirato al layout standard di vendor/laravel/framework/src/Illuminate/Notifications/resources/views/email.blade.php --}}
@component('mail::message')
@if(! empty($greeting))
# {{ $greeting }}
@endif

@foreach ($flow as $item)
    @if($item['type'] === 'line')
        {{ $item['content'] }}

    @elseif($item['type'] === 'action')
        @component('mail::button', ['url' => $item['url']])
            {{ $item['text'] }}
        @endcomponent
    @endif

    {{-- Riga vuota per separare i blocchi in Markdown --}}
    
@endforeach

@if(! empty($salutation))
{{ $salutation }}
@else
{{ __('Saluti,') }}  
{{ config('app.name') }}
@endif
@endcomponent
