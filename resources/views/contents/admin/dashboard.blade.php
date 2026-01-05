@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')
@section('toolbar')
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    {{ $dashboardData['title'] ?? 'Dashboard' }}
                </h1>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-fluid" data-cue="slideInLeft" data-duration="1000"
        data-delay="0">
        
        @if($userRole === 'Admin')
            @include('contents.admin.dashboard.admin-dashboard')
        @elseif($userRole === 'Eksekutif')
            @include('contents.admin.dashboard.eksekutif-dashboard')
        @elseif($userRole === 'Security')
            @include('contents.admin.dashboard.security-dashboard')
        @else
            <div class="alert alert-warning">
                <strong>Perhatian!</strong> Role tidak dikenali atau Anda tidak memiliki akses ke dashboard.
            </div>
        @endif
        
    </div>
@endsection