const loginDenganSkenario = (scenario, expectedRole) => {
  cy.clearCookies();
  cy.clearLocalStorage();
  cy.visit(`/auth/google?cy_scenario=${scenario}`);
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');

  if (expectedRole) {
    cy.get('[data-cy="menu-user-toggle"]').click();
    cy.get('[data-cy="badge-active-role"]').should('contain', expectedRole);
    cy.get('[data-cy="menu-user-toggle"]').click();
  }
};

const loginSebagaiAdmin = () => {
  loginDenganSkenario('multi-role', 'Staf');
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="btn-switch-role-admin"]').click();
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="badge-active-role"]').should('contain', 'Admin');
};

const requestListKategori = (csrfToken, keyword) => {
  return cy.request({
    method: 'POST',
    url: '/app/event/data/kategori-list',
    form: true,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': String(csrfToken),
    },
    body: {
      draw: '1',
      start: '0',
      length: '50',
      'search[value]': keyword,
      'search[regex]': 'false',
    },
  });
};

const ambilDeleteIdDariActionHtml = (actionHtml) => {
  const matched = String(actionHtml ?? '').match(/jf-delete="([^"]+)"/);
  expect(matched, 'id hapus tersedia pada kolom action kategori').to.not.equal(null);
  return matched[1];
};

const ambilEditIdDariActionHtml = (actionHtml) => {
  const matched = String(actionHtml ?? '').match(/jf-edit="([^"]+)"/);
  expect(matched, 'id edit tersedia pada kolom action kategori').to.not.equal(null);
  return matched[1];
};

const bukaHalamanKategoriEvent = () => {
  cy.intercept('POST', '**/app/event/data/kategori-list*').as('listKategori');
  cy.visit('/app/event/');
  cy.get('[data-cy="tab-event-kategori"]').click();
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event/kategori');
  cy.wait('@listKategori').its('response.statusCode').should('eq', 200);
};

const tambahKategoriDariUI = (namaKategori, deskripsiKategori) => {
  cy.intercept('POST', '**/app/event/store/kategori*').as('storeKategori');
  cy.get('[data-cy="btn-add-event-kategori"]').click();
  cy.get('[data-cy="modal-event-kategori-form"]').should('be.visible');
  cy.get('[data-cy="input-nama_kategori"]').clear({ force: true }).type(namaKategori, { force: true });
  cy.get('[data-cy="textarea-deskripsi_kategori"]').clear({ force: true }).type(deskripsiKategori, { force: true });
  cy.get('[data-cy="btn-save-event-kategori"]').click({ force: true });
  cy.wait('@storeKategori').then((storeInterception) => {
    expect(storeInterception.response?.statusCode).to.equal(200);
    expect(storeInterception.response?.body?.status).to.equal(true);
  });
};

describe('BBT-17 Mengelola Kategori Event', () => {
  it('admin membuka halaman kategori event lalu menambah kategori baru dengan data valid', () => {
    const suffix = Date.now().toString().slice(-6);
    const namaKategori = `Kategori BBT17 ${suffix}`;
    const deskripsiKategori = `Deskripsi kategori BBT17 ${suffix}`;

    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    bukaHalamanKategoriEvent();

    // Act (Isi form/klik)
    tambahKategoriDariUI(namaKategori, deskripsiKategori);

    // Assert (Verifikasi UI)
    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
      expect(csrfToken, 'csrf token halaman kategori event tersedia').to.be.a('string').and.not.equal('');
      requestListKategori(csrfToken, namaKategori).then((listResponse) => {
        expect(listResponse.status).to.equal(200);
        const rows = listResponse.body?.data ?? [];
        const createdRow = rows.find((row) => String(row?.nama_kategori ?? '') === namaKategori);
        expect(createdRow, 'kategori baru berhasil ditambahkan').to.not.equal(undefined);
        expect(String(createdRow?.deskripsi_kategori ?? '')).to.equal(deskripsiKategori);
      });
    });
  });

  it('admin mengubah nama dan deskripsi kategori event yang sudah ada', () => {
    const suffix = Date.now().toString().slice(-6);
    const namaKategoriAwal = `Kategori Edit BBT17 ${suffix}`;
    const deskripsiAwal = `Deskripsi awal BBT17 ${suffix}`;
    const namaKategoriBaru = `${namaKategoriAwal} Updated`;
    const deskripsiBaru = `${deskripsiAwal} Updated`;

    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    bukaHalamanKategoriEvent();
    tambahKategoriDariUI(namaKategoriAwal, deskripsiAwal);

    // Act (Isi form/klik)
    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
      expect(csrfToken, 'csrf token halaman kategori event tersedia').to.be.a('string').and.not.equal('');
      requestListKategori(csrfToken, namaKategoriAwal).then((listResponse) => {
        expect(listResponse.status).to.equal(200);
        const rows = listResponse.body?.data ?? [];
        const row = rows.find((item) => String(item?.nama_kategori ?? '') === namaKategoriAwal);
        expect(row, 'kategori target tersedia sebelum diubah').to.not.equal(undefined);
        const editId = ambilEditIdDariActionHtml(row?.action ?? '');

        cy.request({
          method: 'POST',
          url: '/app/event/update/kategori',
          form: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': String(csrfToken),
          },
          body: {
            id: editId,
            nama_kategori: namaKategoriBaru,
            deskripsi_kategori: deskripsiBaru,
          },
        }).then((updateResponse) => {
          expect(updateResponse.status).to.equal(200);
          expect(updateResponse.body?.status).to.equal(true);
        });
      });
    });

    // Assert (Verifikasi UI)
    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
      expect(csrfToken, 'csrf token halaman kategori event tersedia').to.be.a('string').and.not.equal('');
      requestListKategori(csrfToken, namaKategoriBaru).then((listResponse) => {
        expect(listResponse.status).to.equal(200);
        const rows = listResponse.body?.data ?? [];
        const updatedRow = rows.find((row) => String(row?.nama_kategori ?? '') === namaKategoriBaru);
        expect(updatedRow, 'kategori berhasil diperbarui').to.not.equal(undefined);
        expect(String(updatedRow?.deskripsi_kategori ?? '')).to.equal(deskripsiBaru);
      });
    });
  });

  it('admin menghapus kategori event dan kategori tidak muncul lagi pada form pembuatan event', () => {
    const suffix = Date.now().toString().slice(-6);
    const namaKategori = `Kategori Delete BBT17 ${suffix}`;
    const deskripsiKategori = `Deskripsi delete BBT17 ${suffix}`;

    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    bukaHalamanKategoriEvent();
    tambahKategoriDariUI(namaKategori, deskripsiKategori);
    cy.intercept('POST', '**/app/event/destroy/kategori*').as('destroyKategori');

    // Act (Isi form/klik)
    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
      expect(csrfToken, 'csrf token halaman kategori event tersedia').to.be.a('string').and.not.equal('');
      requestListKategori(csrfToken, namaKategori).then((listResponse) => {
        expect(listResponse.status).to.equal(200);
        const rows = listResponse.body?.data ?? [];
        const row = rows.find((item) => String(item?.nama_kategori ?? '') === namaKategori);
        expect(row, 'kategori target tersedia sebelum dihapus').to.not.equal(undefined);
        const deleteId = ambilDeleteIdDariActionHtml(row?.action ?? '');

        cy.request({
          method: 'POST',
          url: '/app/event/destroy/kategori',
          form: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': String(csrfToken),
          },
          body: { id: deleteId },
        }).then((destroyResponse) => {
          expect(destroyResponse.status).to.equal(200);
          expect(destroyResponse.body?.status).to.equal(true);
        });
      });
    });

    // Assert (Verifikasi UI)
    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
      requestListKategori(csrfToken, namaKategori).then((listResponse) => {
        expect(listResponse.status).to.equal(200);
        const rows = listResponse.body?.data ?? [];
        const deletedRow = rows.find((row) => String(row?.nama_kategori ?? '') === namaKategori);
        expect(deletedRow, 'kategori sudah terhapus dari daftar').to.equal(undefined);
      });
    });

    cy.visit('/app/event/');
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="modal-event-form"]').should('be.visible');
    cy.get('[data-cy="select-eventkategori_id"] option').then(($options) => {
      const daftarNamaKategori = [...$options].map((option) => option.textContent.trim());
      expect(daftarNamaKategori).to.not.include(namaKategori);
    });
  });

  it('pengguna role mahasiswa ditolak saat mengakses halaman kategori event', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('mahasiswa-role', 'Mahasiswa');

    // Act (Isi form/klik)
    cy.request({
      method: 'GET',
      url: '/app/event/kategori',
      failOnStatusCode: false,
    }).as('requestKategoriMahasiswa');

    // Assert (Verifikasi UI)
    cy.get('@requestKategoriMahasiswa').its('status').should('eq', 403);
  });
});
