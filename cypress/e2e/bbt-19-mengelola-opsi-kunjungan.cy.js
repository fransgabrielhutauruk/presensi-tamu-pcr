const loginDenganSkenario = (scenario, expectedRole) => {
  cy.clearCookies();
  cy.clearLocalStorage();
  cy.visit(`/auth/google?cy_scenario=${scenario}`);
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/event');

  if (expectedRole) {
    cy.get('[data-cy="menu-user-toggle"]').click();
    cy.get('[data-cy="badge-active-role"]').should('contain', expectedRole);
    cy.get('[data-cy="menu-user-toggle"]').click();
  }
};

const loginSebagaiAdmin = () => {
  loginDenganSkenario('multi-role', 'Staf');
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="btn-switch-role-admin"]').click();
  cy.get('[data-cy="menu-user-toggle"]').click();
  cy.get('[data-cy="badge-active-role"]').should('contain', 'Admin');
};

const bukaHalamanKelolaOpsiKunjungan = () => {
  cy.intercept('POST', '**/app/kunjungan/data/list*').as('listKunjungan');
  cy.intercept('POST', '**/app/kunjungan/data/opsi-list*').as('listOpsiKunjungan');
  cy.visit('/app/kunjungan');
  cy.wait('@listKunjungan').its('response.statusCode').should('eq', 200);
  cy.get('[data-cy="tab-kunjungan-opsi"]').click();
  cy.location('pathname', { timeout: 10000 }).should('eq', '/app/kunjungan/opsi');
  cy.wait('@listOpsiKunjungan').its('response.statusCode').should('eq', 200);
  cy.get('[data-cy="table-opsi-kunjungan-list"]').should('be.visible');
};

const bukaModalEditPihakDituju = () => {
  cy.contains('[data-cy="table-opsi-kunjungan-list"] tbody tr td', /^pihak_dituju$/, { timeout: 15000 })
    .should('be.visible')
    .parent()
    .within(() => {
      cy.get('[data-cy^="btn-action-edit-"]').first().click({ force: true });
    });
  cy.get('[data-cy="modal-opsi-kunjungan-form"]').should('be.visible');
};

const tambahItemOpsi = (nilaiId, nilaiEn) => {
  cy.get('[data-cy="btn-add-option-item"]').click({ force: true });
  cy.get('[data-cy^="option-item-row-"]').its('length').then((totalRows) => {
    const newIndex = totalRows - 1;
    cy.get(`[data-cy="input-option-id-${newIndex}"]`).clear({ force: true }).type(nilaiId, { force: true });
    cy.get(`[data-cy="input-option-en-${newIndex}"]`).clear({ force: true }).type(nilaiEn, { force: true });
  });
};

const simpanPerubahanOpsi = (aliasName) => {
  cy.intercept('POST', '**/app/kunjungan/update/opsi*').as(aliasName);
  cy.get('[data-cy="btn-save-opsi-kunjungan"]').click({ force: true });
  cy.wait(`@${aliasName}`).then((updateInterception) => {
    expect(updateInterception.response?.statusCode).to.equal(200);
    expect(updateInterception.response?.body?.status).to.equal(true);
  });
};

const cariIndexItemOpsi = (nilaiId) => {
  return cy.get('[data-cy^="input-option-id-"]').then(($inputs) => {
    const matchedInput = [...$inputs].find((input) => String(input.value ?? '').trim() === nilaiId);
    expect(matchedInput, `item opsi '${nilaiId}' ditemukan pada modal`).to.not.equal(undefined);
    const dataCy = String(matchedInput.getAttribute('data-cy') ?? '');
    const indexText = dataCy.replace('input-option-id-', '');
    const indexNumber = Number(indexText);
    expect(Number.isNaN(indexNumber), 'index item opsi valid').to.equal(false);
    return indexNumber;
  });
};

describe('BBT-19 Mengelola Opsi Kunjungan', () => {
  it('admin membuka kelola opsi kunjungan lalu menambah opsi baru pada pihak dituju dan opsi muncul di form presensi tamu', () => {
    const suffix = Date.now().toString().slice(-6);
    const opsiBaruId = `Pihak Dituju BBT19 ${suffix}`;
    const opsiBaruEn = `Target Visit BBT19 ${suffix}`;

    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    bukaHalamanKelolaOpsiKunjungan();

    // Act (Isi form/klik)
    bukaModalEditPihakDituju();
    tambahItemOpsi(opsiBaruId, opsiBaruEn);
    simpanPerubahanOpsi('updateOpsiTambah');

    // Assert (Verifikasi UI)
    cy.visit('/non-event/presensi?tujuan=instansi');
    cy.get('[data-cy="select-pihak_dituju"]')
      .should('be.visible')
      .should('contain', opsiBaruId);
  });

  it('admin mengubah nilai salah satu item opsi kunjungan dan perubahan muncul di form presensi tamu', () => {
    const suffix = Date.now().toString().slice(-6);
    const opsiAwalId = `Pihak Edit Awal BBT19 ${suffix}`;
    const opsiAwalEn = `Target Edit Init BBT19 ${suffix}`;
    const opsiBaruId = `Pihak Edit Baru BBT19 ${suffix}`;
    const opsiBaruEn = `Target Edit New BBT19 ${suffix}`;

    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    bukaHalamanKelolaOpsiKunjungan();

    // Act (Isi form/klik)
    bukaModalEditPihakDituju();
    tambahItemOpsi(opsiAwalId, opsiAwalEn);
    simpanPerubahanOpsi('updateOpsiSeedEdit');

    bukaModalEditPihakDituju();
    cariIndexItemOpsi(opsiAwalId).then((indexItem) => {
      cy.get(`[data-cy="input-option-id-${indexItem}"]`).clear({ force: true }).type(opsiBaruId, { force: true });
      cy.get(`[data-cy="input-option-en-${indexItem}"]`).clear({ force: true }).type(opsiBaruEn, { force: true });
    });
    simpanPerubahanOpsi('updateOpsiEdit');

    // Assert (Verifikasi UI)
    cy.visit('/non-event/presensi?tujuan=instansi');
    cy.get('[data-cy="select-pihak_dituju"]')
      .should('be.visible')
      .should('contain', opsiBaruId)
      .and('not.contain', opsiAwalId);
  });

  it('admin menghapus salah satu item opsi kunjungan dan item tidak muncul lagi di form presensi tamu', () => {
    const suffix = Date.now().toString().slice(-6);
    const opsiHapusId = `Pihak Hapus BBT19 ${suffix}`;
    const opsiHapusEn = `Target Delete BBT19 ${suffix}`;

    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    bukaHalamanKelolaOpsiKunjungan();

    // Act (Isi form/klik)
    bukaModalEditPihakDituju();
    tambahItemOpsi(opsiHapusId, opsiHapusEn);
    simpanPerubahanOpsi('updateOpsiSeedDelete');

    bukaModalEditPihakDituju();
    cariIndexItemOpsi(opsiHapusId).then((indexItem) => {
      cy.get(`[data-cy="btn-remove-option-item-${indexItem}"]`).click({ force: true });
    });
    simpanPerubahanOpsi('updateOpsiDelete');

    // Assert (Verifikasi UI)
    cy.visit('/non-event/presensi?tujuan=instansi');
    cy.get('[data-cy="select-pihak_dituju"]')
      .should('be.visible')
      .should('not.contain', opsiHapusId);
  });
});
