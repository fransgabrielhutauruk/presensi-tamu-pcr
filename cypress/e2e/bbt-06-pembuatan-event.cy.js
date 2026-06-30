const loginSebagaiStaf = () => {
  cy.visit('/login');
  cy.get('[data-cy="btn-login-google"]')
    .should('be.visible')
    .invoke('attr', 'href', '/auth/google?cy_scenario=single-role');
  cy.get('[data-cy="btn-login-google"]').click();
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');
};

const pilihKategoriEventPertama = () => {
  cy.get('[data-cy="select-eventkategori_id"]').find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, 'opsi kategori event tersedia').to.not.equal(undefined);
    cy.get('[data-cy="select-eventkategori_id"]').select(opsiValid.value, { force: true });
  });
};

describe('BBT-6 Pembuatan Event Baru', () => {
  it('staf mengisi semua field wajib dengan data valid lalu masuk ke halaman detail QR event', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const tanggalEvent = new Date();
    tanggalEvent.setDate(tanggalEvent.getDate() + 2);
    const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);
    loginSebagaiStaf();
    cy.intercept('POST', '**/app/event/store').as('storeEvent');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-nama_event"]').type(`Event Cypress ${suffix}`);
    pilihKategoriEventPertama();
    cy.get('[data-cy="input-tanggal_event"]').then($el => { $el[0]._flatpickr.setDate(tanggalEventFormat); });
    cy.get('[data-cy="input-waktu_mulai_event"]').clear().type('09:00', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').clear().type('11:00', { force: true });
    cy.get('[data-cy="radio-jenis_kegiatan-non-pmb"]').check({ force: true });
    cy.get('[data-cy="radio-kategori_lokasi-dalam-kampus"]').check({ force: true });
    cy.get('[data-cy="input-lokasi_event"]').type('Gedung Utama PCR');
    cy.get('[data-cy="textarea-deskripsi_event"]').type('Event untuk pengujian Cypress BBT-6.');
    cy.get('[data-cy="btn-simpan-event"]').click();

    cy.wait('@storeEvent').then((interception) => {
      expect(interception.response?.statusCode).to.equal(200);
      expect(interception.response?.body?.status).to.equal(true);
      expect(interception.response?.body?.message).to.equal('Data event berhasil disimpan');
      const eventIdEnc = interception.response?.body?.data?.event_id ?? interception.response?.body?.event_id;
      expect(eventIdEnc).to.be.a('string').and.not.equal('');

      cy.visit(`/app/event/qr/${eventIdEnc}`);

      // Assert (Verifikasi UI)
      cy.url().should('include', `/app/event/qr/${eventIdEnc}`);
      cy.get('[data-cy="event-detail-info"]').should('be.visible');
      cy.get('[data-cy="qr-event-code"]').find('svg').should('exist');
      cy.get('[data-cy="input-event-presensi-link"]')
        .invoke('val')
        .should('be.a', 'string')
        .and('include', '/event/');
    });
  });

  it('staf gagal menyimpan event saat salah satu field wajib dikosongkan', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const tanggalEvent = new Date();
    tanggalEvent.setDate(tanggalEvent.getDate() + 2);
    const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);

    loginSebagaiStaf();
    cy.intercept('POST', '**/app/event/store').as('storeEventMissingField');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-nama_event"]').type(`Event Wajib ${suffix}`);
    pilihKategoriEventPertama();
    cy.get('[data-cy="input-tanggal_event"]').then($el => { $el[0]._flatpickr.setDate(tanggalEventFormat); });
    cy.get('[data-cy="input-waktu_mulai_event"]').clear().type('09:00', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').clear().type('10:00', { force: true });
    cy.get('[data-cy="textarea-deskripsi_event"]').type('Uji field wajib kosong.');
    cy.get('[data-cy="btn-simpan-event"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@storeEventMissingField').then((interception) => {
      expect(interception.response?.statusCode).to.equal(422);
    });
    cy.location('pathname').should('eq', '/app/event');
    cy.get('[data-cy="form-create-event"]').should('exist');
  });
});
