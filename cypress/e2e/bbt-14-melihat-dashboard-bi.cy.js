const loginDenganSkenario = (scenario, expectedRole) => {
  cy.visit('/login');
  cy.get('[data-cy="btn-login-google"]')
    .should('be.visible')
    .invoke('attr', 'href', `/auth/google?cy_scenario=${scenario}`);
  cy.get('[data-cy="btn-login-google"]').click();
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="badge-active-role"]').should('contain', expectedRole);
};

describe('BBT-14 Melihat Dashboard BI', () => {
  it('eksekutif mengakses halaman dashboard BI dan melihat visualisasi Power BI', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('eksekutif-role', 'Eksekutif');
    cy.visit('/app/dashboard');

    // Act (Isi form/klik)
    cy.get('[data-cy="panel-dashboard-bi"]').should('be.visible');

    // Assert (Verifikasi UI)
    cy.get('[data-cy="iframe-dashboard-powerbi"]')
      .should('be.visible')
      .and('have.attr', 'src')
      .and('include', 'https://app.powerbi.com/view');
  });

  it('security dapat mengakses dashboard tetapi tidak menampilkan embed Power BI', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('security-role', 'Security');

    // Act (Isi form/klik)
    cy.visit('/app/dashboard');

    // Assert (Verifikasi UI)
    cy.get('[data-cy="panel-dashboard-bi"]').should('not.exist');
    cy.get('[data-cy="iframe-dashboard-powerbi"]').should('not.exist');
  });

  it('pengguna role mahasiswa ditolak saat mengakses dashboard BI', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('mahasiswa-role', 'Mahasiswa');

    // Act (Isi form/klik)
    cy.request({
      method: 'GET',
      url: '/app/dashboard',
      failOnStatusCode: false,
    }).as('requestDashboardMahasiswa');

    // Assert (Verifikasi UI)
    cy.get('@requestDashboardMahasiswa').then((response) => {
      expect(response.status).to.equal(403);
    });
  });

  it('pengguna tamu diarahkan ke halaman login saat mengakses dashboard BI', () => {
    // Arrange (Kunjungi URL)
    cy.clearCookies();
    cy.clearLocalStorage();

    // Act (Isi form/klik)
    cy.visit('/app/dashboard');

    // Assert (Verifikasi UI)
    cy.location('pathname', { timeout: 10000 }).should('eq', '/login');
    cy.get('[data-cy="btn-login-google"]').should('be.visible');
  });
});
