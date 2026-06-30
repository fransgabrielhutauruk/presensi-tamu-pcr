const loginSebagaiAdmin = () => {
    cy.visit("/login");
    cy.get('[data-cy="btn-login-google"]')
        .should("be.visible")
        .invoke("attr", "href", "/auth/google?cy_scenario=multi-role");
    cy.get('[data-cy="btn-login-google"]').click();
    cy.location("pathname", { timeout: 10000 }).should("eq", "/app/event");
    cy.get('[data-cy="menu-user-toggle"]').click();
    cy.get('[data-cy="btn-switch-role-admin"]').click();
    cy.get('[data-cy="menu-user-toggle"]').click();
    cy.get('[data-cy="badge-active-role"]').should("contain", "Admin");
};

const bukaHalamanLogAktivitas = (listAlias = "listLogAktivitas") => {
    loginSebagaiAdmin();
    cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(listAlias);
    cy.visit("/app/log-aktivitas");
    cy.wait(`@${listAlias}`);
    cy.wait(500); // Beri waktu DataTable render rows setelah AJAX selesai
    cy.get('[data-cy="table-log-aktivitas-list"]').should("be.visible");
};

// Buka panel filter dan tunggu panel terbuka
const bukaPanelFilter = () => {
    // Klik tombol toggle filter (bukan apply, bukan reset)
    cy.get(
        '[data-cy^="btn-table-filter-"]:not([data-cy*="-apply-"]):not([data-cy*="-reset-"])',
    )
        .first()
        .click({ force: true });
    // Tunggu panel filter muncul (ada input filter yang visible)
    cy.get('[data-cy="select-filter-log-user"]', { timeout: 5000 }).should(
        "be.visible",
    );
};

// Filter log aktivitas auto-reload saat value berubah (preXhr.dt listener) - tidak ada tombol Apply
// Gunakan ini untuk menerapkan filter user dan tunggu AJAX reload
const terapkanFilterUser = (logUserId, listAlias) => {
    cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(listAlias);
    cy.get('[data-cy="select-filter-log-user"]')
        .invoke("val", String(logUserId))
        .trigger("change", { force: true });
    cy.wait(`@${listAlias}`);
    cy.wait(300);
};

// Filter date auto-reload setelah 400ms debounce dari input event
const terapkanFilterTanggal = (dateFrom, dateTo, listAlias) => {
    cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(listAlias);
    cy.get('[data-cy="input-filter-log-date-from"]')
        .clear()
        .type(dateFrom, { force: true });
    cy.get('[data-cy="input-filter-log-date-to"]')
        .clear()
        .type(dateTo, { force: true });
    cy.wait(`@${listAlias}`); // Tunggu debounce 400ms + AJAX selesai
    cy.wait(300);
};

const tutupModalDetail = () => {
    cy.get('[data-cy="modal-log-detail"]').then(($modal) => {
        if (!$modal.hasClass("show")) return;
        cy.wait(500)
        cy.get('[data-cy="btn-close-modal-log-detail-header"]').click({
            force: true,
        });
        cy.get('[data-cy="modal-log-detail"]',).should("not.be.visible");
    });
};

const formatTanggalLokal = (dateValue) => {
    const tahun = dateValue.getFullYear();
    const bulan = String(dateValue.getMonth() + 1).padStart(2, "0");
    const tanggal = String(dateValue.getDate()).padStart(2, "0");
    return `${tahun}-${bulan}-${tanggal}`;
};

describe("BBT-12 Melihat Log Aktivitas", () => {
    it("admin melihat daftar log, melakukan pencarian, lalu memfilter berdasarkan user", () => {
        // Arrange (Kunjungi URL)
        loginSebagaiAdmin();
        cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(
            "listLogAktivitas",
        );
        cy.intercept("GET", "**/app/log-aktivitas/data/detail*").as(
            "detailLogAktivitas",
        );
        cy.visit("/app/log-aktivitas");
        cy.wait("@listLogAktivitas");
        cy.wait(500); // Tunggu DataTable render rows
        cy.get('[data-cy="table-log-aktivitas-list"]').should("be.visible");

        // Act (Klik detail log pertama)
        cy.get('[data-cy^="btn-action-detail-"]', { timeout: 10000 })
            .its("length")
            .should("be.gte", 1);
        cy.get('[data-cy^="btn-action-detail-"]')
            .first()
            .click({ force: true });

        cy.wait("@detailLogAktivitas").then((detailInterception) => {
            expect(detailInterception.response?.statusCode).to.equal(200);
            expect(detailInterception.response?.body?.status).to.equal(true);

            const description = String(
                detailInterception.response?.body?.data?.description ?? "",
            ).trim();
            const userName = String(
                detailInterception.response?.body?.data?.user ?? "",
            ).trim();
            expect(description).to.not.equal("");
            expect(userName).to.not.equal("");

            cy.wrap(description).as("logDescription");
            cy.wrap(userName).as("logUserName");
        });

        cy.get('[data-cy="modal-log-detail"]').should("be.visible");
        tutupModalDetail();

        // Pencarian dengan keyword dari deskripsi log
        cy.get("@logDescription").then((logDescription) => {
            const searchKeyword = String(logDescription)
                .split(" ")
                .slice(0, 2)
                .join(" ")
                .trim();
            expect(searchKeyword).to.not.equal("");
            cy.wrap(searchKeyword).as("searchKeyword");

            cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(
                "listLogSearch",
            );
            cy.get('[data-cy^="input-table-search-"]')
                .first()
                .then(($el) => {
                    const tableId = $el.attr("id").replace("customSearch-", "");
                    cy.window().then((win) => {
                        win.$(`#${tableId}`)
                            .DataTable()
                            .search(searchKeyword)
                            .draw();
                    });
                });
            cy.wait("@listLogSearch");
            cy.wait(300);
        });

        cy.get("@searchKeyword").then((searchKeyword) => {
            cy.contains(
                '[data-cy="table-log-aktivitas-list"] tbody tr',
                String(searchKeyword),
            ).should("be.visible");
        });

        // Filter berdasarkan user
        cy.get("@logUserName").then((logUserName) => {
            cy.get('[data-cy="select-filter-log-user"] option').then(
                ($options) => {
                    const userOption = [...$options].find(
                        (option) =>
                            option.text.trim() === String(logUserName).trim(),
                    );
                    expect(
                        userOption,
                        "opsi filter user ditemukan",
                    ).to.not.equal(undefined);
                    cy.wrap(userOption?.value ?? "").as("logUserId");
                },
            );
        });

        bukaPanelFilter();
        cy.get("@logUserId").then((logUserId) => {
            terapkanFilterUser(logUserId, "listLogFiltered");
        });

        // Assert: verifikasi filter user dikirim ke server dan ada data
        cy.get("@logUserId").then((logUserId) => {
            cy.get("@listLogFiltered").then((listInterception) => {
                const requestBody = listInterception.request?.body ?? {};
                const filterUserValue =
                    typeof requestBody === "string"
                        ? new URLSearchParams(requestBody).get("filter_user")
                        : requestBody.filter_user;
                expect(String(filterUserValue ?? "")).to.equal(
                    String(logUserId),
                );
            });
        });

        cy.get("@logUserName").then((logUserName) => {
            cy.get('[data-cy="table-log-aktivitas-list"] tbody tr')
                .its("length")
                .should("be.gte", 1);
            cy.get('[data-cy="table-log-aktivitas-list"] tbody tr')
                .first()
                .should("contain", String(logUserName));
        });
    });

    it("admin tidak menemukan data saat melakukan pencarian dengan kata kunci yang tidak ada", () => {
        // Arrange (Kunjungi URL)
        bukaHalamanLogAktivitas("listLogSearchNoMatch");

        // Act (Cari dengan keyword tidak ada)
        const keywordTidakAda = `bbt12-keyword-tidak-ada-${Date.now()}`;
        cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(
            "listLogNoMatch",
        );
        cy.get('[data-cy^="input-table-search-"]')
            .first()
            .then(($el) => {
                const tableId = $el.attr("id").replace("customSearch-", "");
                cy.window().then((win) => {
                    win.$(`#${tableId}`)
                        .DataTable()
                        .search(keywordTidakAda)
                        .draw();
                });
            });
        cy.wait("@listLogNoMatch");
        cy.wait(300);

        // Assert (Tidak ada tombol detail)
        cy.get('[data-cy^="btn-action-detail-"]', { timeout: 5000 }).should(
            "not.exist",
        );
    });

    it("admin tidak menemukan data saat memfilter tanggal log di masa depan", () => {
        // Arrange (Kunjungi URL)
        bukaHalamanLogAktivitas("listLogFutureDate");
        const tanggalBesok = formatTanggalLokal(
            new Date(Date.now() + 24 * 60 * 60 * 1000),
        );

        // Act (Filter dengan tanggal masa depan)
        bukaPanelFilter();
        terapkanFilterTanggal(
            tanggalBesok,
            tanggalBesok,
            "listLogFutureDateApplied",
        );

        // Assert (Tidak ada data dan filter dikirim dengan benar)
        cy.get("@listLogFutureDateApplied").then((listInterception) => {
            const requestBody = listInterception.request?.body ?? {};
            const getField = (field) => {
                if (typeof requestBody === "string") {
                    return new URLSearchParams(requestBody).get(field);
                }
                return requestBody[field];
            };

            expect(String(getField("filter_date_from") ?? "")).to.equal(
                tanggalBesok,
            );
            expect(String(getField("filter_date_to") ?? "")).to.equal(
                tanggalBesok,
            );
        });
        cy.get('[data-cy^="btn-action-detail-"]').should("not.exist");
    });

    it("admin mereset filter setelah menerapkan filter user pada log aktivitas", () => {
        // Arrange (Kunjungi URL)
        bukaHalamanLogAktivitas("listLogResetFilter");
        cy.intercept("GET", "**/app/log-aktivitas/data/detail*").as(
            "detailLogForReset",
        );

        // Act (Klik detail untuk mendapat nama user)
        cy.get('[data-cy^="btn-action-detail-"]', { timeout: 10000 })
            .first()
            .click({ force: true });
        cy.wait("@detailLogForReset").then((detailInterception) => {
            expect(detailInterception.response?.statusCode).to.equal(200);
            expect(detailInterception.response?.body?.status).to.equal(true);
            const userName = String(
                detailInterception.response?.body?.data?.user ?? "",
            ).trim();
            expect(userName).to.not.equal("");
            cy.wrap(userName).as("logUserNameReset");
        });
        tutupModalDetail();

        cy.get("@logUserNameReset").then((logUserNameReset) => {
            cy.get('[data-cy="select-filter-log-user"] option').then(
                ($options) => {
                    const userOption = [...$options].find(
                        (option) =>
                            option.text.trim() ===
                            String(logUserNameReset).trim(),
                    );
                    expect(
                        userOption,
                        "opsi filter user untuk reset ditemukan",
                    ).to.not.equal(undefined);
                    cy.wrap(userOption?.value ?? "").as("logUserIdReset");
                },
            );
        });

        // Terapkan filter user
        bukaPanelFilter();
        cy.get("@logUserIdReset").then((logUserIdReset) => {
            terapkanFilterUser(logUserIdReset, "listLogAfterFilter");
        });

        // Reset filter
        cy.intercept("POST", "**/app/log-aktivitas/data/list*").as(
            "listLogAfterReset",
        );
        cy.get('[data-cy^="btn-table-filter-reset-"]')
            .first()
            .click({ force: true });
        cy.wait("@listLogAfterReset");
        cy.wait(300);

        // Assert (Filter direset dan data kembali)
        cy.get("@listLogAfterReset").then((listInterception) => {
            const requestBody = listInterception.request?.body ?? {};
            const filterUserValue =
                typeof requestBody === "string"
                    ? new URLSearchParams(requestBody).get("filter_user")
                    : requestBody.filter_user;
            expect(String(filterUserValue ?? "")).to.equal("");
        });
        cy.get('[data-cy="select-filter-log-user"]').should("have.value", "");
        cy.get('[data-cy^="btn-action-detail-"]', { timeout: 10000 })
            .its("length")
            .should("be.gte", 1);
    });
});
