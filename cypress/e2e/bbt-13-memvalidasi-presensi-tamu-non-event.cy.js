const pilihOpsiPertama = (selector) => {
  cy.get(selector).filter(':visible:enabled').first().find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(undefined);
    cy.get(selector).filter(':visible:enabled').first().select(opsiValid.value, { force: true });
  });
};

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

const bukaHalamanValidasiKunjungan = () => {
  loginSebagaiAdmin();
  cy.intercept('POST', '**/app/kunjungan/data/validasi-list*').as('listValidasiKunjungan');
  cy.visit('/app/kunjungan/validasi');
  cy.wait('@listValidasiKunjungan');
  cy.get('[data-cy="table-validasi-kunjungan"]').should('be.visible');
};

const cariDataKunjunganByEmail = (email) => {
  cy.get('[data-cy^="input-table-search-"]').first().clear({ force: true }).type(`${email}{enter}`, { force: true });
  cy.contains('[data-cy="table-validasi-kunjungan"] tbody tr', email, { timeout: 10000 }).should('be.visible');
};

const pastikanDataKunjunganSudahTidakAda = (email) => {
  cy.get('[data-cy^="input-table-search-"]').first().clear({ force: true }).type(`${email}{enter}`, { force: true });
  cy.get('[data-cy="table-validasi-kunjungan"] tbody', { timeout: 10000 }).should('not.contain', email);
};

const buatPresensiNonEventBelumValidasi = () => {
  const suffix = Date.now().toString().slice(-6);
  const uniqueEmail = `bbt13.non.event.${suffix}@example.com`;

  const getInputAktifDiForm = (dataCy) =>
    cy.get('[data-cy="form-presensi-non-event"]')
      .find(`[data-cy="${dataCy}"]`)
      .filter(':visible:enabled')
      .first()
      .should('exist');

  cy.visit('/non-event/tujuan');
  cy.get('[data-cy="btn-tujuan-lainnya"]').click({ force: true });
  cy.location('pathname', { timeout: 10000 }).should('include', '/non-event/presensi');
  cy.get('[data-cy="form-presensi-non-event"]').should('be.visible');
  cy.get('[data-cy="input-kategori-tujuan"]').should('have.value', 'lainnya');

  getInputAktifDiForm('input-nama').clear({ force: true }).type(`Tamu BBT13 ${suffix}`, { force: true });
  getInputAktifDiForm('radio-jenis_kelamin-laki-laki').check({ force: true });
  getInputAktifDiForm('input-nomor_telepon').clear({ force: true }).type(`081277${suffix}`, { force: true });
  getInputAktifDiForm('input-email').clear({ force: true }).type(uniqueEmail, { force: true });
  getInputAktifDiForm('input-pihak_dituju').clear({ force: true }).type('Petugas Informasi', { force: true });
  getInputAktifDiForm('textarea-keperluan').clear({ force: true }).type('Pengujian validasi kunjungan non-event BBT-13', { force: true });
  getInputAktifDiForm('input-jumlah_rombongan').clear({ force: true }).type('1', { force: true });
  getInputAktifDiForm('input-estimasi_durasi').clear({ force: true }).type('2', { force: true });
  pilihOpsiPertama('[data-cy="select-transportasi"]');
  cy.get('[data-cy="btn-submit-presensi"]').click();
  cy.url().should('include', '/sukses/');
  cy.get('[data-cy="text-registrasi-berhasil"]').should('be.visible');

  return cy.wrap(uniqueEmail);
};

describe('BBT-13 Memvalidasi Presensi Tamu Non-Event', () => {
  it('admin menyetujui presensi non-event hingga data tidak muncul lagi di daftar validasi kunjungan', () => {
    // Arrange (Kunjungi URL)
    buatPresensiNonEventBelumValidasi().then((uniqueEmail) => {
      bukaHalamanValidasiKunjungan();
      cy.intercept('POST', '**/app/kunjungan/validate/*').as('validateSingleKunjungan');

      // Act (Isi form/klik)
      cariDataKunjunganByEmail(uniqueEmail);
      cy.contains('[data-cy="table-validasi-kunjungan"] tbody tr', uniqueEmail).within(() => {
        cy.get('[data-cy^="btn-action-detail-"]').first().click({ force: true });
      });

      cy.get('[data-cy="modal-detail-validasi-kunjungan"]').should('be.visible');
      cy.window().then((windowObject) => {
        cy.stub(windowObject.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.get('[data-cy="btn-validate-single-validasi-kunjungan"]').click({ force: true });

      // Assert (Verifikasi UI)
      cy.wait('@validateSingleKunjungan').then((validateInterception) => {
        expect(validateInterception.response?.statusCode).to.equal(200);
        expect(validateInterception.response?.body?.status).to.equal(true);
      });

      pastikanDataKunjunganSudahTidakAda(uniqueEmail);
    });
  });

  it('admin menolak presensi non-event sehingga data tidak muncul lagi di daftar validasi kunjungan', () => {
    // Arrange (Kunjungi URL)
    buatPresensiNonEventBelumValidasi().then((uniqueEmail) => {
      bukaHalamanValidasiKunjungan();
      cy.intercept('POST', '**/app/kunjungan/reject/*').as('rejectSingleKunjungan');

      // Act (Isi form/klik)
      cariDataKunjunganByEmail(uniqueEmail);
      cy.contains('[data-cy="table-validasi-kunjungan"] tbody tr', uniqueEmail).within(() => {
        cy.get('[data-cy^="btn-action-detail-"]').first().click({ force: true });
      });

      cy.get('[data-cy="modal-detail-validasi-kunjungan"]').should('be.visible');
      cy.window().then((windowObject) => {
        cy.stub(windowObject.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.get('[data-cy="btn-reject-single-validasi-kunjungan"]').click({ force: true });

      // Assert (Verifikasi UI)
      cy.wait('@rejectSingleKunjungan').then((rejectInterception) => {
        expect(rejectInterception.response?.statusCode).to.equal(200);
        expect(rejectInterception.response?.body?.status).to.equal(true);
      });

      pastikanDataKunjunganSudahTidakAda(uniqueEmail);
    });
  });

  it('admin melakukan bulk validate pada presensi non-event terpilih sehingga data hilang dari daftar validasi', () => {
    // Arrange (Kunjungi URL)
    buatPresensiNonEventBelumValidasi().then((uniqueEmail) => {
      bukaHalamanValidasiKunjungan();
      cy.intercept('POST', '**/app/kunjungan/bulk-validasi').as('bulkValidasiKunjungan');

      // Act (Isi form/klik)
      cariDataKunjunganByEmail(uniqueEmail);
      cy.contains('[data-cy="table-validasi-kunjungan"] tbody tr', uniqueEmail).within(() => {
        cy.get('[data-cy^="checkbox-row-validasi-kunjungan-"]').first().check({ force: true });
      });
      cy.window().then((windowObject) => {
        cy.stub(windowObject.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.get('[data-cy="btn-bulk-validate-validasi-kunjungan"]').should('not.be.disabled').click({ force: true });

      // Assert (Verifikasi UI)
      cy.wait('@bulkValidasiKunjungan').then((bulkInterception) => {
        expect(bulkInterception.response?.statusCode).to.equal(200);
        expect(bulkInterception.response?.body?.status).to.equal(true);
        const requestBody = bulkInterception.request?.body;
        const action =
          typeof requestBody === 'string'
            ? new URLSearchParams(requestBody).get('action')
            : requestBody?.action;
        expect(action).to.equal('validate');
      });

      pastikanDataKunjunganSudahTidakAda(uniqueEmail);
    });
  });

  it('admin melakukan bulk reject pada presensi non-event terpilih sehingga data hilang dari daftar validasi', () => {
    // Arrange (Kunjungi URL)
    buatPresensiNonEventBelumValidasi().then((uniqueEmail) => {
      bukaHalamanValidasiKunjungan();
      cy.intercept('POST', '**/app/kunjungan/bulk-validasi').as('bulkRejectKunjungan');

      // Act (Isi form/klik)
      cariDataKunjunganByEmail(uniqueEmail);
      cy.contains('[data-cy="table-validasi-kunjungan"] tbody tr', uniqueEmail).within(() => {
        cy.get('[data-cy^="checkbox-row-validasi-kunjungan-"]').first().check({ force: true });
      });
      cy.window().then((windowObject) => {
        cy.stub(windowObject.Swal, 'fire').callsFake(() => Promise.resolve({ isConfirmed: true }));
      });
      cy.get('[data-cy="btn-bulk-reject-validasi-kunjungan"]').should('not.be.disabled').click({ force: true });

      // Assert (Verifikasi UI)
      cy.wait('@bulkRejectKunjungan').then((bulkInterception) => {
        expect(bulkInterception.response?.statusCode).to.equal(200);
        expect(bulkInterception.response?.body?.status).to.equal(true);
        const requestBody = bulkInterception.request?.body;
        const action =
          typeof requestBody === 'string'
            ? new URLSearchParams(requestBody).get('action')
            : requestBody?.action;
        expect(action).to.equal('reject');
      });

      pastikanDataKunjunganSudahTidakAda(uniqueEmail);
    });
  });
});
