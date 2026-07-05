import { htmlReport } from "https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js";
import { textSummary } from "https://jslib.k6.io/k6-summary/0.1.0/index.js";
import http from "k6/http";
import { check, sleep } from "k6";

const BASE_URL = __ENV.BASE_URL || "http://localhost:9000";

const KATEGORI_TUJUAN = [
    "instansi",
    "bisnis",
    "ortu",
    "informasi_kampus",
    "lainnya",
];
const JENIS_KELAMIN = ["Laki-laki", "Perempuan"];
const TRANSPORTASI = ["Mobil", "Motor", "Bus", "Ojek Online", "Jalan Kaki"];

export const options = {
    stages: [
        { duration: "1m", target: 25 },
        { duration: "1m", target: 100 },
        { duration: "2m", target: 100 },
        { duration: "1m", target: 0 },
    ],
};

function pick(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}

function randomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function buildSuffix() {
    return `${Date.now()}${Math.floor(Math.random() * 100000)}`;
}

function extractCsrfToken(html) {
    const inputToken = html.match(/name="_token"[^>]*value="([^"]+)"/i);
    if (inputToken && inputToken[1]) {
        return inputToken[1];
    }

    const metaToken = html.match(/name="csrf-token"\s+content="([^"]+)"/i);
    return metaToken ? metaToken[1] : null;
}

function getLocationHeader(headers) {
    const value = headers.Location || headers.location;
    if (Array.isArray(value)) {
        return value[0];
    }
    return value;
}

function buildPayload(kategoriTujuan) {
    const suffix = buildSuffix();
    const payload = {
        kategori_tujuan: kategoriTujuan,
        nama: `Load Test ${suffix}`,
        jenis_kelamin: pick(JENIS_KELAMIN),
        nomor_telepon: `08${suffix.slice(-10)}`,
        email: `loadtest.${suffix}@example.com`,
        estimasi_durasi: randomInt(1, 4),
        transportasi: pick(TRANSPORTASI),
    };

    switch (kategoriTujuan) {
        case "instansi":
            return {
                ...payload,
                instansi: `Instansi ${suffix}`,
                jenis_instansi: "Swasta",
                jabatan: "Staff",
                pihak_dituju: "Dosen",
                keperluan: `Kunjungan instansi ${suffix}`,
            };

        case "bisnis":
            return {
                ...payload,
                instansi: `Perusahaan ${suffix}`,
                kategori_instansi: "Teknologi",
                jenis_perusahaan: "Nasional",
                skala_instansi: "Menengah",
                jabatan: "Manager",
                pihak_dituju: "Staff",
                keperluan: `Diskusi kerja sama ${suffix}`,
            };

        case "ortu":
            return {
                ...payload,
                hubungan_dengan_mahasiswa: "Orang Tua",
                nama_mahasiswa: `Mahasiswa ${suffix}`,
                prodi_mahasiswa: "Teknik Informatika",
                nim_mahasiswa: suffix.slice(-10),
                pihak_dituju: "Dosen",
                keperluan: `Konsultasi akademik ${suffix}`,
            };

        case "informasi_kampus":
            return {
                ...payload,
                asal_sekolah: `SMA ${suffix.slice(-4)}`,
                prodi_diminati: "Teknik Informatika",
                keperluan: `Mencari informasi kampus ${suffix}`,
            };

        case "lainnya":
        default:
            return {
                ...payload,
                pihak_dituju: "Staff",
                keperluan: `Keperluan lainnya ${suffix}`,
            };
    }
}

export default function () {
    const kategoriTujuan = pick(KATEGORI_TUJUAN);
    const formUrl = `${BASE_URL}/non-event/presensi?tujuan=${kategoriTujuan}`;

    const formResponse = http.get(formUrl);
    const csrfToken = extractCsrfToken(formResponse.body);

    check(formResponse, {
        "GET form non-event status 200": (r) => r.status === 200,
        "CSRF token ditemukan": () => !!csrfToken,
    });

    if (!csrfToken) {
        sleep(1);
        return;
    }

    const payload = buildPayload(kategoriTujuan);
    payload._token = csrfToken;

    const submitResponse = http.post(
        `${BASE_URL}/non-event/store-presensi`,
        payload,
        {
            redirects: 0,
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                Referer: formUrl,
            },
        },
    );

    const locationHeader = getLocationHeader(submitResponse.headers);

    check(submitResponse, {
        "POST presensi status redirect": (r) =>
            r.status === 302 || r.status === 303,
        "Redirect ke halaman sukses": () =>
            typeof locationHeader === "string" &&
            locationHeader.includes("/sukses/"),
    });

    sleep(1);
}

export function handleSummary(data) {
    return {
        [`laporan-performa-${Date.now()}.html`]: htmlReport(data),
        stdout: textSummary(data, { indent: " ", enableColors: true }),
    };
}
