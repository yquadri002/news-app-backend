# NewsHub Pro — Deployment Guide

## Environments

| Environment | URL | Branch | Purpose |
|-------------|-----|--------|---------|
| Local | http://localhost:8080 | feature branches | Development |
| Staging | https://staging.newshub.pro | `main` | Pre-production validation |
| Production | https://api.newshub.pro | tagged releases | Live traffic |

## Local Development (Docker)

```bash
# Start all services
docker compose up -d

# Install dependencies
docker compose exec app composer install

# Run migrations and seed
docker compose exec app php artisan migrate --seed

# Access
# API:     http://localhost:8080
# Horizon: http://localhost:8080/horizon
# Pulse:   http://localhost:8080/pulse
# Health:  http://localhost:8080/health
```

### Services

| Container | Purpose |
|-----------|---------|
| `app` | PHP-FPM application |
| `nginx` | Web server / reverse proxy |
| `mysql` | Database |
| `redis` | Cache, sessions, queues |
| `horizon` | Queue worker (Laravel Horizon) |
| `scheduler` | Cron scheduler |
| `pulse` | Application metrics |

## Staging Deployment

```bash
# Build production image
docker build -f docker/php/Dockerfile.prod -t newshub-pro:staging .

# Deploy with production compose
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Cache config
docker compose -f docker-compose.prod.yml exec app php artisan optimize
```

### Staging Checklist

- [ ] Environment variables configured (`.env.production`)
- [ ] Database migrated
- [ ] Redis connected
- [ ] Horizon running
- [ ] Health check passing (`/health`)
- [ ] Sentry DSN configured
- [ ] SSL certificate active

## Production Deployment

### Pre-deploy

```bash
php artisan down --refresh=15
php artisan backup:database
```

### Deploy

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan horizon:terminate  # graceful worker restart
php artisan up
```

### Post-deploy Verification

```bash
curl -f https://api.newshub.pro/health
curl -f https://api.newshub.pro/up
php artisan infrastructure:monitor
```

### Rollback

```bash
php artisan down
git checkout <previous-tag>
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback --step=1  # if needed
php artisan optimize
php artisan horizon:terminate
php artisan up
```

## Environment Variables

See `.env.example` for all variables. Critical production settings:

```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
HORIZON_PREFIX=newshub_horizon
SENTRY_LARAVEL_DSN=https://...
ALERTING_ENABLED=true
BACKUP_ENABLED=true
CDN_ENABLED=true
```

## Scaling Guide

### Target: 100k DAU / 1M MAU

| Component | Configuration |
|-----------|--------------|
| App servers | 4-8 replicas behind load balancer |
| Horizon | 30 max processes across supervisors |
| Redis | Cluster mode, 4GB+ memory |
| MySQL | Primary + read replica |
| CDN | CloudFront/Cloudflare for static assets |
| S3 | Media storage with signed URLs |

### Queue Priority

1. `high` — critical real-time jobs
2. `notifications` — push notifications
3. `rss` — feed fetching
4. `ingestion` — article processing
5. `analytics` / `recommendations` — background analytics
6. `default` — everything else

## Disaster Recovery

1. **Database**: Daily automated backups to S3 (`backup:database`)
2. **Point-in-time recovery**: Enable MySQL binlog on production
3. **RTO**: 30 minutes | **RPO**: 1 hour
4. **Failover**: Promote read replica if primary fails
5. **Recovery test**: Monthly restore drill to staging
