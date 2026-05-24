const pilihOpsiPertama = (selector) => {
  cy.get(selector).find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(undefined);
    cy.get(selector).select(opsiValid.value);
  });
};

const bukaHalamanFeedback = (suffix) => {
  cy.visit('/non-event/tujuan');
  cy.get('[data-cy="btn-tujuan-lainnya"]').click();
  cy.get('[data-cy="input-nama"]').type(`Tamu Feedback ${suffix}`);
  cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]').check({ force: true });
  cy.get('[data-cy="input-nomor_telepon"]').type(`081266${suffix}`);
  cy.get('[data-cy="input-email"]').type(`bbt5.${suffix}@example.com`);
  cy.get('[data-cy="input-pihak_dituju"]').type('Petugas Informasi');
  cy.get('[data-cy="textarea-keperluan"]').type('Pengujian pengisian feedback.');
  cy.get('[data-cy="input-jumlah_rombongan"]').clear().type('1');
  cy.get('[data-cy="input-estimasi_durasi"]').clear().type('1');
  pilihOpsiPertama('[data-cy="select-transportasi"]');
  cy.get('[data-cy="btn-submit-presensi"]').click();
  cy.get('[data-cy="btn-checkout-sekarang"]').click();
  cy.get('[data-cy="btn-konfirmasi-checkout"]').click();
  cy.url().should('include', '/feedback/');
  cy.get('[data-cy="form-feedback"]').should('be.visible');
};

describe('BBT-5 Pengisian Feedback', () => {
  it('tamu mengisi rating dan komentar lalu submit sukses ke halaman penutup', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    bukaHalamanFeedback(suffix);

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-rating-5"]').click();
    cy.get('[data-cy="textarea-komentar"]').type('Pelayanan sangat baik dan proses presensi cepat.');
    cy.get('[data-cy="btn-submit-feedback"]').click();

    // Assert (Verifikasi UI)
    cy.location('pathname').should('equal', '/');
    cy.get('[data-cy="flash-success-message"]')
      .should('exist')
      .and('contain.text', 'Terima kasih atas penilaian Anda!');
  });

  it('tamu hanya mengisi rating lalu submit tetap sukses ke halaman penutup', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    bukaHalamanFeedback(suffix);

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-rating-4"]').click();
    cy.get('[data-cy="btn-submit-feedback"]').click();

    // Assert (Verifikasi UI)
    cy.location('pathname').should('equal', '/');
    cy.get('[data-cy="flash-success-message"]')
      .should('exist')
      .and('contain.text', 'Terima kasih atas penilaian Anda!');
  });

  it('tamu hanya mengisi komentar tanpa rating maka tampil error rating dan form tidak terkirim', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    bukaHalamanFeedback(suffix);

    // Act (Isi form/klik)
    cy.get('[data-cy="textarea-komentar"]').type('Komentar tanpa rating untuk uji validasi.');
    cy.get('[data-cy="btn-submit-feedback"]').click();

    // Assert (Verifikasi UI)
    cy.url().should('include', '/feedback/');
    cy.get('[data-cy="error-rating"]').should('be.visible');
    cy.get('[data-cy="input-rating"]').should('have.value', '');
  });
});
