const pilihOpsiPertama = (selector) => {
  cy.get(selector).find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(undefined);
    cy.get(selector).select(opsiValid.value);
  });
};

const isiPresensiNonEventSampaiSukses = (suffix) => {
  cy.visit('/non-event/tujuan');
  cy.get('[data-cy="btn-tujuan-lainnya"]').click();
  cy.get('[data-cy="input-nama"]').type(`Tamu Checkout ${suffix}`);
  cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]').check({ force: true });
  cy.get('[data-cy="input-nomor_telepon"]').type(`081255${suffix}`);
  cy.get('[data-cy="input-email"]').type(`bbt4.${suffix}@example.com`);
  cy.get('[data-cy="input-pihak_dituju"]').type('Petugas Informasi');
  cy.get('[data-cy="textarea-keperluan"]').type('Konfirmasi checkout kunjungan.');
  cy.get('[data-cy="input-estimasi_durasi"]').clear().type('1');
  pilihOpsiPertama('[data-cy="select-transportasi"]');
  cy.get('[data-cy="btn-submit-presensi"]').click();
  cy.url().should('include', '/sukses/');
  cy.get('[data-cy="text-registrasi-berhasil"]').should('be.visible');
};

describe('BBT-4 Konfirmasi Checkout', () => {
  it('tamu mengakses tautan checkout valid lalu konfirmasi dan diarahkan ke halaman feedback', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    isiPresensiNonEventSampaiSukses(suffix);

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-checkout-sekarang"]').click();

    cy.url().should('include', '/checkout/');
    cy.get('[data-cy="card-checkout-konfirmasi"]').should('be.visible');
    cy.get('[data-cy="btn-konfirmasi-checkout"]').click();

    // Assert (Verifikasi UI)
    cy.url().should('include', '/feedback/');
    cy.get('[data-cy="form-feedback"]').should('be.visible');
  });

  it('tautan checkout yang sudah dipakai tidak menampilkan form checkout lagi', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    let checkoutUrl = '';
    isiPresensiNonEventSampaiSukses(suffix);
    cy.get('[data-cy="btn-checkout-sekarang"]')
      .should('have.attr', 'href')
      .then((href) => {
        checkoutUrl = String(href);
      });

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-checkout-sekarang"]').click();
    cy.get('[data-cy="btn-konfirmasi-checkout"]').click();
    cy.url().should('include', '/feedback/');
    cy.visit(checkoutUrl);

    // Assert (Verifikasi UI)
    cy.location('pathname').then((pathname) => {
      const validRedirect = pathname.includes('/feedback/') || pathname === '/';
      expect(validRedirect, `redirect checkout ulang: ${pathname}`).to.equal(true);
    });
    cy.get('[data-cy="form-checkout"]').should('not.exist');
  });
});
