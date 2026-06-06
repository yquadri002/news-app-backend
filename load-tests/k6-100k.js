import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '5m', target: 10000 },
    { duration: '10m', target: 50000 },
    { duration: '15m', target: 100000 },
    { duration: '5m', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(99)<1000'],
    http_req_failed: ['rate<0.03'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';

export default function () {
  const scenario = Math.random();

  if (scenario < 0.5) {
    const res = http.get(`${BASE_URL}/api/v1/news/feed`);
    check(res, { 'feed ok': (r) => r.status === 200 });
  } else if (scenario < 0.75) {
    const res = http.get(`${BASE_URL}/api/v1/news/trending`);
    check(res, { 'trending ok': (r) => r.status === 200 });
  } else if (scenario < 0.9) {
    const res = http.get(`${BASE_URL}/api/v1/news/article/1`);
    check(res, { 'article ok': (r) => r.status === 200 || r.status === 404 });
  } else {
    const res = http.get(`${BASE_URL}/health`);
    check(res, { 'health ok': (r) => r.status === 200 });
  }

  sleep(Math.random() * 2);
}
