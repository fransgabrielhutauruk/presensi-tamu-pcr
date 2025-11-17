<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'event' ? 'active' : '' }}"
            href="{{ route('app.event.index') }}">Event</a>
    </li>
    @if (hasAnyActiveRole(\App\Enums\UserRole::getAdminEksekutifSecurityRoles()))
        <li class="nav-item">
            <a class="nav-link {{ $pageData->activeMenu == 'event-kategori' ? 'active' : '' }}"
                href="{{ route('app.event.show', ['param1' => 'kategori']) }}">Kategori</a>
        </li>
    @endif
</ul>
