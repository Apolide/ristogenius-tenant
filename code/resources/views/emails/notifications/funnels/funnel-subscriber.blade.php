@extends('emails.layout-marketing')

@section('title', $email->subject)

@section('content')
    {{-- Oggetto principale --}}
    <h1>{{ $email->subject }}</h1>

    {{-- Corpo del messaggio --}}
    <div style="margin-bottom: 20px;">
        {{-- Sostituisce qualsiasi placeholder @+name@+ con il nome del subscriber --}}
        {!! preg_replace('/@+name@+/', $subscriber->name, $email->body) !!}
    </div>

    {{-- Pulsante di call to action se presente --}}
    @if(! empty($email->url) && ! empty($email->button_label))
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $email->url }}" class="btn-primary">{{ $email->button_label }}</a>
        </p>
    @endif

    {{-- Il footer con link di annullamento iscrizione è gestito dal layout-marketing --}}
@endsection