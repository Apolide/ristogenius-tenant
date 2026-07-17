<div>
    <div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">{{ __('personnel.title') }}</h5>
                <nav><ol class="flex items-center whitespace-nowrap min-w-0"><li class="text-[12px] text-primary">{{ __('personnel.title') }}</li></ol></nav>
            </div>
            <div class="flex xl:my-auto right-content align-items-center gap-3">
                <a href="{{ route('personnel.create') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon" title="{{ __('personnel.add') }}"><i class="las text-3xl la-plus"></i></a>
                <a href="{{ route('personnel.permissions') }}" class="ti-btn ti-btn-primary-full text-white ti-btn-icon" title="{{ __('personnel.permissions.title') }}"><i class="las text-3xl la-shield-alt"></i></a>
                <a href="{{ route('personnel.index') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="{{ __('personnel.refresh') }}"><i class="las text-3xl la-redo-alt"></i></a>
            </div>
        </div>
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="box">
        <div class="box-body">
            <div class="pb-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.index.search') }}" aria-label="{{ __('personnel.index.search') }}" class="form-control" id="search">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered whitespace-nowrap min-w-full">
                    <thead>
                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                            <th class="text-start">{{ __('personnel.index.actions') }}</th>
                            <th class="text-start">{{ __('personnel.index.name') }}</th>
                            <th class="text-start">{{ __('personnel.index.status') }}</th>
                            <th class="text-start">{{ __('personnel.index.role') }}</th>
                            <th class="text-start">{{ __('personnel.index.whatsapp_notifications') }}</th>
                            <th class="text-start">{{ __('personnel.index.telegram_notifications') }}</th>
                            <th class="text-start">{{ __('personnel.language') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        <a href="{{ route('personnel.edit', $user) }}"
                                            class="ti-btn ti-btn-icon bg-info text-white" title="{{ __('personnel.edit') }}">
                                            <i class="las text-2xl la-pen"></i>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <button type="button" wire:click="deletePersonnel({{ $user->id }})"
                                                wire:confirm="{{ __('personnel.index.delete_confirmation') }}"
                                                class="ti-btn ti-btn-icon bg-danger text-white" title="{{ __('personnel.index.delete') }}">
                                                <i class="las text-2xl la-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-sm">
                                    <strong>{{ $user->name }}</strong><br>
                                    @if ($user->phone)<a class="text-primary" href="tel:{{ $user->phone }}">{{ $user->phone }}</a><br>@endif
                                    <a class="text-primary" href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                </td>
                                <td class="whitespace-nowrap text-sm">
                                    @if ($user->activated_at || (! $user->invited_at && $user->enabled))
                                        <span class="badge bg-primary/10 !text-primary">{{ __('personnel.index.active') }}</span>
                                    @else
                                        <span class="badge bg-warning/10 !text-warning">{{ __('personnel.index.invited') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-sm">
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-secondary/10 !text-secondary">{{ __("personnel.roles.{$role->name}") }}</span>
                                    @endforeach
                                </td>
                                <td class="whitespace-nowrap text-sm">
                                    <span class="badge {{ $user->receive_whatsapp_notifications ? 'bg-success/10 !text-success' : 'bg-danger/10 !text-danger' }}">
                                        {{ $user->receive_whatsapp_notifications ? __('personnel.yes') : __('personnel.no') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap text-sm">
                                    <span class="badge {{ $user->receive_telegram_notifications ? 'bg-success/10 !text-success' : 'bg-danger/10 !text-danger' }}">
                                        {{ $user->receive_telegram_notifications ? __('personnel.yes') : __('personnel.no') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap text-sm">{{ strtoupper($user->lang ?: 'it') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-6 text-gray-500">{{ __('personnel.index.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
    </div>
    </div>
</div>
