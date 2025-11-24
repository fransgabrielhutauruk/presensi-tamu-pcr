@php
    $locales = getAvailableLocales();
    $currentLocale = getCurrentLocale();
@endphp

<div class="dropdown">
    <button class="btn btn-light border-0 btn-md dropdown-toggle d-flex align-items-center px-3 py-2" type="button"
        data-bs-toggle="dropdown" aria-expanded="false">
        @if ($currentLocale['code'] === 'id')
            <img src="{{ asset('theme/images/flag-id.webp') }}" class="me-2" alt="Indonesia"
                style="width: 20px; height: auto;">
        @else
            <img src="{{ asset('theme/images/flag-us.webp') }}" class="me-2" alt="English"
                style="width: 20px; height: auto;">
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0"
        style="border-radius: 12px; overflow: hidden; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
        @foreach ($locales as $code => $locale)
            <li>
                <a class="dropdown-item d-flex align-items-center px-3 py-2 {{ app()->getLocale() === $code ? 'active bg-primary-main text-white' : 'text-dark' }}"
                    href="{{ route('language.switch', $code) }}" style="transition: all 0.2s ease; border: none;">
                    <span class="me-2">{{ $locale['flag'] }}</span>
                    {{ $locale['name'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

<style>
    .dropdown-toggle:hover {
        background: rgba(255, 255, 255, 1) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-1px);
    }

    .dropdown-item:not(.active):hover {
        background-color: #f8f9fa !important;
    }

    .dropdown-menu {
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }

    .bg-primary-main {
        background-color: var(--primary-main) !important;
    }
</style>
