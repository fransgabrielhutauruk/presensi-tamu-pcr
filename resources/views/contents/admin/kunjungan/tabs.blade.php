<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'kunjungan' ? 'active' : '' }}"
            href="{{ route('app.kunjungan.index') }}" data-cy="tab-kunjungan-list">Kelola Semua Kunjungan</a>
    </li>
    @if (hasAnyActiveRole([\App\Enums\UserRole::ADMIN->value]))
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'kelola-opsi' ? 'active' : '' }}"
            href="{{ route('app.kunjungan.show', ['param1' => 'opsi']) }}" data-cy="tab-kunjungan-opsi">Kelola Opsi</a>
    </li>
    @endif
</ul>
