import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Rate } from 'k6/metrics';

// Custom Metrics
const landingDuration = new Trend('landing_page_duration');
const loginDuration = new Trend('login_duration');
const errorRate = new Rate('custom_error_rate');

// Konfigurasi Target URL (dapat dioverride via CLI: -e BASE_URL=http://...)
const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  stages: [
    { duration: '30s', target: 20 },  // Ramp-up ke 20 Virtual Users
    { duration: '1m',  target: 20 },  // Beban stabil 20 VUs
    { duration: '30s', target: 50 },  // Tingkatkan beban ke 50 VUs
    { duration: '1m',  target: 50 },  // Beban puncak normal 50 VUs
    { duration: '30s', target: 0 },   // Ramp-down pemulihan ke 0 VUs
  ],
  thresholds: {
    // 95% request harus selesai di bawah 500ms
    http_req_duration: ['p(95)<500'],
    // Tingkat kegagalan request harus di bawah 1%
    http_req_failed: ['rate<0.01'],
    custom_error_rate: ['rate<0.01'],
  },
};

// Ekstrak CSRF token dari form HTML Laravel
function extractCsrfToken(body) {
  const match = body.match(/name="_token"\s+value="([^"]+)"/);
  return match ? match[1] : null;
}

export default function () {
  const params = {
    headers: {
      'User-Agent': 'k6-load-test/1.0',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    },
    redirects: 5,
  };

  group('01. Public Browsing Flow', function () {
    // Akses Landing Page
    const resHome = http.get(`${BASE_URL}/`, params);
    landingDuration.add(resHome.timings.duration);
    const homeOk = check(resHome, {
      'Landing page status 200': (r) => r.status === 200,
      'Landing page contains SampahRute': (r) => r.body.includes('SampahRute'),
    });
    errorRate.add(!homeOk);
    sleep(1);

    // Akses Peta Monitoring
    const resMap = http.get(`${BASE_URL}/peta`, params);
    const mapOk = check(resMap, {
      'Peta status 200': (r) => r.status === 200,
    });
    errorRate.add(!mapOk);
    sleep(1);

    // Akses Titik Lokasi
    const resLoc = http.get(`${BASE_URL}/lokasi`, params);
    const locOk = check(resLoc, {
      'Lokasi status 200': (r) => r.status === 200,
    });
    errorRate.add(!locOk);
    sleep(1);

    // Akses Jadwal
    const resSchedule = http.get(`${BASE_URL}/jadwal`, params);
    const schedOk = check(resSchedule, {
      'Jadwal status 200': (r) => r.status === 200,
    });
    errorRate.add(!schedOk);
    sleep(1);
  });

  group('02. Authentication & Citizen Dashboard Flow', function () {
    // Buka Halaman Login & Ambil CSRF Token
    const resLoginPage = http.get(`${BASE_URL}/login`, params);
    const csrfToken = extractCsrfToken(resLoginPage.body);

    const loginPageOk = check(resLoginPage, {
      'Login page status 200': (r) => r.status === 200,
      'CSRF token extracted': () => csrfToken !== null,
    });
    errorRate.add(!loginPageOk);

    if (csrfToken) {
      // Kirim POST Login sebagai Warga
      const loginPayload = {
        _token: csrfToken,
        email: 'warga',
        password: 'warga123',
      };

      const resLogin = http.post(`${BASE_URL}/login`, loginPayload, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirects: 5,
      });

      loginDuration.add(resLogin.timings.duration);

      const loginOk = check(resLogin, {
        'Login successful (status 200 after redirect)': (r) => r.status === 200,
        'Redirected to dashboard': (r) => r.url.includes('/warga/dashboard') || r.body.includes('Menu Warga') || r.body.includes('Dashboard'),
      });
      errorRate.add(!loginOk);
      sleep(1);

      // Akses Halaman Laporan Warga
      const resReports = http.get(`${BASE_URL}/warga/laporan`, params);
      const reportsOk = check(resReports, {
        'Citizen reports page status 200': (r) => r.status === 200,
      });
      errorRate.add(!reportsOk);
    }

    sleep(2);
  });
}
