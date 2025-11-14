@extends('layouts.tamu.base')

@push('head')
    @include('contents.frontend.partials.common.head-data')
@endpush

@section('base-content')
    {{-- @include('contents.frontend.partials.common.preloader') --}}

    <div class="position-fixed top-0 end-0 p-2 pe-3" style="z-index: 20;">
        <x-language-switcher />
    </div>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <x-alert.swal-session />

    @push('head')
        <style>
            :root {
                --primary-main-rgb: var(--primary-rgb-950) !important;
                --primary-main: var(--primary-950) !important;
                --secondary-main: var(--secondary-600) !important;
            }

            body {
                background:
                    radial-gradient(circle at 22% 28%, rgba(191, 219, 254, 0.7) 0%, rgba(191, 219, 254, 0.35) 18%, rgba(191, 219, 254, 0.08) 42%, rgba(191, 219, 254, 0) 65%),
                    linear-gradient(135deg, rgba(59, 130, 246, 0.10) 0%, rgba(99, 102, 241, 0.08) 37%, rgba(56, 189, 248, 0.07) 63%, rgba(14, 165, 233, 0.05) 85%),
                    linear-gradient(to bottom, #f0faff 0%, #e0f2fe 55%, #ffffff 90%);
            }
        </style>
    @endpush

    {{-- @include('contents.frontend.partials.common.prototype-btns', [
        'buttons' => env('APP_ENV') !== 'production',
    ]) --}}

    @include('contents.frontend.partials.common.footer')
    @include('contents.frontend.partials.common.scroll-to-top')
    @include('contents.frontend.partials.common.script-data')
@endsection
