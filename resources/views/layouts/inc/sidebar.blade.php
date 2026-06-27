@php
    use App\Enums\UserRole;

    $hasAdminRole = hasAnyActiveRole([UserRole::ADMIN->value]);
    $hasEksekutifRole = hasAnyActiveRole([UserRole::EKSEKUTIF->value]);
    $hasSecurityRole = hasAnyActiveRole([UserRole::SECURITY->value]);
    $hasCivitasRole = hasAnyActiveRole(UserRole::getCivitasRoles());
@endphp

<div id="kt_app_sidebar_wrapper" class="app-sidebar-wrapper hover-scroll-y my-5 my-lg-2" data-kt-scroll="true"
    data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
    data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_sidebar_wrapper"
    data-kt-scroll-offset="5px">
    <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false"
        class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary
            px-6 mb-5">
        @if ($hasAdminRole || $hasEksekutifRole)
            <x-theme.menu link="{{ route('app.dashboard.index') }}" text="Dashboard" icon="ki-outline ki-graph-up"
                :active="$pageData->activeMenu == 'dashboard'" />
        @endif

        @if ($hasAdminRole || $hasEksekutifRole || $hasSecurityRole)
            <x-theme.menu link="{{ route('app.kunjungan.monitoring') }}" text="Monitoring Kunjungan"
                icon="ki-outline ki-monitor-mobile" :active="$pageData->activeMenu == 'monitoring-kunjungan'" />
        @endif

        @if ($hasAdminRole)
            <x-theme.menu link="{{ route('app.kunjungan-validasi.index', ['filter_jenis_kunjungan_validasi' => 'non_event']) }}"
                text="Validasi Kunjungan" icon="ki-outline ki-check-circle" :active="$pageData->activeMenu == 'validasi-kunjungan'" />
        @endif

        @if ($hasAdminRole || $hasEksekutifRole)
            <div class="separator separator-dashed border-gray-10 my-2"></div>
        @endif

        @if ($hasAdminRole || $hasEksekutifRole || $hasCivitasRole)
            <x-theme.menu link="{{ route('app.event.index') }}" text="Event" icon="ki-outline ki-calendar-edit"
                :active="$pageData->activeMenu == 'event' || $pageData->activeMenu == 'event-kategori'" />
        @endif

        @if ($hasAdminRole || $hasEksekutifRole)
            <x-theme.menu link="{{ route('app.kunjungan.index') }}" text="Kunjungan" icon="ki-outline ki-user-tick"
                :active="in_array($pageData->activeMenu, ['kunjungan', 'kelola-opsi'])" />
            <x-theme.menu link="{{ route('app.feedback.index') }}" text="Feedback" icon="ki-outline ki-messages"
                :active="$pageData->activeMenu == 'feedback'" />
        @endif

        @if ($hasAdminRole)
            <div class="separator separator-dashed border-gray-10 my-2"></div>
            <x-theme.menu text="Master Data" icon="ki-outline ki-archive" :active="in_array($pageData->activeMenu, ['pegawai'])">
                <x-theme.menu link="{{ route('app.master.show', 'pegawai') }}" text="Pegawai" :active="$pageData->activeMenu == 'pegawai'" />
            </x-theme.menu>
            <x-theme.menu link="{{ route('app.user.index') }}" text="Pengguna" icon="ki-outline ki-setting-3"
                :active="$pageData->activeMenu == 'pengguna'" />
            <x-theme.menu link="{{ route('app.log-aktivitas.index') }}" text="Log Aktivitas"
                icon="ki-outline ki-document" :active="$pageData->activeMenu == 'log-aktivitas'" />
        @endif
    </div>
</div>
