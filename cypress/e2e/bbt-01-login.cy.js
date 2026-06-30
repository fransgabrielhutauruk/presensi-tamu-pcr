describe('BBT-1 Login', () => {
  it('login sukses Google akun kampus satu role masuk ke dashboard', () => {
    // Arrange (Kunjungi URL)
    cy.visit('/login');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-login-google"]')
      .should('be.visible')
      .invoke('attr', 'href', '/auth/google?cy_scenario=single-role');

    cy.get('[data-cy="btn-login-google"]').click();

    // Assert (Verifikasi UI)
    cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');
    cy.get('[data-cy="menu-user-toggle"]', { timeout: 10000 }).should('be.visible');
  });

  it('login sukses Google akun kampus multi role lalu memilih role', () => {
    // Arrange (Kunjungi URL)
    cy.visit('/login');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-login-google"]')
      .should('be.visible')
      .invoke('attr', 'href', '/auth/google?cy_scenario=multi-role');
    cy.get('[data-cy="btn-login-google"]').click();
    cy.url().should('include', '/app/event');

    cy.get('[data-cy="menu-user-toggle"]').click();
    cy.get('[data-cy="btn-switch-role-admin"]').click();
    cy.url().should('include', '/app/event');
    cy.get('[data-cy="menu-user-toggle"]').click();
    cy.get('[data-cy="badge-active-role"]').should('contain', 'Admin');

    // Assert (Verifikasi UI)
    cy.get('[data-cy="menu-user-toggle"]').should('be.visible');
  });

  it('login gagal saat akun non-kampus dipilih', () => {
    // Arrange (Kunjungi URL)
    cy.visit('/login');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-login-google"]')
      .should('be.visible')
      .invoke('attr', 'href', '/auth/google?cy_scenario=non-campus');
    cy.get('[data-cy="btn-login-google"]').should('be.visible').click();

    // Assert (Verifikasi UI)
    cy.url().should('include', '/login');
    cy.get('.swal2-popup').should('be.visible');
    cy.get('.swal2-html-container')
      .should('be.visible')
      .and('contain', 'Akses ditolak. Gunakan email @pcr.ac.id atau gunakan email yang sudah didaftarkan oleh Admin.');
  });
});
