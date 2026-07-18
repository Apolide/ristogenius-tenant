<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">{{ __('personnel.permissions.title') }}</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]"><a class="text-primary" href="{{ route('personnel.index') }}">{{ __('personnel.title') }}</a></li>
                    </ol>
                </nav>
            </div>
            <button wire:click="$refresh" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="{{ __('personnel.refresh') }}">
                <i class="las text-3xl la-redo-alt"></i>
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        <div class="box">
            <div class="box-body">
                <div class="pb-3">
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.permissions.search') }}" aria-label="{{ __('personnel.permissions.search') }}" class="form-control" id="search">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr>
                                <th class="text-start sticky left-0 z-10">{{ __('personnel.permissions.employee') }}</th>
                                @foreach ($features as $label)
                                    <th class="text-xs text-center">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr wire:key="permissions-user-{{ $user->id }}">
                                    <td class="sticky left-0 z-10">
                                        <strong>{{ $user->name }}</strong><br>
                                        <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                    </td>
                                    @foreach ($features as $permission => $label)
                                        <td class="text-center">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                wire:model="assigned.{{ $user->id }}.{{ $permission }}"
                                                wire:change="toggle({{ $user->id }}, '{{ $permission }}')"
                                                aria-label="{{ __('personnel.permissions.aria', ['permission' => $label, 'name' => $user->name]) }}"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($features) + 1 }}" class="text-center py-6 text-gray-500">{{ __('personnel.permissions.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
