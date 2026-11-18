<x-layouts.app>
    <div class="content">
        <div class="main-content">
            <div class="page-header-breadcrumb mb-6">
                <h1 class="page-title text-[1.3125rem] font-medium">{{ __('notifications.title') }}</h1>
            </div>

            <div class="box">
                <div class="box-body">
                    <form method="GET" action="{{ route('notifications.index') }}" class="mb-5 space-y-4"
                          x-data="{ period: @js(old('period', $filters['period'] ?? 'all')) }">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label for="notification-search" class="form-label">{{ __('notifications.search') }}</label>
                                <input id="notification-search" type="search" name="q" class="form-control"
                                       value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('notifications.search_placeholder') }}" maxlength="200">
                            </div>
                            <div>
                                <label for="notification-type" class="form-label">{{ __('notifications.type') }}</label>
                                <select id="notification-type" name="type" class="form-control">
                                    <option value="">{{ __('notifications.all_types') }}</option>
                                    @foreach ($typeLabels as $type => $label)
                                        <option value="{{ $type }}" @selected(($filters['type'] ?? null) === $type)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="notification-period" class="form-label">{{ __('notifications.period') }}</label>
                                <select id="notification-period" name="period" class="form-control" x-model="period">
                                    @foreach ($periods as $period)
                                        <option value="{{ $period }}" @selected(old('period', $filters['period'] ?? 'all') === $period)>{{ __('notifications.periods.'.$period) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="notification-status" class="form-label">{{ __('notifications.status') }}</label>
                                <select id="notification-status" name="status" class="form-control">
                                    @foreach ($states as $state)
                                        <option value="{{ $state }}" @selected(($filters['status'] ?? 'all') === $state)>{{ __('notifications.states.'.$state) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4" x-show="period === 'custom'">
                            <div>
                                <label for="notification-from" class="form-label">{{ __('notifications.from') }}</label>
                                <input id="notification-from" name="from" type="date" class="form-control"
                                       value="{{ old('from', $filters['from'] ?? '') }}" :disabled="period !== 'custom'">
                            </div>
                            <div>
                                <label for="notification-to" class="form-label">{{ __('notifications.to') }}</label>
                                <input id="notification-to" name="to" type="date" class="form-control"
                                       value="{{ old('to', $filters['to'] ?? '') }}" :disabled="period !== 'custom'">
                            </div>
                        </div>

                        @if ($errors->any())
                            <p class="alert alert-danger" role="alert">{{ __('notifications.invalid_filters') }}</p>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="ti-btn ti-btn-primary-full !mb-0">{{ __('notifications.apply') }}</button>
                            <a href="{{ route('notifications.index') }}" class="ti-btn ti-btn-light !mb-0">{{ __('notifications.reset') }}</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered min-w-full">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('notifications.notification') }}</th>
                                    <th scope="col">{{ __('notifications.type') }}</th>
                                    <th scope="col">{{ __('notifications.received_at') }}</th>
                                    <th scope="col">{{ __('notifications.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $notification)
                                    <tr class="relative hover:bg-light">
                                        <td>
                                            <div class="flex items-start gap-3">
                                                <span class="avatar avatar-md avatar-rounded shrink-0 {{ $notification->pivot->read_at ? 'bg-secondary' : 'bg-primary/10 text-primary' }}">
                                                    <i class="la {{ $notification->icon() }} text-xl" aria-hidden="true"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <a href="{{ route('notifications.show', $notification->id) }}"
                                                       class="text-primary after:absolute after:inset-0 {{ $notification->pivot->read_at ? 'font-medium' : 'font-semibold' }}">{{ $notification->translatedTitle() }}</a>
                                                    <p class="text-sm text-textmuted whitespace-normal break-words">{{ $notification->translatedMessage() }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $typeLabels[$notification->type] ?? \App\Models\Notification::typeLabel($notification->type) }}</td>
                                        <td class="whitespace-nowrap">
                                            <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->locale(app(\App\Services\CustomerLanguageService::class)->htmlLocale())->isoFormat('L LT') }}</time>
                                        </td>
                                        <td>
                                            <span class="badge {{ $notification->pivot->read_at ? 'bg-light !text-textmuted' : 'bg-primary/10 !text-primary' }}">
                                                {{ __($notification->pivot->read_at ? 'notifications.read' : 'notifications.states.unread') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-textmuted py-10">{{ __('notifications.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($notifications->hasPages())
                        <div class="mt-4">{{ $notifications->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
