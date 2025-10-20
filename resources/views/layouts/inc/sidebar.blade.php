<div id="kt_app_sidebar_wrapper" class="app-sidebar-wrapper hover-scroll-y my-5 my-lg-2" data-kt-scroll="true"
    data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
    data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_sidebar_wrapper"
    data-kt-scroll-offset="5px">
    <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false"
        class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary px-6 mb-5">

        @if(hasAnyActiveRole(['Mahasiswa', 'Staf', 'Admin', 'Eksekutif']))
        <x-theme.menu link="{{ route('app.dashboard.index') }}" text="Dashboard" icon="ki-outline ki-graph-up" :active="$pageData->activeMenu == 'dashboard'" />
        <x-theme.menu link="{{ route('app.event.index') }}" text="Event" icon="ki-outline ki-calendar-edit" :active="$pageData->activeMenu == 'event' || $pageData->activeMenu == 'event-kategori'" />
        @endif

        @if(hasAnyActiveRole(['Admin', 'Eksekutif']))
        <div class="separator separator-dashed border-gray-10 my-2"></div>
        <x-theme.menu link="{{ route('app.user.index') }}" text="Pengguna" icon="ki-outline ki-setting-3" :active="$pageData->activeMenu == 'pengguna'" />
        @endif

    </div>
</div>