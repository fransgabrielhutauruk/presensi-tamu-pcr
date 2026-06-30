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

describe('BBT-10 Mengelola Data Kunjungan', () => {
  it('admin membaca daftar kunjungan, melakukan pencarian, lalu menghapus data kunjungan', () => {
    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/kunjungan/data/list*').as('listKunjungan');
    cy.intercept('POST', '**/app/kunjungan/destroy**').as('destroyKunjungan');
    cy.visit('/app/kunjungan');
    cy.wait('@listKunjungan');

    // Act (Isi form/klik)
    cy.get('[data-cy^="btn-action-delete-"]').its('length').should('be.gte', 1);

    // Trigger DataTable search langsung via API (lebih reliable dari mengetik)
    cy.intercept('POST', '**/app/kunjungan/data/list*').as('listKunjunganSearch');
    cy.get('[data-cy^="input-table-search-"]').first().then(($el) => {
      const tableId = $el.attr('id').replace('customSearch-', '');
      cy.window().then((win) => {
        win.$(`#${tableId}`).DataTable().search('zzzz-bbt10-data-tidak-ada').draw();
      });
    });
    cy.wait('@listKunjunganSearch');
    cy.get('[data-cy^="btn-action-delete-"]').should('not.exist');

    // Reset pencarian
    cy.intercept('POST', '**/app/kunjungan/data/list*').as('listKunjunganReset');
    cy.get('[data-cy^="input-table-search-"]').first().then(($el) => {
      const tableId = $el.attr('id').replace('customSearch-', '');
      cy.window().then((win) => {
        win.$(`#${tableId}`).DataTable().search('').draw();
      });
    });
    cy.wait('@listKunjunganReset');

    cy.get('[data-cy^="btn-action-delete-"]').first().as('targetDeleteButton');
    cy.get('@targetDeleteButton').invoke('attr', 'data-cy').then((deleteDataCy) => {
      const kunjunganIdEnc = String(deleteDataCy).replace('btn-action-delete-', '');
      expect(kunjunganIdEnc).to.not.equal('');

      cy.window().then((windowObject) => {
        cy.stub(windowObject.Swal, 'fire')
          .callsFake(() => Promise.resolve({
            value: true,
            isConfirmed: true
          }))
          .as('swalFire');
      });

      cy.get('@targetDeleteButton').scrollIntoView().click({ force: true });

      cy.wait('@destroyKunjungan').then((destroyInterception) => {
        expect(destroyInterception.response?.statusCode).to.equal(200);
        expect(destroyInterception.response?.body?.status).to.equal(true);
      });

      // Assert (Verifikasi UI)
      cy.get(`[data-cy="btn-action-delete-${kunjunganIdEnc}"]`, { timeout: 10000 }).should('not.exist');
    });
  });

  it('admin membatalkan hapus kunjungan sehingga data tetap ada', () => {
    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/kunjungan/data/list*').as('listKunjunganCancel');
    cy.intercept('POST', '**/app/kunjungan/destroy**').as('destroyKunjunganCancel');
    cy.visit('/app/kunjungan');
    cy.wait('@listKunjunganCancel');

    // Act (Isi form/klik)
    cy.get('[data-cy^="btn-action-delete-"]').first().as('targetDeleteButtonCancel');
    cy.get('@targetDeleteButtonCancel').invoke('attr', 'data-cy').then((deleteDataCy) => {
      const kunjunganIdEnc = String(deleteDataCy).replace('btn-action-delete-', '');
      expect(kunjunganIdEnc).to.not.equal('');

      cy.window().then((windowObject) => {
        cy.stub(windowObject.Swal, 'fire')
          .callsFake(() => Promise.resolve({
            value: false,
            isConfirmed: false
          }))
          .as('swalFireCancel');
      });

      cy.get('@targetDeleteButtonCancel').scrollIntoView().click({ force: true });
      cy.wait(1000);

      // Assert (Verifikasi UI)
      cy.get('@swalFireCancel').should('have.been.called');
      cy.get('@destroyKunjunganCancel.all').then((destroyCalls) => {
        expect(destroyCalls.length).to.equal(0);
      });
      cy.get(`[data-cy="btn-action-delete-${kunjunganIdEnc}"]`).should('exist');
    });
  });
});
