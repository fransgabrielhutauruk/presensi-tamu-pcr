@php
    use App\Enums\UserRole;
@endphp

<div id="kt_app_sidebar_wrapper" class="app-sidebar-wrapper hover-scroll-y my-5 my-lg-2" data-kt-scroll="true"
    data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
    data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_sidebar_wrapper"
    data-kt-scroll-offset="5px">
    <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false"
        class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary
            px-6 mb-5">
        @if (hasAnyActiveRole(UserRole::getAllRoles()))
            @if (hasAnyActiveRole([UserRole::EKSEKUTIF->value]))
                <x-theme.menu link="{{ route('app.dashboard.index') }}" text="Dashboard BI"
                    icon="ki-outline ki-graph-up" :active="$pageData->activeMenu == 'dashboard'" />
            @endif
            <x-theme.menu link="{{ route('app.event.index') }}" text="Event" icon="ki-outline ki-calendar-edit"
                :active="$pageData->activeMenu == 'event' || $pageData->activeMenu == 'event-kategori'" />
        @endif

        @if (hasAnyActiveRole(UserRole::getAdminEksekutifSecurityRoles()))
            <div class="separator separator-dashed border-gray-10 my-2"></div>
            <x-theme.menu link="{{ route('app.kunjungan.index') }}" text="Kunjungan" icon="ki-outline ki-user-tick"
                :active="$pageData->activeMenu == 'kunjungan'" />
            <x-theme.menu link="{{ route('app.kunjungan.monitoring') }}" text="Monitoring Kunjungan"
                icon="ki-outline ki-monitor-mobile" :active="$pageData->activeMenu == 'monitoring-kunjungan'" />
        @endif

        @if (hasAnyActiveRole([UserRole::ADMIN->value]))
            <x-theme.menu link="{{ route('app.kunjungan.validasi') }}" text="Validasi Kunjungan"
                icon="ki-outline ki-check-circle" :active="$pageData->activeMenu == 'validasi-kunjungan'" />
            <x-theme.menu link="{{ route('app.user.index') }}" text="Pengguna" icon="ki-outline ki-setting-3"
                :active="$pageData->activeMenu == 'pengguna'" />
        @endif
    </div>
</div>
