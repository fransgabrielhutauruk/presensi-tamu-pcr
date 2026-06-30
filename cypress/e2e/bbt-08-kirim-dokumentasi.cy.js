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

describe('BBT-8 Kirim Dokumentasi', () => {
  it('staf mengisi URL dokumentasi valid lalu sukses kembali ke daftar event', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const tanggalEvent = new Date();
    tanggalEvent.setDate(tanggalEvent.getDate() + 3);
    const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);
    const linkDokumentasi = `https://drive.google.com/drive/folders/bbt8-${suffix}`;

    loginSebagaiStaf();
    cy.intercept('POST', '**/app/event/store').as('storeEventBBT8');
    cy.intercept('POST', '**/app/event/update**').as('updateEventBBT8');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-nama_event"]').type(`Event BBT8 ${suffix}`);
    pilihKategoriEventPertama();
    cy.get('[data-cy="input-tanggal_event"]').then($el => { $el[0]._flatpickr.setDate(tanggalEventFormat); });
    cy.get('[data-cy="input-waktu_mulai_event"]').clear().type('10:00', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').clear().type('12:00', { force: true });
    cy.get('[data-cy="radio-jenis_kegiatan-non-pmb"]').check({ force: true });
    cy.get('[data-cy="radio-kategori_lokasi-dalam-kampus"]').check({ force: true });
    cy.get('[data-cy="input-lokasi_event"]').type('Aula Utama PCR');
    cy.get('[data-cy="textarea-deskripsi_event"]').type('Event untuk pengujian BBT-8.');
    cy.get('[data-cy="btn-simpan-event"]').click();

    cy.wait('@storeEventBBT8').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const eventIdEnc = storeInterception.response?.body?.data?.event_id ?? '';
      expect(eventIdEnc).to.be.a('string').and.not.equal('');

      cy.get(`[data-cy="btn-action-edit-${eventIdEnc}"]`, { timeout: 10000 })
        .should('be.visible')
        .click({ force: true });

      cy.get('[data-cy="form-create-event"]').should('be.visible');
      cy.get('[data-cy="input-link_dokumentasi_event"]')
        .should('be.visible')
        .clear()
        .type(linkDokumentasi);
      cy.get('[data-cy="btn-simpan-event"]').click();

      cy.wait('@updateEventBBT8').then((updateInterception) => {
        expect(updateInterception.response?.statusCode).to.equal(200);
        expect(updateInterception.response?.body?.status).to.equal(true);
      });

      // Assert (Verifikasi UI)
      cy.location('pathname').should('eq', '/app/event');
      cy.get(`[data-cy="link-dokumentasi-event-${eventIdEnc}"]`, { timeout: 10000 })
        .should('be.visible')
        .and('have.attr', 'href', linkDokumentasi);
    });
  });

  it('staf gagal mengirim dokumentasi saat URL tidak valid', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const tanggalEvent = new Date();
    tanggalEvent.setDate(tanggalEvent.getDate() + 4);
    const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);
    const linkDokumentasiInvalid = `drive.google.com/folders/bbt8-${suffix}`;

    loginSebagaiStaf();
    cy.intercept('POST', '**/app/event/store').as('storeEventBBT8InvalidUrl');
    cy.intercept('POST', '**/app/event/update**').as('updateEventBBT8InvalidUrl');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-nama_event"]').type(`Event BBT8 Invalid URL ${suffix}`);
    pilihKategoriEventPertama();
    cy.get('[data-cy="input-tanggal_event"]').then($el => { $el[0]._flatpickr.setDate(tanggalEventFormat); });
    cy.get('[data-cy="input-waktu_mulai_event"]').clear().type('13:00', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').clear().type('15:00', { force: true });
    cy.get('[data-cy="radio-jenis_kegiatan-non-pmb"]').check({ force: true });
    cy.get('[data-cy="radio-kategori_lokasi-dalam-kampus"]').check({ force: true });
    cy.get('[data-cy="input-lokasi_event"]').type('Ruang Seminar PCR');
    cy.get('[data-cy="textarea-deskripsi_event"]').type('Event uji validasi URL dokumentasi BBT-8.');
    cy.get('[data-cy="btn-simpan-event"]').click();

    cy.wait('@storeEventBBT8InvalidUrl').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const eventIdEnc = storeInterception.response?.body?.data?.event_id ?? '';
      expect(eventIdEnc).to.be.a('string').and.not.equal('');

      cy.get(`[data-cy="btn-action-edit-${eventIdEnc}"]`, { timeout: 10000 })
        .should('be.visible')
        .click({ force: true });

      cy.get('[data-cy="form-create-event"]').should('be.visible');
      cy.get('[data-cy="input-link_dokumentasi_event"]')
        .should('be.visible')
        .clear()
        .type(linkDokumentasiInvalid);
      cy.get('[data-cy="btn-simpan-event"]').click();

      // Assert (Verifikasi UI)
      cy.wait('@updateEventBBT8InvalidUrl').then((updateInterception) => {
        expect(updateInterception.response?.statusCode).to.equal(422);
        expect(
          Boolean(updateInterception.response?.body?.errors?.link_dokumentasi_event?.length)
        ).to.equal(true);
      });
      cy.location('pathname').should('eq', '/app/event');
      cy.get('[data-cy="form-create-event"]').should('exist');
      cy.get('[data-cy="input-link_dokumentasi_event"]')
        .should('exist')
        .and('have.value', linkDokumentasiInvalid);
      cy.get(`[data-cy="link-dokumentasi-event-${eventIdEnc}"]`).should('not.exist');
      cy.get(`[data-cy="link-tambah-dokumentasi-event-${eventIdEnc}"]`, { timeout: 10000 }).should('exist');
    });
  });
});
