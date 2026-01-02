<div class="header-element py-[1rem] px-[0.65rem] notifications-dropdown header-notification hs-dropdown ti-dropdown !hidden md:!flex"
data-hs-dropdown-placement="bottom-right">
<button id="dropdown-notification" type="button"
  class="hs-dropdown-toggle relative ti-dropdown-toggle !p-0 !border-0 flex-shrink-0  !rounded-full !shadow-none align-middle text-xs">
  <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" height="24px" viewBox="0 0 24 24"
    width="24px" fill="currentColor">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
      d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z" />
  </svg>
  <span class="flex absolute h-5 w-5 -top-[0.6rem] ltr:right-0 rtl:left-0  ltr:-mr-[0.6rem] rtl:-ml-1">
    <span
      class="animate-slow-ping absolute inline-flex top-[7px] -start-[1.4px] h-[10px] w-[10px] !rounded-full bg-success/50 opacity-75"></span>
    <span class="pulse-success"></span>
</button>
<div
  class="main-header-dropdown !-mt-2 !p-0 hs-dropdown-menu ti-dropdown-menu  bg-white !w-[20rem] !border-0 border-defaultborder hidden !m-0"
  aria-labelledby="dropdown-notification">

  <div class="menu-header-content bg-primary text-white">
    <div class="flex items-center justify-between">
      <h6 class="mb-0 text-[.9375rem] font-semibold text-white">Notifiche</h6>
      <button type="button" class="badge !rounded-full bg-warning !text-[0.7rem] !font-medium text-black cursor-pointer" id="mark-all-read">Segna come lette</button>
      {{-- <span class="badge !rounded-full bg-warning !text-[0.7rem] !font-medium text-black">Mark All Read</span> --}}
    </div>
    {{-- <p class="dropdown-title-text subtext mb-0 text-white opacity-[0.6] pb-0 text-[0.75rem] ">You have 6
      unread Notifications</p> --}}
  </div>
  <div class="dropdown-divider"></div>
  <ul class="list-none mb-0" id="header-notification-scroll">
    <li class="dropdown-item px-3">
      <div class="flex items-center">
        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-pinkmain">
          <i class="la la-file-alt text-[1.25rem]"></i>
        </span>
        <div class="ms-3">
          <a href="mail.html">
            <h5 class="notification-label text-defaulttextcolor mb-1">New files available</h5>
          </a>
          <div class="notification-subtext">10 hour ago</div>
        </div>
        <div class="ms-auto">
          <a href="mail.html"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
        </div>
      </div>
    </li>
    <li class="dropdown-item px-3">
      <div class="flex items-center">
        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-purplemain">
          <i class="la la-gem text-[1.25rem]"></i>
        </span>
        <div class="ms-3">
          <a href="mail.html">
            <h5 class="notification-label text-defaulttextcolor mb-1">Updates Available</h5>
          </a>
          <div class="notification-subtext">2 days ago</div>
        </div>
        <div class="ms-auto">
          <a href="mail.html"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
        </div>
      </div>
    </li>
    <li class="dropdown-item px-3">
      <div class="flex items-center">
        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-success">
          <i class="la la-shopping-basket text-[1.25rem]"></i>
        </span>
        <div class="ms-3">
          <a href="mail.html">
            <h5 class="notification-label text-defaulttextcolor mb-1">New Order Received</h5>
          </a>
          <div class="notification-subtext">1 hour ago</div>
        </div>
        <div class="ms-auto">
          <a href="mail.html"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
        </div>
      </div>
    </li>
    <li class="dropdown-item px-3">
      <div class="flex items-center">
        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-warning">
          <i class="la la-envelope-open text-[1.25rem] text-fixed-white"></i>
        </span>
        <div class="ms-3">
          <a href="mail.html">
            <h5 class="notification-label text-defaulttextcolor mb-1">New review received</h5>
          </a>
          <div class="notification-subtext">1 day ago</div>
        </div>
        <div class="ms-auto">
          <a href="mail.html"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
        </div>
      </div>
    </li>
    <li class="dropdown-item px-3">
      <div class="flex items-center">
        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-danger">
          <i class="la la-user-check text-[1.25rem]"></i>
        </span>
        <div class="ms-3">
          <a href="mail.html">
            <h5 class="notification-label text-defaulttextcolor mb-1">22 verified registrations</h5>
          </a>
          <div class="notification-subtext">2 hour ago</div>
        </div>
        <div class="ms-auto">
          <a href="mail.html"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
        </div>
      </div>
    </li>
    <li class="dropdown-item px-3">
      <div class="flex items-center !rounded-bl-md !rounded-br-md">
        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-primary">
          <i class="la la-check-circle text-[1.25rem]"></i>
        </span>
        <div class="ms-3">
          <a href="mail.html">
            <h5 class="notification-label text-defaulttextcolor mb-1">Project has been approved</h5>
          </a>
          <div class="notification-subtext">4 hour ago</div>
        </div>
        <div class="ms-auto">
          <a href="mail.html"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
        </div>
      </div>
    </li>
  </ul>
  <div class="text-center !rounded-bl-md !rounded-br-md dropdown-footer">
    <a href="/notifications" class="text-primary fs-13">VEDI TUTTE</a>
  </div>
</div>
</div>