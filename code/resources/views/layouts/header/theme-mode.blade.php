<style>
    input:is([type='date'], [type='time']) {
        cursor: pointer;
    }

    .dark input:is([type='date'], [type='time']) {
        color-scheme: dark;
    }

    .dark input:is([type='date'], [type='time'])::-webkit-calendar-picker-indicator {
        cursor: pointer;
        filter: brightness(0) invert(1) !important;
        opacity: 0.9;
    }

    .dark input[type='checkbox']:not(:checked):not(:indeterminate) {
        background-color: rgb(var(--light)) !important;
        border-color: rgb(var(--default-text-color) / 0.45) !important;
    }

    .dark input.ti-switch[type='checkbox']:not(:checked)::before {
        background-color: rgb(var(--default-text-color) / 0.85) !important;
    }

    /* Keep secondary text actions legible against the dark page background. */
    .dark .ti-btn-secondary {
        background-color: rgb(var(--secondary) / 0.35) !important;
        border-color: rgb(var(--secondary) / 0.65) !important;
        color: rgb(255 255 255 / 0.95) !important;
    }

    .dark .ti-btn-secondary:hover,
    .dark .ti-btn-secondary:focus {
        background-color: rgb(var(--secondary)) !important;
        border-color: rgb(var(--secondary)) !important;
        color: rgb(255 255 255) !important;
    }

    .dark .ti-btn-danger {
        background-color: rgb(var(--danger) / 0.3) !important;
        border-color: rgb(var(--danger) / 0.65) !important;
        color: rgb(255 255 255 / 0.95) !important;
    }

    .dark .ti-btn-danger:hover,
    .dark .ti-btn-danger:focus {
        background-color: rgb(var(--danger)) !important;
        border-color: rgb(var(--danger)) !important;
        color: rgb(255 255 255) !important;
    }

    /* The template keeps --primary unchanged in dark mode; soften full primary actions. */
    .dark .ti-btn-primary-full {
        background-color: rgb(45 114 184) !important;
        border-color: rgb(45 114 184) !important;
        color: rgb(255 255 255) !important;
    }

    .dark .ti-btn-primary-full:hover,
    .dark .ti-btn-primary-full:focus {
        background-color: rgb(37 99 170) !important;
        border-color: rgb(37 99 170) !important;
        color: rgb(255 255 255) !important;
    }

    /* Light buttons are used for cancel, close and inactive-tab actions. */
    .dark .ti-btn-light {
        background-color: rgb(var(--default-text-color) / 0.12) !important;
        border-color: rgb(var(--default-text-color) / 0.35) !important;
        color: rgb(var(--default-text-color)) !important;
    }

    .dark .ti-btn-light:hover,
    .dark .ti-btn-light:focus {
        background-color: rgb(var(--default-text-color) / 0.22) !important;
        border-color: rgb(var(--default-text-color) / 0.5) !important;
        color: rgb(var(--default-text-color)) !important;
    }
</style>

<div class="header-element header-theme-mode !flex !items-center !py-[1rem] !px-[0.65rem]">
    <button
        type="button"
        data-manage-theme-toggle
        class="group inline-flex flex-shrink-0 items-center justify-center rounded-full text-xs transition-all dark:text-white/70 dark:hover:bg-black/20 dark:hover:text-white dark:focus:ring-white/10 dark:focus:ring-offset-white/10"
        aria-label="Cambia tema chiaro o scuro"
        title="Cambia tema chiaro o scuro"
    >
        <svg xmlns="http://www.w3.org/2000/svg" data-manage-theme-icon="light" class="header-link-icon" height="24" viewBox="0 -960 960 960" width="24" aria-hidden="true">
            <path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Zm0-80q88 0 158-48.5T740-375q-20 5-40 8t-40 3q-123 0-209.5-86.5T364-660q0-20 3-40t8-40q-78 32-126.5 102T200-480q0 116 82 198t198 82Z" />
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" data-manage-theme-icon="dark" class="header-link-icon" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24" aria-hidden="true" hidden>
            <path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z" />
        </svg>
    </button>
</div>
