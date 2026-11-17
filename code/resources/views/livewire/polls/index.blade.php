<div class="content">
    <div class="main-content">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h5 class="page-title">{{ __('polls.title') }}</h5>
            <a class="ti-btn ti-btn-primary-full" href="{{ route('polls.create') }}">{{ __('polls.create') }}</a>
        </div>
        <div class="box">
            <div class="box-body">
                <input wire:model.live.debounce.300ms="search" class="form-control mb-4"
                       aria-label="{{ __('polls.search') }}" placeholder="{{ __('polls.search') }}">
                <div class="table-responsive">
                    <table class="table table-bordered min-w-full">
                        <thead>
                            <tr><th>{{ __('polls.label') }}</th><th>{{ __('polls.status') }}</th><th>{{ __('polls.responses') }}</th><th>{{ __('polls.actions') }}</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($polls as $poll)
                                <tr>
                                    <td>{{ $poll->label }}</td>
                                    <td><span class="badge {{ $poll->enabled_at ? 'bg-success/10 text-success' : 'bg-light text-defaulttextcolor' }}">
                                        {{ __($poll->enabled_at ? 'polls.active' : 'polls.inactive') }}
                                    </span></td>
                                    <td><a class="text-primary underline" href="{{ route('polls.responses', $poll) }}">{{ __('polls.response_count', ['count' => $poll->responses_count]) }}</a></td>
                                    <td><a class="ti-btn ti-btn-light ti-btn-sm" href="{{ route('polls.edit', $poll) }}">{{ __('polls.edit') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ __('polls.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $polls->links() }}
            </div>
        </div>
    </div>
</div>
