@extends('layouts.app')

@section('content')
<div class="content">
    <div class="main-content">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 class="page-title">{{ __('gift_cards.title') }}</h1>
            <a class="ti-btn ti-btn-primary-full" href="{{ route('gift-cards.public') }}" target="_blank" rel="noopener">
                <i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('gift_cards.public_form') }}
            </a>
        </div>
        <div class="box">
            <div class="box-body space-y-4">
                <form method="get" action="{{ route('gift-cards.index') }}" class="flex flex-wrap items-end gap-3">
                    <label class="min-w-0 flex-1 basis-64">
                        <span class="form-label">{{ __('marketing.search') }}</span>
                        <input class="form-control" name="search" value="{{ $search }}" maxlength="150" placeholder="{{ __('gift_cards.search_placeholder') }}">
                    </label>
                    <label>
                        <span class="form-label">{{ __('marketing.status') }}</span>
                        <select name="status" class="form-control">
                            @foreach(['open', 'pending', 'available', 'redeemed', 'all'] as $option)
                                <option value="{{ $option }}" @selected($status === $option)>{{ __("gift_cards.statuses.{$option}") }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="ti-btn ti-btn-primary-full !mb-0" type="submit">{{ __('gift_cards.apply_filters') }}</button>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered min-w-full whitespace-nowrap">
                        <thead>
                            <tr>
                                <th>{{ __('gift_cards.reference') }}</th>
                                <th>{{ __('gift_cards.sender') }}</th>
                                <th>{{ __('gift_cards.recipient') }}</th>
                                <th>{{ __('gift_cards.fields.amount') }}</th>
                                <th>{{ __('marketing.status') }}</th>
                                <th>{{ __('marketing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cards as $card)
                                <tr>
                                    <td>
                                        <span class="font-semibold">{{ $card->reference }}</span>
                                        @if($card->code)<small class="block">{{ $card->code }}</small>@endif
                                    </td>
                                    <td>{{ $card->sender_firstname }} {{ $card->sender_lastname }}</td>
                                    <td>{{ $card->recipient_firstname }} {{ $card->recipient_lastname }}</td>
                                    <td>{{ $card->formattedAmount() }}</td>
                                    <td>@include('gift-cards.status')</td>
                                    <td>
                                        <a class="ti-btn ti-btn-light" href="{{ route('gift-cards.show', $card) }}">
                                            <i class="las la-eye" aria-hidden="true"></i> {{ __('gift_cards.details') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">{{ __('gift_cards.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $cards->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
