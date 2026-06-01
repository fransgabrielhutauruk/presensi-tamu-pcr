import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:9000';
const EVENT_ID_FROM_ENV = __ENV.EVENT_ID || '';
const REQUEST_TIMEOUT = __ENV.REQUEST_TIMEOUT || '30s';
const SETUP_RETRIES = Number(__ENV.SETUP_RETRIES || 3);

const JENIS_KELAMIN = ['Laki-laki', 'Perempuan'];
const DEFAULT_PERAN = ['Peserta', 'Narasumber', 'Panitia'];
const DEFAULT_TRANSPORTASI = ['Mobil', 'Motor', 'Bus', 'Ojek Online', 'Jalan Kaki'];

export const options = {
    stages: [
        { duration: '1m', target: 50 },
        { duration: '1m', target: 100 },
        { duration: '1m', target: 150 },
        { duration: '1m', target: 200 },
        { duration: '1m', target: 250 },
        { duration: '1m', target: 300 },
    ],
};

function pick(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}

function buildSuffix() {
    return `${Date.now()}${Math.floor(Math.random() * 1000000000)}`;
}

function extractCsrfToken(html) {
    if (typeof html !== 'string' || !html) {
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
    if (typeof html !== 'string' || !html) {
        return null;
    }

    const eventIdMatch = html.match(/name="event_id"[^>]*value="([^"]+)"/i);
    return eventIdMatch ? eventIdMatch[1] : null;
}

function extractSelectOptions(html, fieldName) {
    if (typeof html !== 'string' || !html) {
        return [];
    }

    const selectMatch = html.match(
        new RegExp(`<select[^>]*name="${fieldName}"[^>]*>([\\s\\S]*?)<\\/select>`, 'i')
    );
    if (!selectMatch || !selectMatch[1]) {
        return [];
    }

    const values = [];
    const optionRegex = /<option[^>]*value="([^"]*)"[^>]*>/gi;
    let optionMatch = optionRegex.exec(selectMatch[1]);
    while (optionMatch !== null) {
        const value = (optionMatch[1] || '').trim();
        if (value) {
            values.push(value);
        }
        optionMatch = optionRegex.exec(selectMatch[1]);
    }

    return values;
}

function extractEventIdFromListHtml(html) {
    if (typeof html !== 'string' || !html) {
        return null;
    }

    const anchorRegex = /<a\b[^>]*\bdata-cy="card-event-(?:first|\d+)"[^>]*>/gi;
    let anchorMatch = anchorRegex.exec(html);
    while (anchorMatch !== null) {
        const anchorTag = anchorMatch[0] || '';
        const hrefMatch = anchorTag.match(/\bhref="([^"]+)"/i);
        const href = hrefMatch && hrefMatch[1] ? hrefMatch[1] : '';
        const pathMatch = href.match(/\/event\/([^/?#"]+)/i);
        const candidateId = pathMatch && pathMatch[1] ? pathMatch[1].trim() : '';
        if (candidateId) {
            return candidateId;
        }
        anchorMatch = anchorRegex.exec(html);
    }

    return null;
}

function resolveEventIdFromList() {
    for (let attempt = 1; attempt <= SETUP_RETRIES; attempt++) {
        const listResponse = http.get(`${BASE_URL}/event/list`, {
            timeout: REQUEST_TIMEOUT,
            responseType: 'text',
        });
        const listBody = typeof listResponse.body === 'string' ? listResponse.body : '';
        const eventId = extractEventIdFromListHtml(listBody);

        const listOk = check(listResponse, {
            'GET event list status 200': (r) => r.status === 200,
            'Event ID dari list tersedia': () => !!eventId,
        });

        if (listOk && eventId) {
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
    return http.post(
        `${BASE_URL}/event/store`,
        payload,
        {
            redirects: 0,
            timeout: REQUEST_TIMEOUT,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                Referer: formUrl,
            },
        }
    );
}

export function setup() {
    const eventId = EVENT_ID_FROM_ENV || resolveEventIdFromList();
    if (!eventId) {
        throw new Error('event_id valid tidak ditemukan. Set env EVENT_ID dari database/URL event yang aktif.');
    }

    return { eventId };
}

export default function (setupData) {
    const eventId = setupData && setupData.eventId ? setupData.eventId : EVENT_ID_FROM_ENV;
    check({ eventId }, {
        'Event ID tersedia': () => !!eventId,
    });

    if (!eventId) {
        sleep(1);
        return;
    }

    const formUrl = `${BASE_URL}/event/presensi/${eventId}`;
    const formResponse = http.get(formUrl, {
        timeout: REQUEST_TIMEOUT,
        responseType: 'text',
    });

    const formBody = typeof formResponse.body === 'string' ? formResponse.body : '';
    const csrfToken = extractCsrfToken(formBody);
    const hiddenEventId = extractHiddenEventId(formBody) || eventId;

    const formOk = check(formResponse, {
        'GET form presensi event status 200': (r) => r.status === 200,
        'CSRF token ditemukan': () => !!csrfToken,
        'Hidden event_id ditemukan': () => !!hiddenEventId,
    });

    if (!formOk || !csrfToken || !hiddenEventId) {
        sleep(1);
        return;
    }

    const peranOptions = extractSelectOptions(formBody, 'peran');
    const transportasiOptions = extractSelectOptions(formBody, 'transportasi');
    const suffix = buildSuffix();

    const payload = {
        _token: csrfToken,
        event_id: hiddenEventId,
        nama: `Stress Event ${suffix}`,
        jenis_kelamin: pick(JENIS_KELAMIN),
        nomor_telepon: `08${suffix.slice(-10)}`,
        email: `stress.event.${suffix}@example.com`,
        institusi: `Instansi Stress ${suffix.slice(-6)}`,
        peran: pick(peranOptions.length > 0 ? peranOptions : DEFAULT_PERAN),
        transportasi: pick(transportasiOptions.length > 0 ? transportasiOptions : DEFAULT_TRANSPORTASI),
    };

    let submitResponse = postPresensiEvent(formUrl, payload);
    if (submitResponse.status === 419) {
        const refreshResponse = http.get(formUrl, {
            timeout: REQUEST_TIMEOUT,
            responseType: 'text',
        });
        const refreshBody = typeof refreshResponse.body === 'string' ? refreshResponse.body : '';
        const refreshedToken = extractCsrfToken(refreshBody);

        check(refreshResponse, {
            'GET refresh form status 200': (r) => r.status === 200,
            'Refresh CSRF token ditemukan': () => !!refreshedToken,
        });

        if (refreshedToken) {
            payload._token = refreshedToken;
            submitResponse = postPresensiEvent(formUrl, payload);
        }
    }

    const locationHeader = getLocationHeader(submitResponse.headers);
    check(submitResponse, {
        'POST presensi event status redirect': (r) => r.status === 302 || r.status === 303,
        'Redirect ke halaman sukses': () =>
            typeof locationHeader === 'string' && locationHeader.includes('/sukses/'),
    });

    sleep(1);
}
