const pilihOpsiPertama = (selector) => {
  cy.get(selector).filter(':visible:enabled').first().find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(undefined);
    cy.get(selector).filter(':visible:enabled').first().select(opsiValid.value, { force: true });
  });
};

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

const buatKunjunganNonEventHariIni = () => {
  const suffix = Date.now().toString().slice(-6);
  const uniqueNama = `Tamu Monitoring ${suffix}`;
  const uniqueEmail = `bbt15.monitoring.${suffix}@example.com`;

  cy.visit('/non-event/tujuan');
  cy.get('[data-cy="btn-tujuan-lainnya"]').click({ force: true });
  cy.location('pathname', { timeout: 10000 }).should('include', '/non-event/presensi');
  cy.get('[data-cy="form-presensi-non-event"]').should('be.visible');

  cy.get('[data-cy="form-presensi-non-event"]').within(() => {
    cy.get('[data-cy="input-nama"]:visible:enabled').first().type(uniqueNama, { force: true });
    cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]:visible:enabled').first().check({ force: true });
    cy.get('[data-cy="input-nomor_telepon"]:visible:enabled').first().type(`081255${suffix}`, { force: true });
    cy.get('[data-cy="input-email"]:visible:enabled').first().type(uniqueEmail, { force: true });
    cy.get('[data-cy="input-pihak_dituju"]:visible:enabled').first().type('Petugas Informasi', { force: true });
    cy.get('[data-cy="textarea-keperluan"]:visible:enabled').first().type('Pengujian monitoring tamu aktif BBT-15', { force: true });
    cy.get('[data-cy="input-jumlah_rombongan"]:visible:enabled').first().clear({ force: true }).type('1', { force: true });
    cy.get('[data-cy="input-estimasi_durasi"]:visible:enabled').first().clear({ force: true }).type('2', { force: true });
  });
  pilihOpsiPertama('[data-cy="select-transportasi"]');
  cy.get('[data-cy="btn-submit-presensi"]').click();
  cy.location('pathname', { timeout: 15000 }).should('include', '/sukses/');
  cy.get('[data-cy="text-registrasi-berhasil"]').should('be.visible');

  return cy.wrap({
    nama: uniqueNama,
    email: uniqueEmail,
  });
};

describe('BBT-15 Monitoring Tamu Aktif', () => {
  it('eksekutif masuk ke halaman monitoring kunjungan dan melihat daftar tamu aktif hari ini', () => {
    // Arrange (Kunjungi URL)
    buatKunjunganNonEventHariIni().then((kunjunganData) => {
      loginDenganSkenario('eksekutif-role', 'Eksekutif');
      cy.intercept('POST', '**/app/kunjungan/data/monitoring-hari-ini*').as('listMonitoringHariIni');
      cy.visit('/app/kunjungan/monitoring');
      cy.wait('@listMonitoringHariIni');

      // Act (Isi form/klik)
      cy.get('[data-cy="table-monitoring-kunjungan"]').should('be.visible');
      cy.get('[data-cy^="input-table-search-"]').first().clear({ force: true }).type(`${kunjunganData.nama}{enter}`, { force: true });

      // Assert (Verifikasi UI)
      cy.contains('[data-cy="table-monitoring-kunjungan"] tbody tr', kunjunganData.nama, { timeout: 10000 })
        .should('be.visible')
        .and('contain', 'Belum Checkout');
    });
  });

  it('pengguna role mahasiswa ditolak saat mengakses halaman monitoring kunjungan', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('mahasiswa-role', 'Mahasiswa');

    // Act (Isi form/klik)
    cy.request({
      method: 'GET',
      url: '/app/kunjungan/monitoring',
      failOnStatusCode: false,
    }).as('requestMonitoringMahasiswa');

    // Assert (Verifikasi UI)
    cy.get('@requestMonitoringMahasiswa').then((response) => {
      expect(response.status).to.equal(403);
    });
  });

  it('pengguna tamu diarahkan ke halaman login saat mengakses monitoring kunjungan', () => {
    // Arrange (Kunjungi URL)
    cy.clearCookies();
    cy.clearLocalStorage();

    // Act (Isi form/klik)
    cy.visit('/app/kunjungan/monitoring');

    // Assert (Verifikasi UI)
    cy.location('pathname', { timeout: 10000 }).should('eq', '/login');
    cy.get('[data-cy="btn-login-google"]').should('be.visible');
  });

  it('eksekutif tidak menemukan data saat mencari kata kunci yang tidak ada di monitoring kunjungan', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('eksekutif-role', 'Eksekutif');
    cy.intercept('POST', '**/app/kunjungan/data/monitoring-hari-ini*').as('listMonitoringNoMatch');
    cy.visit('/app/kunjungan/monitoring');
    cy.wait('@listMonitoringNoMatch');
    const kataKunciTidakAda = `bbt15-monitoring-no-match-${Date.now()}`;

    // Act (Isi form/klik)
    cy.get('[data-cy="table-monitoring-kunjungan"]').should('be.visible');
    cy.get('[data-cy^="input-table-search-"]').first().clear({ force: true }).type(`${kataKunciTidakAda}{enter}`, { force: true });

    // Assert (Verifikasi UI)
    cy.get('[data-cy^="btn-action-detail-"]').should('not.exist');
  });
});
