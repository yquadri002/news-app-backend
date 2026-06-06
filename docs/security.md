# Security

## Rate Limiting

API rate limits are enforced via `ApiRateLimiter` middleware:

| Limiter | Default | Scope |
|---------|---------|-------|
| `api` | 120/min | Authenticated API |
| `auth` | 10/min | Login/auth endpoints |
| `public` | 300/min | Public news endpoints |

## Security Headers

Applied via `SecurityHeaders` middleware on all responses:

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Strict-Transport-Security` (HTTPS only)
- `Content-Security-Policy` (production)

## DDoS Protection

1. **Nginx**: `client_max_body_size`, connection limits
2. **Rate limiting**: Per-IP and per-user throttling
3. **CDN/WAF**: Cloudflare or AWS WAF in production
4. **Horizon queue isolation**: Prevents queue flooding from affecting API

## Secrets Management

- Never commit `.env` files
- Use environment variables or secrets manager (AWS Secrets Manager, Doppler)
- Rotate `APP_KEY`, database passwords, and API keys regularly
- Firebase credentials stored outside web root

## API Protection

- Sanctum token authentication for client/admin APIs
- Permission-based admin access (`admin.permission` middleware)
- CORS configured per environment
- Input validation on all endpoints

## Production Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] HTTPS enforced
- [ ] Telescope disabled in production
- [ ] Horizon/Telescope gated to admin emails
- [ ] Sentry configured for error reporting
- [ ] Database credentials rotated
- [ ] Backup encryption enabled on S3
