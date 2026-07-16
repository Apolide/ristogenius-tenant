<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Modalita di invio notifiche</h5>
            </div>
        </div>

        <div class="box">
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
                @endif

                <form wire:submit.prevent="save" class="space-y-5">
                    <div>
                        <span class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Modalita di invio notifiche</span>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <label class="flex cursor-not-allowed items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                                <input type="checkbox" checked disabled class="h-4 w-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Email</span>
                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                                <input type="checkbox" wire:model="notification_channels" value="sms" class="h-4 w-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">SMS</span>
                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                                <input type="checkbox" wire:model="notification_channels" value="whatsapp" class="h-4 w-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">WhatsApp</span>
                            </label>


                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                                <input type="checkbox" wire:model="notification_channels" value="telegram" class="h-4 w-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Telegram</span>
                            </label>

                        </div>

                        @error('notification_channels') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                        @error('notification_channels.*') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">Salva</button>
                        <a href="{{ route('settings.index') }}" class="ti-btn ti-btn-light mr-3">Annulla</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
