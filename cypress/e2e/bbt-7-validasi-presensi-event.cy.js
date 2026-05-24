const pilihOpsiPertama = (selector) => {
  cy.get(selector).find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(undefined);
    cy.get(selector).select(opsiValid.value);
  });
};

const loginSebagaiStaf = () => {
  cy.visit('/login');
  cy.get('[data-cy="btn-login-google"]')
    .should('be.visible')
    .invoke('attr', 'href', '/auth/google?cy_scenario=single-role');
  cy.get('[data-cy="btn-login-google"]').click();
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');
};

const buatPresensiEventBelumValidasi = () => {
  const suffix = Date.now().toString().slice(-6);
  let eventIdEnc = '';

  cy.visit('/event/list?cy_mock=1');
  cy.get('[data-cy="card-event-first"]')
    .should('have.attr', 'href')
    .then((href) => {
      const parts = String(href).split('/').filter(Boolean);
      eventIdEnc = parts[parts.length - 1];
      expect(eventIdEnc).to.not.equal('');
    });

  cy.get('[data-cy="card-event-first"]').click();
  cy.get('[data-cy="btn-identitas-non-civitas"]').click();
  cy.get('[data-cy="input-nama"]').type(`Tamu Validasi ${suffix}`);
  cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]').check({ force: true });
  cy.get('[data-cy="input-nomor_telepon"]').type(`081277${suffix}`);
  cy.get('[data-cy="input-email"]').type(`bbt7.${suffix}@example.com`);
  cy.get('[data-cy="input-institusi"]').type('PT Validasi Event');
  cy.get('[data-cy="input-jabatan"]').type('Staf Operasional');
  cy.get('[data-cy="input-jumlah_rombongan"]').clear().type('2');
  pilihOpsiPertama('[data-cy="select-transportasi"]');
  cy.get('[data-cy="btn-submit-presensi-event"]').click();
  cy.location('pathname').should('include', '/sukses/');

  return cy.then(() => eventIdEnc);
};

describe('BBT-7 Validasi Presensi Tamu Event', () => {
  it('staf memvalidasi kunjungan event berstatus belum validasi hingga status berubah tervalidasi', () => {
    // Arrange (Kunjungi URL)
    buatPresensiEventBelumValidasi().then((eventIdEnc) => {
      loginSebagaiStaf();
      cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
      cy.get('[data-cy="table-event-validasi"]').should('exist');

      // Act (Isi form/klik)
      cy.window().then((win) => {
        cy.stub(win.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.intercept('POST', '**/app/kunjungan/validate/*').as('validateSingleKunjunganEvent');

      cy.get('[data-cy^="btn-action-detail-"]').first().then(($btn) => {
        const kunjunganIdEnc = $btn.attr('data-id') || '';
        expect(kunjunganIdEnc).to.not.equal('');
        cy.wrap(kunjunganIdEnc).as('kunjunganIdEnc');
        cy.wrap($btn).click();
      });

      cy.get('[data-cy="modal-detail-kunjungan-event"]').should('exist');
      cy.get('[data-cy="btn-validate-single-kunjungan-event"]').click();

      // Assert (Verifikasi UI)
      cy.wait('@validateSingleKunjunganEvent').then((interception) => {
        expect(interception.response?.statusCode).to.equal(200);
        expect(interception.response?.body?.status).to.equal(true);
      });

      cy.get('@kunjunganIdEnc').then((id) => {
        cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
        cy.get('[data-cy="table-event-validasi"]').should('exist');
        cy.get(`[data-cy="btn-action-detail-${id}"]`, { timeout: 10000 }).click({ force: true });
        cy.get('[data-cy="badge-status-validasi-detail"]').should('contain.text', 'Tervalidasi');
      });
    });
  });

  it('staf menolak presensi event berstatus belum validasi sehingga data tidak muncul lagi di daftar', () => {
    // Arrange (Kunjungi URL)
    buatPresensiEventBelumValidasi().then((eventIdEnc) => {
      loginSebagaiStaf();
      cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
      cy.get('[data-cy="table-event-validasi"]').should('exist');

      // Act (Isi form/klik)
      cy.window().then((win) => {
        cy.stub(win.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.intercept('POST', '**/app/kunjungan/reject/*').as('rejectSingleKunjunganEvent');
      cy.intercept('POST', `**/app/event/data/validasi-kunjungan-list/${eventIdEnc}*`).as('reloadListAfterReject');

      cy.get('[data-cy^="btn-action-detail-"]').first().then(($btn) => {
        const kunjunganIdEnc = $btn.attr('data-id') || '';
        expect(kunjunganIdEnc).to.not.equal('');
        cy.wrap(kunjunganIdEnc).as('kunjunganIdEncReject');
        cy.wrap($btn).click();
      });

      cy.get('[data-cy="modal-detail-kunjungan-event"]').should('exist');
      cy.get('[data-cy="btn-reject-single-kunjungan-event"]').click();

      // Assert (Verifikasi UI)
      cy.wait('@rejectSingleKunjunganEvent').then((interception) => {
        expect(interception.response?.statusCode).to.equal(200);
        expect(interception.response?.body?.status).to.equal(true);
      });

      cy.get('@kunjunganIdEncReject').then((id) => {
        cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
        cy.wait('@reloadListAfterReject');
        cy.get(`[data-cy="btn-action-detail-${id}"]`, { timeout: 10000 }).should('not.exist');
      });
    });
  });

  it('staf melakukan bulk validate pada presensi event terpilih sehingga status menjadi tervalidasi', () => {
    // Arrange (Kunjungi URL)
    buatPresensiEventBelumValidasi().then((eventIdEnc) => {
      loginSebagaiStaf();
      cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
      cy.get('[data-cy="table-event-validasi"]').should('exist');

      // Act (Isi form/klik)
      cy.window().then((win) => {
        cy.stub(win.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.intercept('POST', '**/app/kunjungan/bulk-validasi').as('bulkValidasiEvent');

      cy.get('[data-cy^="checkbox-row-validasi-event-"]').first().as('rowCheckboxBulkValidate');
      cy.get('@rowCheckboxBulkValidate').then(($checkbox) => {
        const kunjunganIdEnc = $checkbox.attr('data-id') || '';
        expect(kunjunganIdEnc).to.not.equal('');
        cy.wrap(kunjunganIdEnc).as('kunjunganIdEncBulkValidate');
      });

      cy.get('@rowCheckboxBulkValidate').check({ force: true });
      cy.get('[data-cy="btn-bulk-validate-event"]').should('not.be.disabled').click();

      // Assert (Verifikasi UI)
      cy.wait('@bulkValidasiEvent').then((interception) => {
        expect(interception.response?.statusCode).to.equal(200);
        expect(interception.response?.body?.status).to.equal(true);
        const reqBody = interception.request?.body;
        const action =
          typeof reqBody === 'string'
            ? new URLSearchParams(reqBody).get('action')
            : reqBody?.action;
        expect(action).to.equal('validate');
      });

      cy.get('@kunjunganIdEncBulkValidate').then((id) => {
        cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
        cy.get(`[data-cy="btn-action-detail-${id}"]`, { timeout: 10000 }).click({ force: true });
        cy.get('[data-cy="badge-status-validasi-detail"]').should('contain.text', 'Tervalidasi');
      });
    });
  });

  it('staf melakukan bulk reject pada presensi event terpilih sehingga data hilang dari daftar', () => {
    // Arrange (Kunjungi URL)
    buatPresensiEventBelumValidasi().then((eventIdEnc) => {
      loginSebagaiStaf();
      cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
      cy.get('[data-cy="table-event-validasi"]').should('exist');

      // Act (Isi form/klik)
      cy.window().then((win) => {
        cy.stub(win.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.intercept('POST', '**/app/kunjungan/bulk-validasi').as('bulkRejectEvent');

      cy.get('[data-cy^="checkbox-row-validasi-event-"]').first().as('rowCheckboxBulkReject');
      cy.get('@rowCheckboxBulkReject').then(($checkbox) => {
        const kunjunganIdEnc = $checkbox.attr('data-id') || '';
        expect(kunjunganIdEnc).to.not.equal('');
        cy.wrap(kunjunganIdEnc).as('kunjunganIdEncBulkReject');
      });

      cy.get('@rowCheckboxBulkReject').check({ force: true });
      cy.get('[data-cy="btn-bulk-reject-event"]').should('not.be.disabled').click();

      // Assert (Verifikasi UI)
      cy.wait('@bulkRejectEvent').then((interception) => {
        expect(interception.response?.statusCode).to.equal(200);
        expect(interception.response?.body?.status).to.equal(true);
        const reqBody = interception.request?.body;
        const action =
          typeof reqBody === 'string'
            ? new URLSearchParams(reqBody).get('action')
            : reqBody?.action;
        expect(action).to.equal('reject');
      });

      cy.get('@kunjunganIdEncBulkReject').then((id) => {
        cy.visit(`/app/event/validasi-kunjungan/${eventIdEnc}`);
        cy.get(`[data-cy="btn-action-detail-${id}"]`, { timeout: 10000 }).should('not.exist');
      });
    });
  });
});
