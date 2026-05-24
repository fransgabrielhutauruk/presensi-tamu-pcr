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

const ambilDetailIdDariActionHtml = (actionHtml) => {
  const matched = String(actionHtml ?? '').match(/jf-detail="([^"]+)"/);
  expect(matched, 'id detail tersedia pada kolom action').to.not.equal(null);
  return matched[1];
};

const ambilDeleteIdDariActionHtml = (actionHtml) => {
  const matched = String(actionHtml ?? '').match(/jf-delete="([^"]+)"/);
  expect(matched, 'id hapus tersedia pada kolom action').to.not.equal(null);
  return matched[1];
};

const ekstrakCsrfToken = (html) => {
  const matched = html.match(/name="_token"[^>]*value="([^"]+)"/);
  expect(matched, 'token CSRF ditemukan pada form').to.not.equal(null);
  return matched[1];
};

const requestListFeedback = (csrfToken, searchKeyword) => {
  return cy.request({
    method: 'POST',
    url: '/app/feedback/data/list',
    form: true,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': String(csrfToken),
    },
    body: {
      draw: '1',
      start: '0',
      length: '50',
      'search[value]': searchKeyword,
      'search[regex]': 'false',
    },
  });
};

const buatFeedbackTamu = () => {
  const suffix = Date.now().toString().slice(-6);
  const namaTamu = `Tamu Feedback Detail ${suffix}`;
  const komentar = `Komentar feedback detail BBT16 ${suffix}`;

  return cy.request('/non-event/presensi?tujuan=lainnya').then((formResponse) => {
    expect(formResponse.status).to.equal(200);
    const csrfToken = ekstrakCsrfToken(formResponse.body);

    return cy.request({
      method: 'POST',
      url: '/non-event/store-presensi',
      form: true,
      followRedirect: false,
      body: {
        _token: csrfToken,
        nama: namaTamu,
        jenis_kelamin: 'Laki-laki',
        nomor_telepon: `081277${suffix}`,
        email: `bbt16.${suffix}@example.com`,
        kategori_tujuan: 'lainnya',
        pihak_dituju: 'Petugas Informasi',
        keperluan: 'Pengujian melihat detail feedback BBT-16',
        estimasi_durasi: '1',
        transportasi: 'Mobil',
      },
    });
  }).then((presensiResponse) => {
    expect(presensiResponse.status).to.equal(302);
    const suksesPath = String(presensiResponse.headers.location ?? '');
    expect(suksesPath).to.include('/sukses/');
    const kunjunganId = suksesPath.split('/').pop();
    expect(kunjunganId, 'kunjunganId hasil presensi').to.not.equal('');

    return cy.request(`/feedback/${kunjunganId}`).then((feedbackFormResponse) => {
      expect(feedbackFormResponse.status).to.equal(200);
      const csrfTokenFeedback = ekstrakCsrfToken(feedbackFormResponse.body);

      return cy.request({
        method: 'POST',
        url: `/checkout/${kunjunganId}`,
        form: true,
        followRedirect: false,
        body: { _token: csrfTokenFeedback },
      }).then((checkoutResponse) => {
        expect(checkoutResponse.status).to.equal(302);
        expect(String(checkoutResponse.headers.location ?? '')).to.include(`/feedback/${kunjunganId}`);

        return cy.request({
          method: 'POST',
          url: `/feedback/${kunjunganId}`,
          form: true,
          followRedirect: false,
          body: {
            _token: csrfTokenFeedback,
            rating: '5',
            komentar,
          },
        });
      });
    });
  }).then((feedbackResponse) => {
    expect(feedbackResponse.status).to.equal(302);
    const redirectLocation = String(feedbackResponse.headers.location ?? '');
    const redirectPathname = redirectLocation.startsWith('http')
      ? new URL(redirectLocation).pathname
      : redirectLocation;
    expect(redirectPathname).to.equal('/');

    return cy.wrap({
      namaTamu,
      komentar,
      rating: '5',
    });
  });
};

describe('BBT-16 Melihat Feedback', () => {
  it('admin membuka detail feedback dan melihat rating serta komentar lengkap', () => {
    // Arrange (Kunjungi URL)
    buatFeedbackTamu().then((feedbackData) => {
      loginSebagaiAdmin();
      cy.intercept('POST', '**/app/feedback/data/list*').as('listFeedback');
      cy.visit('/app/feedback');
      cy.wait('@listFeedback').its('response.statusCode').should('eq', 200);
      cy.get('[data-cy="table-feedback-list"]').should('be.visible');

      // Act (Isi form/klik)
      cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
        requestListFeedback(csrfToken, feedbackData.namaTamu).then((listResponse) => {
          expect(listResponse.status).to.equal(200);
          const rows = listResponse.body?.data ?? [];
          const row = rows.find((item) => String(item?.nama_tamu ?? '') === feedbackData.namaTamu);
          expect(row, 'feedback baru ditemukan melalui search endpoint').to.not.equal(undefined);

          const detailId = ambilDetailIdDariActionHtml(row?.action ?? '');
          cy.request({
            method: 'POST',
            url: '/app/feedback/data/detail',
            form: true,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': String(csrfToken),
            },
            body: { id: detailId },
          }).then((detailResponse) => {
            // Assert (Verifikasi UI)
            expect(detailResponse.status).to.equal(200);
            expect(detailResponse.body?.status).to.equal(true);
            expect(String(detailResponse.body?.data?.rating ?? '')).to.equal(feedbackData.rating);
            expect(String(detailResponse.body?.data?.komentar ?? '')).to.equal(feedbackData.komentar);
          });
        });
      });
    });
  });

  it('pengguna role mahasiswa ditolak saat mengakses halaman feedback', () => {
    // Arrange (Kunjungi URL)
    loginDenganSkenario('mahasiswa-role', 'Mahasiswa');

    // Act (Isi form/klik)
    cy.request({
      method: 'GET',
      url: '/app/feedback',
      failOnStatusCode: false,
    }).as('requestFeedbackMahasiswa');

    // Assert (Verifikasi UI)
    cy.get('@requestFeedbackMahasiswa').its('status').should('eq', 403);
  });

  it('pengguna tamu diarahkan ke halaman login saat mengakses feedback', () => {
    // Arrange (Kunjungi URL)
    cy.clearCookies();
    cy.clearLocalStorage();

    // Act (Isi form/klik)
    cy.visit('/app/feedback');

    // Assert (Verifikasi UI)
    cy.location('pathname', { timeout: 10000 }).should('eq', '/login');
    cy.get('[data-cy="btn-login-google"]').should('be.visible');
  });

  it('admin tidak menemukan data saat mencari kata kunci feedback yang tidak ada', () => {
    // Arrange (Kunjungi URL)
    loginSebagaiAdmin();
    cy.intercept('POST', '**/app/feedback/data/list*').as('listFeedbackNoMatch');
    cy.visit('/app/feedback');
    cy.wait('@listFeedbackNoMatch').its('response.statusCode').should('eq', 200);
    const kataKunciTidakAda = `bbt16-feedback-no-match-${Date.now()}`;

    // Act (Isi form/klik)
    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
      requestListFeedback(csrfToken, kataKunciTidakAda).then((listResponse) => {
        // Assert (Verifikasi UI)
        expect(listResponse.status).to.equal(200);
        const rows = listResponse.body?.data ?? [];
        expect(rows.length).to.equal(0);
      });
    });
  });

  it('admin menghapus feedback dan data tidak muncul lagi di daftar', () => {
    // Arrange (Kunjungi URL)
    buatFeedbackTamu().then((feedbackData) => {
      loginSebagaiAdmin();
      cy.intercept('POST', '**/app/feedback/data/list*').as('listFeedbackDelete');
      cy.visit('/app/feedback');
      cy.wait('@listFeedbackDelete').its('response.statusCode').should('eq', 200);

      // Act (Isi form/klik)
      cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((csrfToken) => {
        requestListFeedback(csrfToken, feedbackData.namaTamu).then((listResponse) => {
          expect(listResponse.status).to.equal(200);
          const rows = listResponse.body?.data ?? [];
          const row = rows.find((item) => String(item?.nama_tamu ?? '') === feedbackData.namaTamu);
          expect(row, 'feedback target tersedia sebelum dihapus').to.not.equal(undefined);
          const deleteId = ambilDeleteIdDariActionHtml(row?.action ?? '');

          cy.request({
            method: 'POST',
            url: '/app/feedback/destroy',
            form: true,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': String(csrfToken),
            },
            body: { id: deleteId },
          }).then((deleteResponse) => {
            expect(deleteResponse.status).to.equal(200);
            expect(deleteResponse.body?.status).to.equal(true);

            // Assert (Verifikasi UI)
            requestListFeedback(csrfToken, feedbackData.namaTamu).then((afterDeleteResponse) => {
              expect(afterDeleteResponse.status).to.equal(200);
              const rowsAfterDelete = afterDeleteResponse.body?.data ?? [];
              const deletedRow = rowsAfterDelete.find((item) => String(item?.nama_tamu ?? '') === feedbackData.namaTamu);
              expect(deletedRow, 'feedback sudah tidak ada setelah dihapus').to.equal(undefined);
            });
          });
        });
      });
    });
  });
});
