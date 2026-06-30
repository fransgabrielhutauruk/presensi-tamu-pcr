const pilihOpsiPertama = (selector) => {
  cy.get(selector).find('option').then(($options) => {
    const opsiValid = [...$options].find((option) => option.value && option.value.trim() !== '');
    expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(undefined);
    cy.get(selector).select(opsiValid.value);
  });
};

const assertFieldFrontendError = (selector) => {
  cy.get(selector).then(($el) => {
    expect($el[0].checkValidity()).to.equal(false);
    expect($el[0].validationMessage).to.not.equal('');
  });
};

const assertSemuaFieldMandatoryError = (selectors) => {
  selectors.forEach((selector) => {
    assertFieldFrontendError(selector);
  });
};

const assertRedirectSukses = () => {
  cy.location('pathname', { timeout: 15000 }).should('include', '/sukses/');
  cy.get('[data-cy="text-registrasi-berhasil"]', { timeout: 15000 }).should('be.visible');
};

const bukaFormCivitas = () => {
  cy.visit('/event/list?cy_mock=1');
  cy.get('[data-cy="card-event-first"]').click();
  cy.get('[data-cy="btn-identitas-civitas"]').click();
};

const getInputNimNipCivitas = () =>
  cy.get('[data-cy="form-presensi-event-civitas"]')
    .find('[data-cy="input-nim_nip"]')
    .should('have.length', 1)
    .first();

const pastikanInputNimNipSiap = () => {
  cy.get('body').then(($body) => {
    const $nimInput = $body.find('[data-cy="form-presensi-event-civitas"] [data-cy="input-nim_nip"]');
    const inputTerkunci = $nimInput.prop('disabled') || $nimInput.prop('readonly');

    if (inputTerkunci && $body.find('[data-cy="btn-change-identifier-civitas"]').length > 0) {
      cy.get('[data-cy="btn-change-identifier-civitas"]').click();
    }
  });

  getInputNimNipCivitas().should('be.visible').and('not.be.disabled');
  getInputNimNipCivitas().should(($el) => {
    expect($el.prop('readonly')).to.equal(false);
  });
  getInputNimNipCivitas().clear();
};

const isiNimNipCivitas = (value) => {
  getInputNimNipCivitas().should('be.enabled');
  getInputNimNipCivitas().should(($el) => {
    expect($el.prop('readonly')).to.equal(false);
  });
  getInputNimNipCivitas().clear().type(value);
};

describe('BBT-3 Pengisian Presensi Event', () => {
  it('tamu mengisi semua field wajib presensi event dengan data valid', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-4);
    cy.visit('/event/list?cy_mock=1');

    // Act (Isi form/klik)
    cy.get('[data-cy="card-event-first"]').click();
    cy.get('[data-cy="btn-identitas-non-civitas"]').click();

    cy.get('[data-cy="input-nama"]').type(`Siti Aminah ${suffix}`);
    cy.get('[data-cy="radio-jenis_kelamin-perempuan"]').check({ force: true });
    cy.get('[data-cy="input-nomor_telepon"]').type(`08129876${suffix}`);
    cy.get('[data-cy="input-email"]').type(`siti.bbt3.${suffix}@example.com`);
    cy.get('[data-cy="input-instansi"]').type('PT Inovasi Nusantara');
    pilihOpsiPertama('[data-cy="select-peran"]');
    pilihOpsiPertama('[data-cy="select-transportasi"]');
    cy.get('[data-cy="btn-submit-presensi-event"]').click();

    // Assert (Verifikasi UI)
    assertRedirectSukses();
  });

  it('civitas valid: data diambil dari tabel civitas lalu submit sukses', () => {
    // Arrange (Kunjungi URL)
    cy.intercept('POST', '**/event/check-civitas').as('checkCivitasFromTable');
    bukaFormCivitas();
    pastikanInputNimNipSiap();

    // Act (Isi form/klik)
    isiNimNipCivitas('2312012345');
    cy.get('[data-cy="btn-lookup-civitas"]').click();
    cy.wait('@checkCivitasFromTable').then((interception) => {
      expect(interception.response?.statusCode).to.equal(200);
      expect(interception.response?.body?.status).to.equal(true);
      expect(interception.response?.body?.source).to.equal('civitas');
    });
    cy.get('[data-cy="step-2-civitas"]').should('be.visible');
    cy.get('[data-cy="input-nama"]').should('have.value', 'Andi Pratama');
    cy.get('[data-cy="input-email"]').should('have.value', 'andi@pcr.ac.id');
    pilihOpsiPertama('[data-cy="select-peran"]');
    cy.get('[data-cy="btn-submit-presensi-event-civitas"]').click();

    // Assert (Verifikasi UI)
    assertRedirectSukses();
  });

  it('civitas valid: jika tidak ada di civitas maka NIP diambil dari tabel dm_pegawai', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-4);
    cy.intercept('POST', '**/event/check-civitas').as('checkCivitasFromNip');
    cy.intercept('POST', '**/event/fetch-external-data').as('fetchExternalFromPegawai');
    bukaFormCivitas();
    pastikanInputNimNipSiap();

    // Act (Isi form/klik)
    isiNimNipCivitas('123456');
    cy.get('[data-cy="btn-lookup-civitas"]').click();
    cy.wait('@checkCivitasFromNip').then((interception) => {
      expect(interception.response?.body?.source).to.equal('not_found');
    });
    cy.wait('@fetchExternalFromPegawai').then((interception) => {
      expect(interception.response?.statusCode).to.equal(200);
      expect(interception.response?.body?.source).to.equal('dm_pegawai');
    });
    cy.get('[data-cy="step-2-civitas"]').should('be.visible');
    cy.get('[data-cy="input-nama"]').should('have.value', 'Bambang Setiawan');
    cy.get('[data-cy="input-email"]').should('have.value', 'bambang@pcr.ac.id');
    cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]').check({ force: true });
    cy.get('[data-cy="input-nomor_telepon"]').type(`08123333${suffix}`);
    pilihOpsiPertama('[data-cy="select-peran"]');
    cy.get('[data-cy="btn-submit-presensi-event-civitas"]').click();

    // Assert (Verifikasi UI)
    assertRedirectSukses();
  });

  it('civitas valid: jika nim tidak ada di civitas maka diambil dari API mahasiswa', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-4);
    cy.intercept('POST', '**/event/check-civitas').as('checkCivitasFromNim');
    cy.intercept('POST', '**/event/fetch-external-data').as('fetchExternalFromApiMahasiswa');
    bukaFormCivitas();
    pastikanInputNimNipSiap();

    // Act (Isi form/klik)
    isiNimNipCivitas('2312098765');
    cy.get('[data-cy="btn-lookup-civitas"]').click();
    cy.wait('@checkCivitasFromNim').then((interception) => {
      expect(interception.response?.body?.source).to.equal('not_found');
    });
    cy.wait('@fetchExternalFromApiMahasiswa').then((interception) => {
      expect(interception.response?.statusCode).to.equal(200);
      expect(interception.response?.body?.source).to.equal('api_mahasiswa');
    });
    cy.get('[data-cy="step-2-civitas"]').should('be.visible');
    cy.get('[data-cy="input-nama"]').should('have.value', 'Sri Wahyuni');
    cy.get('[data-cy="input-email"]').should('have.value', 'sri@mahasiswa.pcr.ac.id');
    cy.get('[data-cy="radio-jenis_kelamin-perempuan"]').check({ force: true });
    cy.get('[data-cy="input-nomor_telepon"]').type(`08125555${suffix}`);
    pilihOpsiPertama('[data-cy="select-peran"]');
    cy.get('[data-cy="btn-submit-presensi-event-civitas"]').click();

    // Assert (Verifikasi UI)
    assertRedirectSukses();
  });

  it('non-civitas gagal mengirim form saat semua field mandatory dikosongkan', () => {
    // Arrange (Kunjungi URL)
    cy.visit('/event/list?cy_mock=1');
    cy.get('[data-cy="card-event-first"]').click();
    cy.get('[data-cy="btn-identitas-non-civitas"]').click();

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-submit-presensi-event"]').click();

    // Assert (Verifikasi UI)
    cy.url().should('include', '/event/presensi/');
    cy.url().should('not.include', '/sukses/');
    assertSemuaFieldMandatoryError([
      '[data-cy="input-nama"]',
      '[data-cy="radio-jenis_kelamin-laki-laki"]',
      '[data-cy="input-nomor_telepon"]',
      '[data-cy="input-email"]',
      '[data-cy="input-instansi"]',
      '[data-cy="select-peran"]',
      '[data-cy="select-transportasi"]',
    ]);
  });

  it('civitas gagal mengirim form saat semua field mandatory step-2 dikosongkan', () => {
    // Arrange (Kunjungi URL)
    cy.intercept('POST', '**/event/check-civitas', {
      statusCode: 200,
      body: {
        status: false,
        source: 'not_found',
        identifier_type: 'nim',
        autofilled_fields: [],
        message: 'Data tidak ditemukan di database lokal',
      },
    }).as('checkCivitas');
    cy.intercept('POST', '**/event/fetch-external-data', {
      statusCode: 404,
      body: {
        status: false,
        source: 'not_found',
        identifier_type: 'nim',
        autofilled_fields: [],
        message: 'Data mahasiswa dengan NIM tersebut tidak ditemukan',
      },
    }).as('fetchExternal');
    bukaFormCivitas();
    pastikanInputNimNipSiap();

    // Act (Isi form/klik)
    assertFieldFrontendError('[data-cy="input-nim_nip"]');

    isiNimNipCivitas('2312000000');
    cy.get('[data-cy="btn-lookup-civitas"]').click();
    cy.wait('@checkCivitas');
    cy.wait('@fetchExternal');
    cy.get('[data-cy="step-2-civitas"]').should('be.visible');

    cy.get('[data-cy="btn-submit-presensi-event-civitas"]').click();

    // Assert (Verifikasi UI)
    cy.url().should('include', '/event/presensi-civitas/');
    cy.url().should('not.include', '/sukses/');
    assertSemuaFieldMandatoryError([
      '[data-cy="input-nama"]',
      '[data-cy="radio-jenis_kelamin-laki-laki"]',
      '[data-cy="input-nomor_telepon"]',
      '[data-cy="input-email"]',
      '[data-cy="select-peran"]',
    ]);
  });
});
