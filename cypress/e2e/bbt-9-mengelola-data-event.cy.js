const loginSebagaiAdmin = () => {
  cy.visit('/login');
  cy.get('[data-cy="btn-login-google"]')
    .should('be.visible')
    .invoke('attr', 'href', '/auth/google?cy_scenario=multi-role');
  cy.get('[data-cy="btn-login-google"]').click();
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="btn-switch-role-admin"]').click();
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="badge-active-role"]').should('contain', 'Admin');
};

const pilihKategoriEventPertama = () => {
  return cy.get('[data-cy="select-eventkategori_id"]').find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, 'opsi kategori event tersedia').to.not.equal(undefined);
    const kategoriId = opsiValid.value;

    return cy.get('[data-cy="select-eventkategori_id"]')
      .select(kategoriId, { force: true })
      .then(() => kategoriId);
  });
};

describe('BBT-9 Mengelola Data Event', () => {
  it('admin membaca daftar event, melakukan pencarian dan filter, lalu mengedit data event', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const namaEvent = `Event BBT9 ${suffix}`;
    const lokasiAwal = 'AulaA';
    const lokasiBaru = 'AulaB';
    const deskripsiBaru = 'Deskripsi event diperbarui pada skenario BBT-9.';
    const tanggalEvent = new Date();
    tanggalEvent.setDate(tanggalEvent.getDate() + 5);
    const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);

    loginSebagaiAdmin();
    cy.get('[data-cy="table-event-list"]').should('be.visible');
    cy.intercept('POST', '**/app/event/store').as('storeEventBBT9');
    cy.intercept('POST', '**/app/event/update**').as('updateEventBBT9');
    cy.intercept('POST', '**/app/event/data/list*').as('listEventBBT9');
    cy.intercept('POST', '**/app/event/data/list?kategori=*').as('listEventFilteredBBT9');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="form-create-event"]').should('exist');
    cy.get('[data-cy="input-nama_event"]').type(namaEvent);
    pilihKategoriEventPertama().then((kategoriId) => {
      cy.wrap(kategoriId).as('kategoriEventId');
    });
    cy.get('[data-cy="input-tanggal_event"]').clear().type(tanggalEventFormat, { force: true });
    cy.get('[data-cy="input-waktu_mulai_event"]').clear().type('08:00', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').clear().type('10:00', { force: true });
    cy.get('[data-cy="input-lokasi_event"]').type(lokasiAwal);
    cy.get('[data-cy="textarea-deskripsi_event"]').type('Event untuk skenario pengelolaan data event.');
    cy.get('[data-cy="btn-simpan-event"]').click();

    cy.wait('@storeEventBBT9').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const eventIdEnc = storeInterception.response?.body?.data?.event_id ?? '';
      expect(eventIdEnc).to.be.a('string').and.not.equal('');
      cy.wrap(eventIdEnc).as('eventIdEnc');
    });

    cy.get('@eventIdEnc').then((eventIdEnc) => {
      cy.get('[data-cy^="input-table-search-"]').first().clear().type(namaEvent);
      cy.get(`[data-cy="btn-action-edit-${eventIdEnc}"]`, { timeout: 10000 })
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });

    cy.get('[data-cy="form-create-event"]').should('exist');
    cy.get('[data-cy="input-lokasi_event"]')
      .click()
      .type('{selectall}{backspace}')
      .type(lokasiBaru)
      .should('have.value', lokasiBaru);
    cy.get('[data-cy="textarea-deskripsi_event"]').clear().type(deskripsiBaru);
    cy.get('[data-cy="btn-simpan-event"]').click();

    cy.wait('@updateEventBBT9').then((updateInterception) => {
      expect(updateInterception.response?.statusCode).to.equal(200);
      expect(updateInterception.response?.body?.status).to.equal(true);
      const body = String(updateInterception.request?.body ?? '');
      expect(body).to.include('name="lokasi_event"');
      expect(body).to.include(`\r\n\r\n${lokasiBaru}\r\n`);
    });

    cy.get('[data-cy^="input-table-search-"]').first().clear();
    cy.get('@kategoriEventId').then((kategoriEventId) => {
      cy.get('[data-cy="select-filter-kategori-event"]')
        .select(kategoriEventId, { force: true })
        .should('have.value', kategoriEventId);
    });
    cy.wait('@listEventFilteredBBT9');

    // Assert (Verifikasi UI)
    cy.location('pathname').should('eq', '/app/event');
    cy.get('[data-cy^="input-table-search-"]').first().clear().type(namaEvent);
    cy.wait('@listEventBBT9');
    cy.contains('[data-cy="table-event-list"] tbody tr', namaEvent, { timeout: 10000 })
      .should('be.visible');
    cy.get('@eventIdEnc').then((eventIdEnc) => {
      cy.get(`[data-cy="btn-action-edit-${eventIdEnc}"]`, { timeout: 10000 })
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });
    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-lokasi_event"]').should('have.value', lokasiBaru);
  });

  it('admin gagal mengedit event saat nama event dikosongkan', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const namaEvent = `Event BBT9 Invalid ${suffix}`;
    const tanggalEvent = new Date();
    tanggalEvent.setDate(tanggalEvent.getDate() + 6);
    const tanggalEventFormat = tanggalEvent.toISOString().slice(0, 10);

    loginSebagaiAdmin();
    cy.get('[data-cy="table-event-list"]').should('be.visible');
    cy.intercept('POST', '**/app/event/store').as('storeEventBBT9Invalid');
    cy.intercept('POST', '**/app/event/update**').as('updateEventBBT9Invalid');
    cy.intercept('POST', '**/app/event/data/list*').as('listEventBBT9Invalid');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-event"]').click();
    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-nama_event"]').type(namaEvent);
    pilihKategoriEventPertama();
    cy.get('[data-cy="input-tanggal_event"]').clear().type(tanggalEventFormat, { force: true });
    cy.get('[data-cy="input-waktu_mulai_event"]').clear().type('13:00', { force: true });
    cy.get('[data-cy="input-waktu_selesai_event"]').clear().type('15:00', { force: true });
    cy.get('[data-cy="input-lokasi_event"]').type('AulaC');
    cy.get('[data-cy="textarea-deskripsi_event"]').type('Event untuk edge case edit nama kosong.');
    cy.get('[data-cy="btn-simpan-event"]').click();

    cy.wait('@storeEventBBT9Invalid').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const eventIdEnc = storeInterception.response?.body?.data?.event_id ?? '';
      expect(eventIdEnc).to.be.a('string').and.not.equal('');
      cy.wrap(eventIdEnc).as('eventIdEncInvalid');
    });

    cy.get('@eventIdEncInvalid').then((eventIdEnc) => {
      cy.get('[data-cy^="input-table-search-"]').first().clear().type(namaEvent);
      cy.wait('@listEventBBT9Invalid');
      cy.get(`[data-cy="btn-action-edit-${eventIdEnc}"]`, { timeout: 10000 })
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });

    cy.get('[data-cy="form-create-event"]').should('be.visible');
    cy.get('[data-cy="input-nama_event"]').click().type('{selectall}{backspace}').should('have.value', '');
    cy.get('[data-cy="btn-simpan-event"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@updateEventBBT9Invalid').then((updateInterception) => {
      expect(updateInterception.response?.statusCode).to.equal(422);
      expect(Boolean(updateInterception.response?.body?.errors?.nama_event?.length)).to.equal(true);
    });
    cy.location('pathname').should('eq', '/app/event');
    cy.get('[data-cy="form-create-event"]').should('exist');
    cy.get('[data-cy="input-nama_event"]').should('exist').invoke('val').should('eq', '');
  });
});
