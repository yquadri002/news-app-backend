# NewsHub Pro — Backend API

Enterprise-grade Laravel backend for the NewsHub Pro news application. Built with Repository + Service layer patterns, Sanctum authentication, Redis queues, and Firebase Cloud Messaging.

## Stack

| Component | Technology |
|-----------|------------|
| Framework | Laravel 12+ |
| Database | MySQL |
| Auth | Laravel Sanctum |
| Queue | Redis |
| Push Notifications | Firebase Cloud Messaging |
| Architecture | Repository Pattern + Service Layer |

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure MySQL and Redis in .env, then:
php artisan migrate --seed
php artisan queue:work redis --queue=notifications
php artisan schedule:work
```

**Default Admin:** `admin@newshub.pro` / `password`

## Folder Structure

```
app/
├── Console/Commands/          # Scheduled jobs (notifications, RSS health)
├── Enums/                     # Domain enums (permissions, statuses)
├── Http/
│   ├── Controllers/Api/
│   │   ├── Admin/             # Admin panel APIs
│   │   └── Client/            # Mobile app APIs
│   ├── Middleware/            # Admin auth, permissions, activity tracking
│   ├── Requests/              # Form request validation
│   └── Resources/             # API response transformers
├── Jobs/                      # Queue jobs (FCM push)
├── Models/                    # Eloquent models
├── Policies/                  # Authorization policies
├── Repositories/
│   ├── Contracts/             # Repository interfaces
│   └── *.php                  # Repository implementations
└── Services/                  # Business logic layer
```

## API Endpoints

### Admin Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/admin/auth/login` | Admin login |
| POST | `/api/v1/admin/auth/logout` | Admin logout |
| GET | `/api/v1/admin/auth/me` | Current admin profile |
| POST | `/api/v1/admin/auth/forgot-password` | Request password reset |
| POST | `/api/v1/admin/auth/reset-password` | Reset password |

### Dashboard
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/dashboard` | Total users, active users, articles opened, notifications sent, revenue, source performance |

### Category Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/v1/admin/categories` | List / Create |
| GET/PUT/DELETE | `/api/v1/admin/categories/{id}` | Show / Update / Delete |
| POST | `/api/v1/admin/categories/sort-order` | Update sort order |
| PATCH | `/api/v1/admin/categories/{id}/toggle` | Enable / Disable |

### RSS Source Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/v1/admin/rss-sources` | List / Add source |
| DELETE | `/api/v1/admin/rss-sources/{id}` | Remove source |
| PATCH | `/api/v1/admin/rss-sources/{id}/priority` | Set priority |
| POST | `/api/v1/admin/rss-sources/{id}/validate` | Auto validation |
| GET | `/api/v1/admin/rss-sources-health` | Health monitoring |

### Breaking News Engine
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/admin/breaking-news/articles/{id}/mark` | Mark breaking |
| POST | `/api/v1/admin/breaking-news/articles/{id}/push/all` | Push to all users |
| POST | `/api/v1/admin/breaking-news/articles/{id}/push/categories` | Push to categories |
| POST | `/api/v1/admin/breaking-news/articles/{id}/push/segments` | Push to segments |

### Notification Center
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/v1/admin/notifications` | List / Create |
| POST | `/api/v1/admin/notifications/{id}/schedule` | Schedule notification |
| POST | `/api/v1/admin/notifications/{id}/send` | Send immediately |
| GET | `/api/v1/admin/notifications/{id}/analytics` | Delivery analytics |

### Advertisement Control
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/v1/admin/ads/placements` | Ad placement CRUD |
| POST | `/api/v1/admin/ads/placements/{id}/ab-tests` | A/B testing |

### App Update Center
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/v1/admin/app-versions` | Version management |
| GET | `/api/v1/client/app/check-update` | Force/soft update check |

### User Preferences (Client)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/client/device/register` | Register device + FCM token |
| GET/PUT | `/api/v1/client/preferences` | Get / update all preferences |
| PATCH | `/api/v1/client/preferences/interests` | Update interests |
| PATCH | `/api/v1/client/preferences/categories` | Update categories |
| PATCH | `/api/v1/client/preferences/sources` | Update sources |
| PATCH | `/api/v1/client/preferences/language` | Update language |
| PATCH | `/api/v1/client/preferences/location` | Update location |

### Analytics
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/analytics/overview` | Dashboard analytics |
| GET | `/api/v1/admin/analytics/categories/{id}` | Category analytics |
| GET | `/api/v1/admin/analytics/search-trends` | Search analytics |
| GET | `/api/v1/admin/analytics/retention` | User retention |
| POST | `/api/v1/client/analytics/article-view` | Track article views |
| POST | `/api/v1/client/analytics/search` | Track searches |

## Database Schema

| Table | Purpose |
|-------|---------|
| `roles` | Admin roles with JSON permissions |
| `admins` | Admin users |
| `admin_password_reset_tokens` | Password reset tokens |
| `categories` | News categories with sort order & icons |
| `rss_sources` | RSS feeds with health monitoring |
| `articles` | News articles with breaking flag |
| `users` | Mobile app users (device-based) |
| `user_preferences` | Interests, categories, sources, language, location |
| `user_segments` | Audience segments for targeted push |
| `notifications` | Push notification campaigns |
| `notification_targets` | Polymorphic notification targets |
| `notification_deliveries` | Per-user delivery tracking |
| `ad_placements` | Ad placement configuration |
| `ad_ab_tests` | A/B test variants |
| `app_versions` | Force/soft update management |
| `article_views` | Article view tracking |
| `search_analytics` | Search query analytics |
| `analytics_events` | Generic event tracking |
| `category_analytics` | Daily category metrics |
| `user_retention_snapshots` | Cohort retention data |
| `revenue_records` | Ad revenue tracking |

## Environment Variables

```env
DB_CONNECTION=mysql
DB_DATABASE=newshub_pro
QUEUE_CONNECTION=redis
CACHE_STORE=redis
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
```

## Scheduled Tasks

| Command | Schedule | Purpose |
|---------|----------|---------|
| `notifications:process-scheduled` | Every minute | Dispatch scheduled push notifications |
| `rss:monitor-health` | Hourly | Validate RSS source health |

## Role Permissions

- `dashboard.view` — View dashboard
- `categories.manage` — Category CRUD
- `sources.manage` — RSS source management
- `breaking_news.manage` — Breaking news engine
- `notifications.manage` — Notification center
- `ads.manage` — Advertisement control
- `app_updates.manage` — App version management
- `analytics.view` — Analytics access
- `roles.manage` — Role management
- `admins.manage` — Admin user management
