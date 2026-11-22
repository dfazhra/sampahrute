import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Custom Metrics
const failureRate = new Rate('failed_requests_rate');
const responseTimeTrend = new Trend('custom_response_time');

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  stages: [
    { duration: '30s', target: 50 },   // Pemanasan cepat ke 50 VUs
    { duration: '1m',  target: 100 },  // Tekanan sedang 100 VUs
    { duration: '1m',  target: 200 },  // Tekanan berat 200 VUs
    { duration: '1m',  target: 300 },  // Tekanan ekstrim / puncak 300 VUs
    { duration: '1m',  target: 0 },    // Pendinginan dan uji recovery ke 0 VUs
  ],
  thresholds: {
    // Di bawah kondisi stress ekstrem, toleransi kegagalan maksimum 5%
    failed_requests_rate: ['rate<0.05'],
    // 90% request di bawah 1500ms, 95% di bawah 2500ms
    http_req_duration: ['p(90)<1500', 'p(95)<2500'],
  },
};

export default function () {
  const params = {
    headers: {
      'User-Agent': 'k6-stress-test/1.0',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    },
    timeout: '10s',
  };

  // Kumpulan endpoint yang sering diakses bersamaan
  const endpoints = [
    { url: `${BASE_URL}/`, name: 'Home' },
    { url: `${BASE_URL}/peta`, name: 'Peta' },
    { url: `${BASE_URL}/lokasi`, name: 'Lokasi' },
    { url: `${BASE_URL}/jadwal`, name: 'Jadwal' },
    { url: `${BASE_URL}/login`, name: 'Login_Page' },
  ];

  // Acak atau kunjungi batch endpoint untuk mensimulasikan lalu lintas acak yang berat
  for (const ep of endpoints) {
    const res = http.get(ep.url, params);
    responseTimeTrend.add(res.timings.duration);

    const success = check(res, {
      [`${ep.name} status 200`]: (r) => r.status === 200,
    });

    failureRate.add(!success);

    // Jeda singkat (0.2s - 0.5s) antar request untuk memicu beban tinggi
    sleep(0.3);
  }
}
