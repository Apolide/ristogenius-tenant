<div class="header-element py-[1rem] px-[0.65rem] notifications-dropdown header-notification hs-dropdown ti-dropdown !hidden md:!flex"
wire:ignore.self wire:poll.15s data-hs-dropdown-placement="bottom-right">
<button id="dropdown-notification" type="button"
  aria-label="{{ __('interface.notification_count', ['count' => $unreadCount]) }}" class="hs-dropdown-toggle relative ti-dropdown-toggle !p-0 !border-0 flex-shrink-0  !rounded-full !shadow-none align-middle text-xs">
  <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" height="24px" viewBox="0 0 24 24"
    width="24px" fill="currentColor">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
      d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z" />
  </svg>
  @if ($unreadCount > 0)
  <span class="flex absolute h-5 w-5 -top-[0.6rem] ltr:right-0 rtl:left-0  ltr:-mr-[0.6rem] rtl:-ml-1">
    <span
      class="animate-slow-ping absolute inline-flex top-[7px] -start-[1.4px] h-[10px] w-[10px] !rounded-full bg-success/50 opacity-75"></span>
    <span class="pulse-success"></span>
  </span>
  @endif
</button>
<div wire:ignore.self
  class="main-header-dropdown !-mt-2 !p-0 hs-dropdown-menu ti-dropdown-menu  bg-white !w-[20rem] !border-0 border-defaultborder hidden !m-0"
  aria-labelledby="dropdown-notification">

  <div class="menu-header-content bg-primary text-white">
    <div class="flex items-center justify-between">
      <h6 class="mb-0 text-[.9375rem] font-semibold text-white">{{ __('interface.notifications') }}</h6>
      <button type="button" class="badge !rounded-full bg-warning !text-[0.7rem] !font-medium text-black cursor-pointer" id="mark-all-read" wire:click="markAllRead">{{ __('interface.mark_all_read') }}</button>
      {{-- <span class="badge !rounded-full bg-warning !text-[0.7rem] !font-medium text-black">Mark All Read</span> --}}
    </div>
    {{-- <p class="dropdown-title-text subtext mb-0 text-white opacity-[0.6] pb-0 text-[0.75rem] ">You have 6
      unread Notifications</p> --}}
  </div>
  <div class="dropdown-divider"></div>
  <ul class="list-none mb-0 overflow-y-auto" style="max-height: 24rem" id="header-notification-scroll">
    @forelse ($notifications as $notification)
      <li class="dropdown-item px-3" wire:key="notification-{{ $notification->id }}">
        <a href="{{ $notification->url }}" wire:click.prevent="open({{ $notification->id }})" class="flex items-center gap-3 w-full py-2">
          <span class="avatar avatar-md avatar-rounded flex-shrink-0 {{ $notification->pivot->read_at ? 'bg-secondary' : 'bg-success' }}">
            <i class="la {{ $notification->icon() }} text-[1.25rem]" aria-hidden="true"></i>
          </span>
          <div class="min-w-0 whitespace-normal">
            <h5 class="notification-label text-defaulttextcolor mb-1 {{ $notification->pivot->read_at ? '' : 'font-semibold' }}">{{ $notification->translatedTitle() }}</h5>
            <p class="text-xs mb-1">{{ $notification->translatedMessage() }}</p>
            <div class="notification-subtext">{{ $notification->created_at->diffForHumans() }}{{ $notification->pivot->read_at ? '' : ' · '.__('interface.unread') }}</div>
          </div>
        </a>
      </li>
    @empty
      <li class="px-3 py-4 text-center text-muted">{{ __('interface.empty') }}</li>
    @endforelse
  </ul>
  <div class="text-center dropdown-footer text-muted text-xs py-2">
    <div>{{ __('interface.notification_footer', ['count' => $unreadCount]) }}</div>
    <a href="{{ route('notifications.index') }}" class="inline-block mt-1 font-semibold text-primary">{{ __('interface.view_all_notifications') }}</a>
  </div>
</div>
</div>
