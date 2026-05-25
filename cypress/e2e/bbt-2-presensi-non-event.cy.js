const pilihOpsiPertama = (selector) => {
    cy.get(selector)
        .find("option")
        .then(($options) => {
            const opsiValid = [...$options].find(
                (option) => option.value && option.value.trim() !== "",
            );
            expect(opsiValid, `opsi valid untuk ${selector}`).to.not.equal(
                undefined,
            );
            cy.get(selector).select(opsiValid.value);
        });
};

const isiDataDasarTamu = (suffix) => {
    const suffixAman = String(suffix).slice(-4);

    cy.get('[data-cy="input-nama"]').type(`Agus Saputra ${suffix}`);
    cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]').check({ force: true });
    cy.get('[data-cy="input-nomor_telepon"]').type(`08138888${suffixAman}`);
    cy.get('[data-cy="input-email"]').type(`tamu.bbt2.${suffix}@example.com`);
};

const isiDataKunjunganDasar = () => {
    cy.get('[data-cy="textarea-keperluan"]').type(
        "Kunjungan untuk kebutuhan administrasi.",
    );
    cy.get('[data-cy="input-estimasi_durasi"]').clear().type("2");
    pilihOpsiPertama('[data-cy="select-transportasi"]');
};

const assertFieldFrontendError = (selector) => {
    cy.get(selector).then(($el) => {
        expect($el[0].checkValidity()).to.equal(false);
        expect($el[0].validationMessage).to.not.equal("");
    });
};

const assertSemuaFieldWajibError = (
    selectorKategori,
    daftarSelectorMandatory,
) => {
    cy.visit("/non-event/tujuan");
    cy.get(selectorKategori).click();
    cy.get('[data-cy="btn-submit-presensi"]').click();

    cy.url().should("include", "/non-event/presensi");
    cy.url().should("not.include", "/sukses/");

    daftarSelectorMandatory.forEach((selector) => {
        assertFieldFrontendError(selector);
    });
};

describe("BBT-2 Pengisian Presensi Non-Event", () => {
    it("kategori instansi: tamu mengisi semua field wajib dengan data valid", () => {
        // Arrange (Kunjungi URL)
        cy.visit("/non-event/tujuan");

        // Act (Isi form/klik)
        cy.get('[data-cy="btn-tujuan-instansi"]').click();
        isiDataDasarTamu(`${Date.now()}02`);
        cy.get('[data-cy="input-instansi"]').type("PT Maju Jaya");
        pilihOpsiPertama('[data-cy="select-jenis_instansi"]');
        pilihOpsiPertama('[data-cy="select-jabatan"]');
        pilihOpsiPertama('[data-cy="select-pihak_dituju"]');
        isiDataKunjunganDasar();
        cy.get('[data-cy="btn-submit-presensi"]').click();

        // Assert (Verifikasi UI)
        cy.url().should("include", "/sukses/");
        cy.get('[data-cy="text-registrasi-berhasil"]').should("be.visible");
    });

    it("kategori bisnis: tamu mengisi semua field wajib dengan data valid", () => {
        // Arrange (Kunjungi URL)
        cy.visit("/non-event/tujuan");

        // Act (Isi form/klik)
        cy.get('[data-cy="btn-tujuan-bisnis"]').click();
        isiDataDasarTamu(`${Date.now()}03`);
        cy.get('[data-cy="input-instansi"]').type("PT Solusi Digital");
        pilihOpsiPertama('[data-cy="select-kategori_instansi"]');
        pilihOpsiPertama('[data-cy="select-skala_instansi"]');
        pilihOpsiPertama('[data-cy="select-jabatan"]');
        pilihOpsiPertama('[data-cy="select-pihak_dituju"]');
        isiDataKunjunganDasar();
        cy.get('[data-cy="btn-submit-presensi"]').click();

        // Assert (Verifikasi UI)
        cy.url().should("include", "/sukses/");
        cy.get('[data-cy="text-registrasi-berhasil"]').should("be.visible");
    });

    it("kategori ortu: tamu mengisi semua field wajib dengan data valid", () => {
        // Arrange (Kunjungi URL)
        cy.visit("/non-event/tujuan");

        // Act (Isi form/klik)
        cy.get('[data-cy="btn-tujuan-ortu"]').click();
        isiDataDasarTamu(`${Date.now()}04`);
        pilihOpsiPertama('[data-cy="select-hubungan_dengan_mahasiswa"]');
        cy.get('[data-cy="input-nama_mahasiswa"]').type("Budi Santoso");
        pilihOpsiPertama('[data-cy="select-prodi_mahasiswa"]');
        cy.get('[data-cy="input-nim_mahasiswa"]').type("2312012345");
        pilihOpsiPertama('[data-cy="select-pihak_dituju"]');
        isiDataKunjunganDasar();
        cy.get('[data-cy="btn-submit-presensi"]').click();

        // Assert (Verifikasi UI)
        cy.url().should("include", "/sukses/");
        cy.get('[data-cy="text-registrasi-berhasil"]').should("be.visible");
    });

    it("kategori informasi kampus: tamu mengisi semua field wajib dengan data valid", () => {
        // Arrange (Kunjungi URL)
        cy.visit("/non-event/tujuan");

        // Act (Isi form/klik)
        cy.get('[data-cy="btn-tujuan-informasi_kampus"]').click();
        isiDataDasarTamu(`${Date.now()}05`);
        cy.get('[data-cy="input-asal_sekolah"]').type("SMA Negeri 1 Pekanbaru");
        pilihOpsiPertama('[data-cy="select-prodi_diminati"]');
        isiDataKunjunganDasar();
        cy.get('[data-cy="btn-submit-presensi"]').click();

        // Assert (Verifikasi UI)
        cy.url().should("include", "/sukses/");
        cy.get('[data-cy="text-registrasi-berhasil"]').should("be.visible");
    });

    it("kategori lainnya: tamu mengisi semua field wajib dengan data valid", () => {
        // Arrange (Kunjungi URL)
        cy.visit("/non-event/tujuan");

        // Act (Isi form/klik)
        cy.get('[data-cy="btn-tujuan-lainnya"]').click();
        isiDataDasarTamu(`${Date.now()}01`);
        cy.get('[data-cy="input-pihak_dituju"]').type("Petugas Informasi");
        isiDataKunjunganDasar();

        cy.get('[data-cy="btn-submit-presensi"]').click();

        // Assert (Verifikasi UI)
        cy.url().should("include", "/sukses/");
        cy.get('[data-cy="text-registrasi-berhasil"]').should("be.visible");
    });

    it("tamu gagal mengirim form saat salah satu field wajib dikosongkan", () => {
        // Arrange (Kunjungi URL)
        const uniqueEmail = `tamu.bbt2.invalid.${Date.now()}@example.com`;

        cy.visit("/non-event/tujuan");
        cy.get('[data-cy="btn-tujuan-lainnya"]').click();

        // Act (Isi form/klik)
        cy.get('[data-cy="input-nama"]').type("Agus Saputra");
        cy.get('[data-cy="radio-jenis_kelamin-laki-laki"]').check({
            force: true,
        });
        cy.get('[data-cy="input-nomor_telepon"]').type("081388888889");
        cy.get('[data-cy="input-email"]').type(uniqueEmail);
        cy.get('[data-cy="input-pihak_dituju"]').type("Petugas Informasi");
        cy.get('[data-cy="input-estimasi_durasi"]').clear().type("2");
        cy.get('[data-cy="select-transportasi"]').select("Jalan Kaki");
        cy.get('[data-cy="btn-submit-presensi"]').click();

        // Assert (Verifikasi UI)
        cy.url().should("include", "/non-event/presensi");
        cy.url().should("not.include", "/sukses/");
        cy.get('[data-cy="textarea-keperluan"]').then(($el) => {
            expect($el[0].checkValidity()).to.equal(false);
            expect($el[0].validationMessage).to.not.equal("");
        });
        cy.get('[data-cy="input-nama"]').should("have.value", "Agus Saputra");
    });

    it("kategori instansi: saat semua field kosong maka semua field mandatory menampilkan error frontend", () => {
        // Arrange + Act + Assert
        assertSemuaFieldWajibError('[data-cy="btn-tujuan-instansi"]', [
            '[data-cy="input-nama"]',
            '[data-cy="radio-jenis_kelamin-laki-laki"]',
            '[data-cy="input-nomor_telepon"]',
            '[data-cy="input-email"]',
            '[data-cy="input-instansi"]',
            '[data-cy="select-jenis_instansi"]',
            '[data-cy="select-jabatan"]',
            '[data-cy="select-pihak_dituju"]',
            '[data-cy="textarea-keperluan"]',
            '[data-cy="input-estimasi_durasi"]',
            '[data-cy="select-transportasi"]',
        ]);
    });

    it("kategori bisnis: saat semua field kosong maka semua field mandatory menampilkan error frontend", () => {
        // Arrange + Act + Assert
        assertSemuaFieldWajibError('[data-cy="btn-tujuan-bisnis"]', [
            '[data-cy="input-nama"]',
            '[data-cy="radio-jenis_kelamin-laki-laki"]',
            '[data-cy="input-nomor_telepon"]',
            '[data-cy="input-email"]',
            '[data-cy="input-instansi"]',
            '[data-cy="select-kategori_instansi"]',
            '[data-cy="select-skala_instansi"]',
            '[data-cy="select-jabatan"]',
            '[data-cy="select-pihak_dituju"]',
            '[data-cy="textarea-keperluan"]',
            '[data-cy="input-estimasi_durasi"]',
            '[data-cy="select-transportasi"]',
        ]);
    });

    it("kategori ortu: saat semua field kosong maka semua field mandatory menampilkan error frontend", () => {
        // Arrange + Act + Assert
        assertSemuaFieldWajibError('[data-cy="btn-tujuan-ortu"]', [
            '[data-cy="input-nama"]',
            '[data-cy="radio-jenis_kelamin-laki-laki"]',
            '[data-cy="input-nomor_telepon"]',
            '[data-cy="input-email"]',
            '[data-cy="select-hubungan_dengan_mahasiswa"]',
            '[data-cy="input-nama_mahasiswa"]',
            '[data-cy="select-prodi_mahasiswa"]',
            '[data-cy="select-pihak_dituju"]',
            '[data-cy="textarea-keperluan"]',
            '[data-cy="input-estimasi_durasi"]',
            '[data-cy="select-transportasi"]',
        ]);
    });

    it("kategori informasi kampus: saat semua field kosong maka semua field mandatory menampilkan error frontend", () => {
        // Arrange + Act + Assert
        assertSemuaFieldWajibError('[data-cy="btn-tujuan-informasi_kampus"]', [
            '[data-cy="input-nama"]',
            '[data-cy="radio-jenis_kelamin-laki-laki"]',
            '[data-cy="input-nomor_telepon"]',
            '[data-cy="input-email"]',
            '[data-cy="input-asal_sekolah"]',
            '[data-cy="select-prodi_diminati"]',
            '[data-cy="textarea-keperluan"]',
            '[data-cy="input-estimasi_durasi"]',
            '[data-cy="select-transportasi"]',
        ]);
    });

    it("kategori lainnya: saat semua field kosong maka semua field mandatory menampilkan error frontend", () => {
        // Arrange + Act + Assert
        assertSemuaFieldWajibError('[data-cy="btn-tujuan-lainnya"]', [
            '[data-cy="input-nama"]',
            '[data-cy="radio-jenis_kelamin-laki-laki"]',
            '[data-cy="input-nomor_telepon"]',
            '[data-cy="input-email"]',
            '[data-cy="input-pihak_dituju"]',
            '[data-cy="textarea-keperluan"]',
            '[data-cy="input-estimasi_durasi"]',
            '[data-cy="select-transportasi"]',
        ]);
    });
});
