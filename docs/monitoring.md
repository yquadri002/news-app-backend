# Monitoring & Alerting

## Health Checks

| Endpoint | Purpose |
|----------|---------|
| `GET /up` | Laravel built-in health check |
| `GET /health` | Extended health (DB, Redis, queue, storage) |
| `GET /health/metrics` | Application and queue metrics |

## Laravel Horizon

Queue monitoring dashboard at `/horizon`.

- Supervisor auto-scaling per queue priority
- Failed job tracking and retry
- Queue wait time alerts
- Metrics snapshots via `horizon:snapshot` (scheduled every 5 min)

## Laravel Pulse

Real-time application metrics at `/pulse`.

- Server CPU and memory
- Slow queries and requests
- Queue throughput
- Cache performance

## Laravel Telescope

Debug dashboard (staging/local only) at `/telescope`.

- Request/query/job inspection
- Exception tracking
- Scheduled task monitoring

## Sentry

Error tracking and performance monitoring.

```env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project
SENTRY_TRACES_SAMPLE_RATE=0.2
```

## Alerting

Infrastructure alerts via `php artisan infrastructure:monitor` (every 5 min).

| Alert | Threshold | Default |
|-------|-----------|---------|
| CPU | `ALERT_CPU_THRESHOLD` | 85% |
| Memory | `ALERT_MEMORY_THRESHOLD` | 85% |
| Queue backlog | `ALERT_QUEUE_BACKLOG` | 1000 jobs |
| DB connections | `ALERT_DB_CONNECTIONS` | 80% |
| Notification failures | `ALERT_NOTIFICATION_FAILURE_RATE` | 10% |

Configure notification channels:

```env
ALERTING_ENABLED=true
ALERT_SLACK_WEBHOOK=https://hooks.slack.com/...
ALERT_EMAIL=ops@newshub.pro
```

## Scheduled Monitoring Tasks

| Task | Schedule |
|------|----------|
| `horizon:snapshot` | Every 5 minutes |
| `infrastructure:monitor` | Every 5 minutes |
| `backup:database` | Daily at 02:00 |
| `pulse:check` | Continuous (Pulse container) |
