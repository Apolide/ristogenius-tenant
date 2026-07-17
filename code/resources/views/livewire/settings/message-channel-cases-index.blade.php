<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">
                    {{ __('message_channel_cases.title') }}
                </h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-textmuted" href="javascript:void(0);">
                                {{ __('message_channel_cases.title') }}
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex xl:my-auto right-content align-items-center gap-2">
                <button type="button" wire:click="resetToDefaults" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                    <i class="las text-3xl la-redo-alt"></i>
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full">
                    {{ __('message_channel_cases.save') }}
                </button>
            </div>
        </div>

        <div class="box">
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger !mb-5" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="pb-3">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('message_channel_cases.search') }}" class="form-control">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <th class="border border-defaultborder dark:border-defaultborder/10 text-start">{{ __('message_channel_cases.actions') }}</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">{{ __('message_channel_cases.label') }}</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">{{ __('message_channel_cases.required') }}</th>
                                @foreach ($channels as $channelLabel)
                                    <th class="border-b dark:border-defaultborder/10 text-start">{{ $channelLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($cases as $caseKey => $case)
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <td class="whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-5">
                                            <div class="hs-tooltip ti-main-tooltip">
                                                <button type="button"
                                                        wire:click="resetCase('{{ $caseKey }}')"
                                                        class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                    <i class="las text-3xl la-redo-alt"></i>
                                                    <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                        {{ __('message_channel_cases.reset') }}
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        {{ $case['label'] }}
                                    </td>

                                    <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        @if ($case['required'])
                                            <span class="badge bg-success/10 text-success">{{ __('message_channel_cases.yes') }}</span>
                                        @else
                                            <span class="badge bg-danger/10 text-danger">{{ __('message_channel_cases.no') }}</span>
                                        @endif
                                    </td>

                                    @foreach ($channels as $channelKey => $channelLabel)
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            <label class="inline-flex items-center gap-2">
                                                <input type="checkbox"
                                                       wire:model="message_channel_cases.{{ $caseKey }}.channels"
                                                       value="{{ $channelKey }}"
                                                       class="h-4 w-4">
                                                <span class="sr-only">{{ $channelLabel }}</span>
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <td colspan="{{ count($channels) + 3 }}" class="text-center text-sm text-gray-500">
                                        {{ __('message_channel_cases.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="save" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">{{ __('message_channel_cases.save') }}</button>
                    <a href="{{ route('settings.index') }}" class="ti-btn ti-btn-light mr-3">{{ __('message_channel_cases.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
