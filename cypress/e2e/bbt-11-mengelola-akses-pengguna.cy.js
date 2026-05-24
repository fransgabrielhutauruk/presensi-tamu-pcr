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

const pilihRoles = (roles) => {
  cy.get('[data-cy="select-user-roles"]')
    .invoke('val', roles)
    .trigger('change', { force: true });

  cy.get('[data-cy="select-user-roles"]').then(($select) => {
    const selected = $select.val() || [];
    expect(selected).to.include.members(roles);
  });
};

const cariUserDiTabel = (keyword) => {
  cy.get('[data-cy^="input-table-search-"]').first()
    .click({ force: true })
    .type('{selectall}{backspace}', { force: true })
    .type(`${keyword}{enter}`, { force: true });
};

const tungguModalUserTertutup = () => {
  cy.get('body').then(($body) => {
    if ($body.find('[data-cy="form-user"]').length > 0) {
      cy.get('[data-cy="form-user"]').should('not.be.visible');
    }
  });
  cy.wait(300);
};

describe('BBT-11 Mengelola Akses Pengguna', () => {
  it('admin memberikan lalu mencabut hak akses pengguna', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const namaUser = `User BBT11 ${suffix}`;
    const emailUser = `user.bbt11.${suffix}@example.com`;

    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/user/data/list*').as('listUser');
    cy.intercept('POST', '**/app/user/store').as('storeUser');
    cy.intercept('POST', '**/app/user/update**').as('updateUser');
    cy.visit('/app/user');
    cy.wait('@listUser');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(namaUser);
    cy.get('[data-cy="input-user-email"]').type(emailUser);
    pilihRoles(['Security']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    cy.wait('@storeUser').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const userIdEnc = storeInterception.response?.body?.data?.id ?? '';
      expect(userIdEnc).to.be.a('string').and.not.equal('');
      cy.wrap(userIdEnc).as('userIdEnc');
    });

    cy.wait('@listUser');
    tungguModalUserTertutup();
    cariUserDiTabel(emailUser);
    cy.contains('[data-cy="table-user-list"] tbody tr', emailUser).should('contain', 'Security');

    cy.get('@userIdEnc').then((userIdEnc) => {
      cy.get(`[data-cy="btn-action-edit-${userIdEnc}"]`)
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').invoke('val').should('not.equal', '');
    cy.get('[data-cy="input-user-email"]').should('have.value', emailUser);
    pilihRoles(['Security', 'Admin']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    cy.wait('@updateUser').then((updateInterception) => {
      expect(updateInterception.response?.statusCode).to.equal(200);
      expect(updateInterception.response?.body?.status).to.equal(true);
    });

    cy.wait('@listUser');
    tungguModalUserTertutup();
    cariUserDiTabel(emailUser);
    cy.contains('[data-cy="table-user-list"] tbody tr', emailUser).should('contain', 'Admin');

    cy.get('@userIdEnc').then((userIdEnc) => {
      cy.get(`[data-cy="btn-action-edit-${userIdEnc}"]`)
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').invoke('val').should('not.equal', '');
    cy.get('[data-cy="input-user-email"]').should('have.value', emailUser);
    pilihRoles(['Security']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    cy.wait('@updateUser').then((updateInterception) => {
      expect(updateInterception.response?.statusCode).to.equal(200);
      expect(updateInterception.response?.body?.status).to.equal(true);
    });

    // Assert (Verifikasi UI)
    cy.wait('@listUser');
    tungguModalUserTertutup();
    cariUserDiTabel(emailUser);
    cy.contains('[data-cy="table-user-list"] tbody tr', emailUser)
      .should('contain', 'Security')
      .and('not.contain', 'Admin');
  });

  it('admin gagal memperbarui akses saat role dikosongkan', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const namaUser = `User BBT11 Invalid Role ${suffix}`;
    const emailUser = `user.bbt11.invalid.role.${suffix}@example.com`;

    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/user/data/list*').as('listUserInvalidRole');
    cy.intercept('POST', '**/app/user/store').as('storeUserInvalidRole');
    cy.intercept('POST', '**/app/user/update**').as('updateUserInvalidRole');
    cy.visit('/app/user');
    cy.wait('@listUserInvalidRole');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(namaUser);
    cy.get('[data-cy="input-user-email"]').type(emailUser);
    pilihRoles(['Security']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    cy.wait('@storeUserInvalidRole').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const userIdEnc = storeInterception.response?.body?.data?.id ?? '';
      expect(userIdEnc).to.be.a('string').and.not.equal('');
      cy.wrap(userIdEnc).as('userIdEncInvalidRole');
    });

    cy.wait('@listUserInvalidRole');
    tungguModalUserTertutup();
    cariUserDiTabel(emailUser);
    cy.get('@userIdEncInvalidRole').then((userIdEnc) => {
      cy.get(`[data-cy="btn-action-edit-${userIdEnc}"]`)
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });

    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').invoke('val').should('not.equal', '');
    cy.get('[data-cy="input-user-email"]').should('have.value', emailUser);
    cy.get('[data-cy="select-user-roles"]').invoke('val', []).trigger('change', { force: true });
    cy.get('[data-cy="btn-simpan-user"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@updateUserInvalidRole').then((updateInterception) => {
      expect(updateInterception.response?.statusCode).to.equal(422);
      expect(Boolean(updateInterception.response?.body?.errors?.roles?.length)).to.equal(true);
    });
    cy.location('pathname').should('eq', '/app/user');
    cy.get('[data-cy="form-user"]').should('exist');
    cy.get('[data-cy="input-user-email"]').invoke('val').should('eq', emailUser);
  });

  it('admin gagal menambah pengguna saat email sudah terdaftar', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const emailUser = `user.bbt11.duplicate.${suffix}@example.com`;

    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/user/data/list*').as('listUserDuplicate');
    cy.intercept('POST', '**/app/user/store').as('storeUserDuplicate');
    cy.visit('/app/user');
    cy.wait('@listUserDuplicate');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(`User BBT11 First ${suffix}`);
    cy.get('[data-cy="input-user-email"]').type(emailUser);
    pilihRoles(['Security']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    cy.wait('@storeUserDuplicate').then((firstStoreInterception) => {
      expect(firstStoreInterception.response?.statusCode).to.equal(200);
      expect(firstStoreInterception.response?.body?.status).to.equal(true);
    });

    tungguModalUserTertutup();
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(`User BBT11 Second ${suffix}`);
    cy.get('[data-cy="input-user-email"]').type(emailUser);
    pilihRoles(['Admin']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@storeUserDuplicate').then((secondStoreInterception) => {
      expect(secondStoreInterception.response?.statusCode).to.equal(422);
      expect(Boolean(secondStoreInterception.response?.body?.errors?.email?.length)).to.equal(true);
    });
    cy.location('pathname').should('eq', '/app/user');
    cy.get('[data-cy="form-user"]').should('exist');
    cy.get('[data-cy="input-user-email"]').invoke('val').should('eq', emailUser);
  });

  it('admin gagal menambah pengguna saat format email tidak valid', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const invalidEmail = `user-bbt11-invalid-email-${suffix}`;

    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/user/data/list*').as('listUserInvalidEmail');
    cy.intercept('POST', '**/app/user/store').as('storeUserInvalidEmail');
    cy.visit('/app/user');
    cy.wait('@listUserInvalidEmail');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(`User BBT11 Invalid Email ${suffix}`);
    cy.get('[data-cy="input-user-email"]').type(invalidEmail);
    pilihRoles(['Security']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@storeUserInvalidEmail').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(422);
      expect(Boolean(storeInterception.response?.body?.errors?.email?.length)).to.equal(true);
    });
    cy.location('pathname').should('eq', '/app/user');
    cy.get('[data-cy="form-user"]').should('exist');
    cy.get('[data-cy="input-user-email"]').invoke('val').should('eq', invalidEmail);
  });

  it('admin gagal menambah pengguna saat role dikosongkan', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const emailUser = `user.bbt11.no.role.${suffix}@example.com`;

    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/user/data/list*').as('listUserNoRole');
    cy.intercept('POST', '**/app/user/store').as('storeUserNoRole');
    cy.visit('/app/user');
    cy.wait('@listUserNoRole');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(`User BBT11 No Role ${suffix}`);
    cy.get('[data-cy="input-user-email"]').type(emailUser);
    cy.get('[data-cy="select-user-roles"]').invoke('val', []).trigger('change', { force: true });
    cy.get('[data-cy="btn-simpan-user"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@storeUserNoRole').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(422);
      expect(Boolean(storeInterception.response?.body?.errors?.roles?.length)).to.equal(true);
    });
    cy.location('pathname').should('eq', '/app/user');
    cy.get('[data-cy="form-user"]').should('exist');
    cy.get('[data-cy="input-user-email"]').invoke('val').should('eq', emailUser);
  });

  it('admin gagal memperbarui akses saat role yang dikirim tidak valid', () => {
    // Arrange (Kunjungi URL)
    const suffix = Date.now().toString().slice(-6);
    const namaUser = `User BBT11 Invalid Value ${suffix}`;
    const emailUser = `user.bbt11.invalid.value.${suffix}@example.com`;

    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/user/data/list*').as('listUserInvalidValue');
    cy.intercept('POST', '**/app/user/store').as('storeUserInvalidValue');
    cy.intercept('POST', '**/app/user/update**').as('updateUserInvalidValue');
    cy.visit('/app/user');
    cy.wait('@listUserInvalidValue');

    // Act (Isi form/klik)
    cy.get('[data-cy="btn-tambah-user"]').click({ force: true });
    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-name"]').type(namaUser);
    cy.get('[data-cy="input-user-email"]').type(emailUser);
    pilihRoles(['Security']);
    cy.get('[data-cy="btn-simpan-user"]').click();

    cy.wait('@storeUserInvalidValue').then((storeInterception) => {
      expect(storeInterception.response?.statusCode).to.equal(200);
      expect(storeInterception.response?.body?.status).to.equal(true);

      const userIdEnc = storeInterception.response?.body?.data?.id ?? '';
      expect(userIdEnc).to.be.a('string').and.not.equal('');
      cy.wrap(userIdEnc).as('userIdEncInvalidValue');
    });

    cy.wait('@listUserInvalidValue');
    tungguModalUserTertutup();
    cariUserDiTabel(emailUser);
    cy.get('@userIdEncInvalidValue').then((userIdEnc) => {
      cy.get(`[data-cy="btn-action-edit-${userIdEnc}"]`)
        .should('exist')
        .scrollIntoView()
        .click({ force: true });
    });

    cy.get('[data-cy="form-user"]').should('be.visible');
    cy.get('[data-cy="input-user-email"]').should('have.value', emailUser);
    cy.get('[data-cy="select-user-roles"]').then(($select) => {
      const hasInvalidOption = [...$select.find('option')].some((option) => option.value === 'SuperAdmin');
      if (!hasInvalidOption) {
        $select.append('<option value="SuperAdmin">SuperAdmin</option>');
      }
    });
    cy.get('[data-cy="select-user-roles"]').invoke('val', ['SuperAdmin']).trigger('change', { force: true });
    cy.get('[data-cy="btn-simpan-user"]').click();

    // Assert (Verifikasi UI)
    cy.wait('@updateUserInvalidValue').then((updateInterception) => {
      expect(updateInterception.response?.statusCode).to.equal(422);
      const errors = updateInterception.response?.body?.errors ?? {};
      const hasRoleError = Boolean(errors['roles.0']?.length) || Boolean(errors.roles?.length);
      expect(hasRoleError).to.equal(true);
    });
    cy.location('pathname').should('eq', '/app/user');
    cy.get('[data-cy="form-user"]').should('exist');
    cy.get('[data-cy="input-user-email"]').invoke('val').should('eq', emailUser);
  });
});
