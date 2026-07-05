import { htmlReport } from "https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js";
import { textSummary } from "https://jslib.k6.io/k6-summary/0.1.0/index.js";
import http from "k6/http";
import { check, sleep } from "k6";

const BASE_URL = __ENV.BASE_URL || "http://localhost:9000";
const EVENT_ID_FROM_ENV = __ENV.EVENT_ID || "";
const REQUEST_TIMEOUT = __ENV.REQUEST_TIMEOUT || "30s";
const MAX_SETUP_RETRIES = Number(__ENV.SETUP_RETRIES || 3);

const JENIS_KELAMIN = ["Laki-laki", "Perempuan"];
const DEFAULT_PERAN = ["Peserta", "Narasumber", "Panitia"];
const DEFAULT_TRANSPORTASI = [
    "Mobil",
    "Motor",
    "Bus",
    "Ojek Online",
    "Jalan Kaki",
];
const INVALID_EVENT_SEGMENTS = new Set([
    "list",
    "store",
    "presensi",
    "presensi-civitas",
    "civitas-store",
    "check-civitas",
    "fetch-external-data",
]);

export const options = {
    stages: [
        { duration: "1m", target: 200 }, // 1. Naik perlahan ke 200 user dalam 1 menit
        { duration: "1m", target: 500 }, // 2. Naik lagi hingga mencapai puncak 500 user di menit ke-2
        { duration: "2m", target: 500 }, // 3. Tahan beban konstan 500 user selama 2 menit (Uji Ketahanan)
        { duration: "1m", target: 0 }, // 4. Turunkan perlahan ke 0 user (Cooling down)
    ],
};

function pick(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}

function buildSuffix() {
    const randomPart = Math.floor(Math.random() * 100000);
    return `${Date.now()}${randomPart}`;
}

// Tambahkan header tiruan browser agar tidak diblokir firewall (WAF)
const customHeaders = {
    "User-Agent":
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    Accept: "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
};

function extractCsrfToken(html) {
    if (typeof html !== "string" || !html) {
        return null;
    }

    const inputToken = html.match(/name="_token"[^>]*value="([^"]+)"/i);
    if (inputToken && inputToken[1]) {
        return inputToken[1];
    }

    const metaToken = html.match(/name="csrf-token"\s+content="([^"]+)"/i);
    return metaToken ? metaToken[1] : null;
}

function extractHiddenEventId(html) {
    if (typeof html !== "string" || !html) {
        return null;
    }

    const eventIdMatch = html.match(/name="event_id"[^>]*value="([^"]+)"/i);
    return eventIdMatch ? eventIdMatch[1] : null;
}

function extractSelectOptions(html, fieldName) {
    if (typeof html !== "string" || !html) {
        return [];
    }

    const selectMatch = html.match(
        new RegExp(
            `<select[^>]*name="${fieldName}"[^>]*>([\\s\\S]*?)<\\/select>`,
            "i",
        ),
    );

    if (!selectMatch || !selectMatch[1]) {
        return [];
    }

    const values = [];
    const optionRegex = /<option[^>]*value="([^"]*)"[^>]*>/gi;
    let optionMatch = optionRegex.exec(selectMatch[1]);

    while (optionMatch !== null) {
        const value = (optionMatch[1] || "").trim();
        if (value) {
            values.push(value);
        }
        optionMatch = optionRegex.exec(selectMatch[1]);
    }

    return values;
}

function extractEventIdFromListHtml(html) {
    if (typeof html !== "string" || !html) {
        return null;
    }

    const anchorRegex = /<a\b[^>]*\bdata-cy="card-event-(?:first|\d+)"[^>]*>/gi;
    let anchorMatch = anchorRegex.exec(html);

    while (anchorMatch !== null) {
        const anchorTag = anchorMatch[0] || "";
        const hrefMatch = anchorTag.match(/\bhref="([^"]+)"/i);
        const href = hrefMatch && hrefMatch[1] ? hrefMatch[1] : "";
        const pathMatch = href.match(/\/event\/([^/?#"]+)/i);
        const candidateId =
            pathMatch && pathMatch[1] ? pathMatch[1].trim() : "";

        if (
            candidateId &&
            !INVALID_EVENT_SEGMENTS.has(candidateId.toLowerCase())
        ) {
            return candidateId;
        }

        anchorMatch = anchorRegex.exec(html);
    }

    return null;
}

function isValidEventId(eventId) {
    if (typeof eventId !== "string") {
        return false;
    }

    const trimmed = eventId.trim();
    if (!trimmed) {
        return false;
    }

    if (trimmed.includes("/")) {
        return false;
    }

    if (INVALID_EVENT_SEGMENTS.has(trimmed.toLowerCase())) {
        return false;
    }

    return true;
}

function resolveEventIdFromList() {
    for (let attempt = 1; attempt <= MAX_SETUP_RETRIES; attempt++) {
        const listResponse = http.get(`${BASE_URL}/event/list`, {
            timeout: REQUEST_TIMEOUT,
            responseType: "text",
        });
        const listBody =
            typeof listResponse.body === "string" ? listResponse.body : "";
        const eventId = extractEventIdFromListHtml(listBody);

        const isHealthyResponse = check(listResponse, {
            "GET event list status 200": (r) => r.status === 200,
            "Event ID valid dari list": () => isValidEventId(eventId),
        });

        if (isHealthyResponse && isValidEventId(eventId)) {
            return eventId;
        }
    }

    return null;
}

function getLocationHeader(headers) {
    const value = headers.Location || headers.location;
    if (Array.isArray(value)) {
        return value[0];
    }

    return value;
}

function postPresensiEvent(formUrl, payload) {
    return http.post(`${BASE_URL}/event/store`, payload, {
        redirects: 0,
        timeout: REQUEST_TIMEOUT,
        headers: Object.assign(
            {
                "Content-Type": "application/x-www-form-urlencoded",
                Referer: formUrl,
            },
            customHeaders,
        ), // Menggabungkan header default dengan User-Agent browser
    });
}

export function setup() {
    const resolvedEventId = EVENT_ID_FROM_ENV || resolveEventIdFromList();
    const eventId = isValidEventId(resolvedEventId) ? resolvedEventId : null;

    if (!eventId) {
        throw new Error(
            "Tidak menemukan event_id valid dari /event/list. Pastikan ada event hari ini, atau jalankan dengan -e EVENT_ID=<hashed_event_id>.",
        );
    }

    return { eventId };
}

export default function (setupData) {
    const setupEventId =
        setupData && setupData.eventId ? setupData.eventId : "";
    const candidateEventId = EVENT_ID_FROM_ENV || setupEventId;
    const eventId = isValidEventId(candidateEventId) ? candidateEventId : null;

    check(
        { eventId },
        {
            "Event ID tersedia": () => !!eventId,
        },
    );

    if (!eventId) {
        sleep(1);
        return;
    }

    const nonCivitasFormUrl = `${BASE_URL}/event/presensi/${eventId}`;
    const formResponse = http.get(nonCivitasFormUrl, {
        timeout: REQUEST_TIMEOUT,
        responseType: "text",
    });
    const formBody =
        typeof formResponse.body === "string" ? formResponse.body : "";
    const csrfToken = extractCsrfToken(formBody);
    const hiddenEventIdCandidate = extractHiddenEventId(formBody) || eventId;
    const hiddenEventId = isValidEventId(hiddenEventIdCandidate)
        ? hiddenEventIdCandidate
        : null;

    const formOk = check(formResponse, {
        "GET form presensi event status 200": (r) => r.status === 200,
        "CSRF token ditemukan": () => !!csrfToken,
        "Hidden event_id ditemukan": () => !!hiddenEventId,
    });

    if (!formOk || !csrfToken || !hiddenEventId) {
        sleep(1);
        return;
    }

    const peranOptions = extractSelectOptions(formBody, "peran");
    const transportasiOptions = extractSelectOptions(formBody, "transportasi");
    const suffix = buildSuffix();

    const payload = {
        _token: csrfToken,
        event_id: hiddenEventId,
        nama: `Load Event ${suffix}`,
        jenis_kelamin: pick(JENIS_KELAMIN),
        nomor_telepon: `08${suffix.slice(-10)}`,
        email: `event.load.${suffix}@example.com`,
        instansi: `Instansi ${suffix.slice(-6)}`,
        peran: pick(peranOptions.length > 0 ? peranOptions : DEFAULT_PERAN),
        transportasi: pick(
            transportasiOptions.length > 0
                ? transportasiOptions
                : DEFAULT_TRANSPORTASI,
        ),
    };

    let submitResponse = postPresensiEvent(nonCivitasFormUrl, payload);
    if (submitResponse.status === 419) {
        const refreshFormResponse = http.get(nonCivitasFormUrl, {
            timeout: REQUEST_TIMEOUT,
            responseType: "text",
        });
        const refreshBody =
            typeof refreshFormResponse.body === "string"
                ? refreshFormResponse.body
                : "";
        const refreshedToken = extractCsrfToken(refreshBody);

        check(refreshFormResponse, {
            "GET refresh form status 200": (r) => r.status === 200,
            "Refresh CSRF token ditemukan": () => !!refreshedToken,
        });

        if (refreshedToken) {
            payload._token = refreshedToken;
            submitResponse = postPresensiEvent(nonCivitasFormUrl, payload);
        }
    }

    const locationHeader = getLocationHeader(submitResponse.headers);

    check(submitResponse, {
        "POST presensi event status redirect": (r) =>
            r.status === 302 || r.status === 303,
        "Redirect ke halaman sukses": () =>
            typeof locationHeader === "string" &&
            locationHeader.includes("/sukses/"),
    });

    sleep(Math.random() * 3 + 5);
}

export function handleSummary(data) {
    return {
        [`laporan-performa-load-test-bbt-3-${Date.now()}.html`]:
            htmlReport(data),
        stdout: textSummary(data, { indent: " ", enableColors: true }),
    };
}
