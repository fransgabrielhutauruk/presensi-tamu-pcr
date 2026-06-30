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

const ambilKategoriEventPertama = () => {
  return cy.get('[data-cy="select-eventkategori_id"]').find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, 'opsi kategori event tersedia').to.not.equal(undefined);
    return opsiValid.value;
  });
};

const buatEventMelaluiUI = (namaEvent) => {
  const tanggalEvent = new Date();
  tanggalEvent.setDate(tanggalEvent.getDate() + 2);
  const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);

  cy.intercept('POST', '**/app/event/store').as('storeEventBBT18');
  cy.get('[data-cy="btn-tambah-event"]').click();
  cy.get('[data-cy="form-create-event"]').should('be.visible');
  cy.get('[data-cy="input-nama_event"]').clear({ force: true }).type(namaEvent, { force: true });

  return ambilKategoriEventPertama().then((kategoriId) => {
    cy.get('[data-cy="select-eventkategori_id"]').select(kategoriId, { force: true });
    cy.get('[name="jenis_kegiatan"][value="non_pmb"]').check({ force: true });
    cy.get('[name="kategori_lokasi"][value="dalam_kampus"]').check({ force: true });
    cy.get('[data-cy="input-tanggal_event"]').invoke('val', tanggalEventFormat).trigger('change', { force: true });
    cy.get('[data-cy="input-waktu_mulai_event"]').invoke('val', '09:00').trigger('change', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').invoke('val', '11:00').trigger('change', { force: true });
    cy.get('[data-cy="input-lokasi_event"]').clear({ force: true }).type('Gedung Utama PCR', { force: true });
    cy.get('[data-cy="textarea-deskripsi_event"]').clear({ force: true }).type(`Event untuk pengujian BBT-18 ${namaEvent}`, { force: true });
    cy.get('[data-cy="btn-simpan-event"]').click();

    return cy.wait('@storeEventBBT18').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);
      const eventIdEnc = String(storeInterception.response?.body?.data?.event_id ?? '');
      expect(eventIdEnc, 'event id ter-encode dari response store').to.not.equal('');
      return eventIdEnc;
    });
  });
};

const bukaQrCodeLangsung = (eventIdEnc) => {
  cy.visit(`/app/event/qr/${eventIdEnc}`);
};

const assertHalamanQrEvent = () => {
  cy.location('pathname', { timeout: 10000 }).should('include', '/app/event/qr/');
  cy.get('[data-cy="event-detail-info"]').should('be.visible');
  cy.get('[data-cy="text-event-title"]').invoke('text').then((eventTitle) => {
    expect(String(eventTitle).trim(), 'judul event pada halaman QR tersedia').to.not.equal('');
  });
  cy.get('[data-cy="qr-event-code"]').invoke('html').then((qrMarkup) => {
    expect(String(qrMarkup).toLowerCase(), 'svg QR code tersedia').to.include('<svg');
  });
  cy.get('[data-cy="input-event-presensi-link"]').invoke('val').then((presensiLink) => {
    const linkValue = String(presensiLink ?? '').trim();
    expect(linkValue, 'tautan presensi event tersedia').to.not.equal('');
    expect(linkValue, 'tautan presensi mengarah ke endpoint event').to.include('/event/');
  });
};

describe('BBT-18 Melihat QR Code Presensi Event', () => {
  it('admin membuka halaman QR Code presensi event dan melihat QR serta tautannya', () => {
    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/event/data/list*').as('listEvent');
    cy.visit('/app/event');
    cy.wait('@listEvent').its('response.statusCode').should('eq', 200);
    cy.get('[data-cy="table-event-list"]').should('be.visible');
    const suffix = Date.now().toString().slice(-6);
    const namaEvent = `Event BBT18 Admin ${suffix}`;

    // Act (Isi form/klik)
    buatEventMelaluiUI(namaEvent).then((eventIdEnc) => {
      bukaQrCodeLangsung(eventIdEnc);

      // Assert (Verifikasi UI)
      assertHalamanQrEvent();
    });
  });

  it('staf membuka halaman QR Code presensi event miliknya', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('single-role', 'Staf');
    cy.intercept('POST', '**/app/event/data/list*').as('listEventStaf');
    cy.visit('/app/event');
    cy.wait('@listEventStaf').its('response.statusCode').should('eq', 200);
    cy.get('[data-cy="table-event-list"]').should('be.visible');
    const suffix = Date.now().toString().slice(-6);
    const namaEvent = `Event BBT18 Staf ${suffix}`;

    // Act (Isi form/klik)
    buatEventMelaluiUI(namaEvent).then((eventIdEnc) => {
      bukaQrCodeLangsung(eventIdEnc);

      // Assert (Verifikasi UI)
      assertHalamanQrEvent();
    });
  });

  it('mahasiswa membuka halaman QR Code presensi event miliknya', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('mahasiswa-role', 'Mahasiswa');
    cy.intercept('POST', '**/app/event/data/list*').as('listEventMahasiswa');
    cy.visit('/app/event');
    cy.wait('@listEventMahasiswa').its('response.statusCode').should('eq', 200);
    cy.get('[data-cy="table-event-list"]').should('be.visible');
    const suffix = Date.now().toString().slice(-6);
    const namaEvent = `Event BBT18 Mahasiswa ${suffix}`;

    // Act (Isi form/klik)
    buatEventMelaluiUI(namaEvent).then((eventIdEnc) => {
      bukaQrCodeLangsung(eventIdEnc);

      // Assert (Verifikasi UI)
      assertHalamanQrEvent();
    });
  });

  it('pengguna tamu diarahkan ke halaman login saat mengakses halaman QR event', () => {
    // Arrange (Kunjungi URL)
    cy.clearCookies();
    cy.clearLocalStorage();

    // Act (Isi form/klik)
    cy.visit('/app/event/qr/contoh-id-event');

    // Assert (Verifikasi UI)
    cy.location('pathname', { timeout: 10000 }).should('eq', '/login');
    cy.get('[data-cy="btn-login-google"]').should('be.visible');
  });

  it('admin mendapat respon 404 saat membuka QR code dengan id event tidak valid', () => {
    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();

    // Act (Isi form/klik)
    cy.request({
      method: 'GET',
      url: '/app/event/qr/id-event-tidak-valid',
      failOnStatusCode: false,
    }).as('requestQrEventInvalidId');

    // Assert (Verifikasi UI)
    cy.get('@requestQrEventInvalidId').its('status').should('eq', 404);
  });
});
