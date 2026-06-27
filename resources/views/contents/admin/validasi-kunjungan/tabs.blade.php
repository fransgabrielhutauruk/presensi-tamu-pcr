<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'validasi-kunjungan' ? 'active' : '' }} d-flex align-items-center"
            href="{{ route('app.kunjungan-validasi.index') }}" data-cy="tab-validasi-kunjungan">
            Validasi Kunjungan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $pageData->activeMenu == 'validasi-kunjungan-dihapus' ? 'active' : '' }} d-flex align-items-center"
            href="{{ route('app.kunjungan-validasi.show', ['param1' => 'dihapus']) }}"
            data-cy="tab-validasi-kunjungan-dihapus">
            Kunjungan yang Dihapus
        </a>
    </li>
</ul>
