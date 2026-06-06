# Load Testing

Simulates traffic at 10k, 50k, and 100k concurrent users using [k6](https://k6.io/).

## Prerequisites

```bash
# Install k6
# macOS: brew install k6
# Linux: https://k6.io/docs/get-started/installation/
```

## Run Tests

```bash
# 10k users (ramp to 10,000 VUs)
k6 run load-tests/k6-10k.js

# 50k users
k6 run load-tests/k6-50k.js

# 100k users (requires production-scale infrastructure)
k6 run load-tests/k6-100k.js

# Against staging
BASE_URL=https://staging.newshub.pro k6 run load-tests/k6-10k.js
```

## Thresholds

| Test | Target VUs | p95 Latency | Error Rate |
|------|-----------|-------------|------------|
| 10k  | 10,000    | < 500ms     | < 1%       |
| 50k  | 50,000    | < 800ms     | < 2%       |
| 100k | 100,000   | < 1000ms    | < 3%       |

## Infrastructure Requirements

| Scale | App Replicas | Horizon Workers | Redis | MySQL |
|-------|-------------|-----------------|-------|-------|
| 10k DAU | 2 | 10 | 1GB | db.r6g.large |
| 50k DAU | 4 | 20 | 2GB | db.r6g.xlarge |
| 100k DAU | 8+ | 30+ | 4GB cluster | db.r6g.2xlarge + read replica |
