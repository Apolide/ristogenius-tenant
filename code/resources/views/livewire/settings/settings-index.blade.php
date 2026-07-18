<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">{{ __('reservation_settings.index.title') }}</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <span class="flex items-center text-textmuted">{{ __('reservation_settings.index.title') }}</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="box mb-4">
            <div class="box-header border-none">
                <div>
                    <div class="box-title pb-0">{{ __('reservation_settings.index.reservations') }}</div>
                    <p class="text-xs text-gray-500 font-normal">{{ __('reservation_settings.index.reservations_help') }}</p>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <th class="border border-defaultborder dark:border-defaultborder/10 text-start">{{ __('reservation_settings.common.actions') }}</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">{{ __('reservation_settings.common.name') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.opening-hours') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.hours') }}</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.pax-capacity') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.pax') }}</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.max-sitting-time') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.sitting') }}</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.notification-modes') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.notifications') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="box mb-4">
            <div class="box-header border-none">
                <div>
                    <div class="box-title pb-0">{{ __('reservation_settings.index.operations') }}</div>
                    <p class="text-xs text-gray-500 font-normal">{{ __('reservation_settings.index.operations_help') }}</p>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <th class="border border-defaultborder dark:border-defaultborder/10 text-start">{{ __('reservation_settings.common.actions') }}</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">{{ __('reservation_settings.common.name') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.departments') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.departments') }}</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.rooms') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.rooms') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="box mb-4">
            <div class="box-header border-none">
                <div>
                    <div class="box-title pb-0">{{ __('reservation_settings.index.automations') }}</div>
                    <p class="text-xs text-gray-500 font-normal">{{ __('reservation_settings.index.automations_help') }}</p>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <th class="border border-defaultborder dark:border-defaultborder/10 text-start">{{ __('reservation_settings.common.actions') }}</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">{{ __('reservation_settings.common.name') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.booking-remind-hours') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>{{ __('reservation_settings.index.reminder_question') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
