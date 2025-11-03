<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'kunjungan' ? 'active' : '' }}"
            href="{{ route('app.kunjungan.index') }}">Kelola Semua Kunjungan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'kelola-opsi' ? 'active' : '' }}"
            href="{{ route('app.kunjungan.show', ['param1' => 'opsi']) }}">Kelola Opsi</a>
    </li>
</ul>