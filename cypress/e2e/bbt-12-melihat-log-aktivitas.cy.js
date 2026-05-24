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

const bukaHalamanLogAktivitas = (listAlias = 'listLogAktivitas') => {
  loginSebagaiAdmin();
  cy.intercept('POST', '**/app/log-aktivitas/data/list*').as(listAlias);
  cy.visit('/app/log-aktivitas');
  cy.wait(`@${listAlias}`);
  cy.get('[data-cy="table-log-aktivitas-list"]').should('be.visible');
};

const bukaPanelFilter = () => {
  cy.get('[data-cy^="btn-table-filter-"]:not([data-cy*="-apply-"]):not([data-cy*="-reset-"])').first().click({ force: true });
};

const tutupModalDetailJikaTerbuka = () => {
  cy.get('[data-cy="modal-log-detail"]').then(($modal) => {
    if (!$modal.hasClass('show')) return;

    cy.wrap($modal).within(() => {
      cy.get('[data-cy="btn-close-modal-log-detail"]:visible').first().click({ force: true });
    });

    cy.window().then((windowObject) => {
      const modalElement = windowObject.document.querySelector('[data-cy="modal-log-detail"]');
      if (!modalElement || !modalElement.classList.contains('show')) return;

      const bootstrapModal = windowObject.bootstrap?.Modal?.getInstance?.(modalElement)
        || windowObject.bootstrap?.Modal?.getOrCreateInstance?.(modalElement);

      if (bootstrapModal?.hide) {
        bootstrapModal.hide();
        return;
      }

      const jQueryInstance = windowObject.$ || windowObject.jQuery;
      if (jQueryInstance && typeof jQueryInstance(modalElement).modal === 'function') {
        jQueryInstance(modalElement).modal('hide');
        return;
      }

      modalElement.classList.remove('show');
      modalElement.setAttribute('aria-hidden', 'true');
      modalElement.style.display = 'none';
      windowObject.document.body.classList.remove('modal-open');
    });
  });

};

const formatTanggalLokal = (dateValue) => {
  const tahun = dateValue.getFullYear();
  const bulan = String(dateValue.getMonth() + 1).padStart(2, '0');
  const tanggal = String(dateValue.getDate()).padStart(2, '0');
  return `${tahun}-${bulan}-${tanggal}`;
};

describe('BBT-12 Melihat Log Aktivitas', () => {
  it('admin melihat daftar log, melakukan pencarian, lalu memfilter berdasarkan user', () => {
    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/log-aktivitas/data/list*').as('listLogAktivitas');
    cy.intercept('GET', '**/app/log-aktivitas/data/detail*').as('detailLogAktivitas');
    cy.visit('/app/log-aktivitas');
    cy.wait('@listLogAktivitas');

    // Act (Isi form/klik)
    cy.get('[data-cy="table-log-aktivitas-list"]').should('be.visible');
    cy.get('[data-cy^="btn-action-detail-log-"]').its('length').should('be.gte', 1);
    cy.get('[data-cy^="btn-action-detail-log-"]').first().click({ force: true });

    cy.wait('@detailLogAktivitas').then((detailInterception) => {
      expect(detailInterception.response?.statusCode).to.equal(200);
      expect(detailInterception.response?.body?.status).to.equal(true);

      const description = String(detailInterception.response?.body?.data?.description ?? '').trim();
      const userName = String(detailInterception.response?.body?.data?.user ?? '').trim();
      expect(description).to.not.equal('');
      expect(userName).to.not.equal('');

      cy.wrap(description).as('logDescription');
      cy.wrap(userName).as('logUserName');
    });

    cy.get('[data-cy="modal-log-detail"]').should('be.visible');
    tutupModalDetailJikaTerbuka();

    cy.get('@logDescription').then((logDescription) => {
      const searchKeyword = String(logDescription).split(' ').slice(0, 2).join(' ').trim();
      expect(searchKeyword).to.not.equal('');
      cy.wrap(searchKeyword).as('searchKeyword');
      tutupModalDetailJikaTerbuka();
      cy.get('[data-cy^="input-table-search-"]').first().clear({ force: true }).type(searchKeyword, { force: true });
    });

    cy.get('@searchKeyword').then((searchKeyword) => {
      cy.contains('[data-cy="table-log-aktivitas-list"] tbody tr', String(searchKeyword)).should('be.visible');
    });

    cy.get('@logUserName').then((logUserName) => {
      cy.get('[data-cy="select-filter-log-user"] option').then(($options) => {
        const userOption = [...$options].find((option) => option.text.trim() === String(logUserName).trim());
        expect(userOption, 'opsi filter user ditemukan').to.not.equal(undefined);
      cy.wrap(userOption?.value ?? '').as('logUserId');
      });
    });

    bukaPanelFilter();
    cy.get('@logUserId').then((logUserId) => {
      cy.get('[data-cy="select-filter-log-user"]')
        .invoke('val', String(logUserId))
        .trigger('change', { force: true });
    });
    tutupModalDetailJikaTerbuka();
    cy.get('[data-cy^="btn-table-filter-apply-"]').first().click({ force: true });

    // Assert (Verifikasi UI)
    cy.get('@logUserId').then((logUserId) => {
      cy.wait('@listLogAktivitas').then((listInterception) => {
        const requestBody = listInterception.request?.body ?? {};
        const filterUserValue = typeof requestBody === 'string'
          ? new URLSearchParams(requestBody).get('filter_user')
          : requestBody.filter_user;
        expect(String(filterUserValue ?? '')).to.equal(String(logUserId));
      });
    });

    cy.get('@logUserName').then((logUserName) => {
      cy.get('[data-cy="table-log-aktivitas-list"] tbody tr').its('length').should('be.gte', 1);
      cy.get('[data-cy="table-log-aktivitas-list"] tbody tr')
        .first()
        .should('contain', String(logUserName));
    });
  });

  it('admin tidak menemukan data saat melakukan pencarian dengan kata kunci yang tidak ada', () => {
    // Arrange (Kunjungi URL)
    bukaHalamanLogAktivitas('listLogSearchNoMatch');

    // Act (Isi form/klik)
    const keywordTidakAda = `bbt12-keyword-tidak-ada-${Date.now()}`;
    cy.get('[data-cy^="input-table-search-"]').first().clear({ force: true }).type(`${keywordTidakAda}{enter}`, { force: true });

    // Assert (Verifikasi UI)
    cy.get('[data-cy^="btn-action-detail-log-"]', { timeout: 10000 }).should('not.exist');
  });

  it('admin tidak menemukan data saat memfilter tanggal log di masa depan', () => {
    // Arrange (Kunjungi URL)
    bukaHalamanLogAktivitas('listLogFutureDate');
    const tanggalBesok = formatTanggalLokal(new Date(Date.now() + 24 * 60 * 60 * 1000));

    // Act (Isi form/klik)
    bukaPanelFilter();
    cy.get('[data-cy="input-filter-log-date-from"]').clear().type(tanggalBesok, { force: true });
    cy.get('[data-cy="input-filter-log-date-to"]').clear().type(tanggalBesok, { force: true });
    cy.get('[data-cy^="btn-table-filter-apply-"]').first().click({ force: true });

    // Assert (Verifikasi UI)
    cy.wait('@listLogFutureDate').then((listInterception) => {
      const requestBody = listInterception.request?.body ?? {};
      const getField = (field) => {
        if (typeof requestBody === 'string') {
          return new URLSearchParams(requestBody).get(field);
        }
        return requestBody[field];
      };

      expect(String(getField('filter_date_from') ?? '')).to.equal(tanggalBesok);
      expect(String(getField('filter_date_to') ?? '')).to.equal(tanggalBesok);
    });
    cy.get('[data-cy^="btn-action-detail-log-"]').should('not.exist');
  });

  it('admin mereset filter setelah menerapkan filter user pada log aktivitas', () => {
    // Arrange (Kunjungi URL)
    bukaHalamanLogAktivitas('listLogResetFilter');
    cy.intercept('GET', '**/app/log-aktivitas/data/detail*').as('detailLogForReset');

    // Act (Isi form/klik)
    cy.get('[data-cy^="btn-action-detail-log-"]').first().click({ force: true });
    cy.wait('@detailLogForReset').then((detailInterception) => {
      expect(detailInterception.response?.statusCode).to.equal(200);
      expect(detailInterception.response?.body?.status).to.equal(true);
      const userName = String(detailInterception.response?.body?.data?.user ?? '').trim();
      expect(userName).to.not.equal('');
      cy.wrap(userName).as('logUserNameReset');
    });
    tutupModalDetailJikaTerbuka();

    cy.get('@logUserNameReset').then((logUserNameReset) => {
      cy.get('[data-cy="select-filter-log-user"] option').then(($options) => {
        const userOption = [...$options].find((option) => option.text.trim() === String(logUserNameReset).trim());
        expect(userOption, 'opsi filter user untuk reset ditemukan').to.not.equal(undefined);
        cy.wrap(userOption?.value ?? '').as('logUserIdReset');
      });
    });

    tutupModalDetailJikaTerbuka();
    bukaPanelFilter();
    cy.get('@logUserIdReset').then((logUserIdReset) => {
      cy.get('[data-cy="select-filter-log-user"]').invoke('val', String(logUserIdReset)).trigger('change', { force: true });
    });
    tutupModalDetailJikaTerbuka();
    cy.get('[data-cy^="btn-table-filter-apply-"]').first().click({ force: true });
    cy.wait('@listLogResetFilter');

    bukaPanelFilter();
    cy.get('[data-cy^="btn-table-filter-reset-"]').first().click({ force: true });

    // Assert (Verifikasi UI)
    cy.wait('@listLogResetFilter').then((listInterception) => {
      const requestBody = listInterception.request?.body ?? {};
      const filterUserValue = typeof requestBody === 'string'
        ? new URLSearchParams(requestBody).get('filter_user')
        : requestBody.filter_user;
      expect(String(filterUserValue ?? '')).to.equal('');
    });
    cy.get('[data-cy="select-filter-log-user"]').should('have.value', '');
    cy.get('[data-cy^="btn-action-detail-log-"]').its('length').should('be.gte', 1);
  });
});
