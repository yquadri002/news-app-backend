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
php artisan queue:work redis --queue=rss,ingestion,notifications
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

## Phase 9 — News Ingestion & Content Engine

### Pipeline Architecture

```
RSS Sources → FetchRssFeedsJob → ProcessArticleJob
  → Article Processing (HTML cleanup, images, metadata)
  → Category Assignment (AI keyword scoring)
  → Duplicate Detection (title/content similarity)
  → Breaking News Detection → Trending Engine
```

### Public News APIs

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/news/feed` | Personalized news feed |
| GET | `/api/v1/news/trending` | Velocity-ranked trending articles |
| GET | `/api/v1/news/breaking` | Breaking news |
| GET | `/api/v1/news/latest` | Latest articles |
| GET | `/api/v1/news/category/{id}` | Category feed |
| GET | `/api/v1/news/article/{id}` | Article detail |
| GET | `/api/v1/news/search?q=` | Full-text search |

### Admin Ingestion APIs

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/news/articles` | All articles with filters |
| GET | `/api/v1/admin/news/moderation/pending` | Pending moderation |
| POST | `/api/v1/admin/news/moderation/{id}/approve` | Approve article |
| POST | `/api/v1/admin/news/moderation/{id}/reject` | Reject article |
| GET | `/api/v1/admin/news/duplicates` | Duplicate review |
| GET | `/api/v1/admin/feeds/dashboard` | Feed monitoring dashboard |
| GET | `/api/v1/admin/feeds/logs` | Fetch logs |
| GET | `/api/v1/admin/feeds/source-performance` | Source performance |
| POST | `/api/v1/admin/feeds/{sourceId}/fetch` | Trigger manual fetch |

### Content Engine Tables

| Table | Purpose |
|-------|---------|
| `article_images` | Extracted images per article |
| `article_categories` | AI-assigned categories with confidence |
| `article_tags` | Auto-extracted tags |
| `article_metrics` | Trending, velocity, engagement scores |
| `feed_fetch_logs` | RSS fetch audit trail |

### Background Jobs

| Job | Queue | Purpose |
|-----|-------|---------|
| `FetchRssFeedsJob` | `rss` | Fetch all due RSS sources |
| `FetchRssSourceJob` | `rss` | Fetch single source with retry |
| `ProcessArticleJob` | `ingestion` | Full article processing pipeline |
| `DetectDuplicatesJob` | `ingestion` | Cross-feed duplicate merging |
| `DetectBreakingNewsJob` | `ingestion` | Breaking news scoring |
| `CalculateTrendingJob` | `ingestion` | Trending score calculation |

## Phase 10 — AI Recommendation & Personalization Engine

### Personalized Feed Types
| Feed | Endpoint | Description |
|------|----------|-------------|
| For You | `GET /api/v1/recommendations/feed` | Personalized ranked feed |
| Following | `GET /api/v1/recommendations/following` | Followed sources/categories |
| Trending | `GET /api/v1/recommendations/trending` | Velocity-ranked trending |
| Breaking | `GET /api/v1/recommendations/breaking` | Breaking news |
| Local | `GET /api/v1/recommendations/local` | Location-based news |

### Behavior Tracking
`POST /api/v1/recommendations/behavior` — article open, read time, scroll depth, bookmark, share, search, category/source open

### Feedback Loop
`POST /api/v1/recommendations/feedback` — click/read/bookmark/share feedback for recommendation accuracy

### Ranking Algorithm Weights
| Factor | Default Weight |
|--------|---------------|
| User Interests | 30% |
| Trending Score | 20% |
| Breaking Score | 10% |
| Freshness | 15% |
| Engagement | 15% |
| Source Quality | 10% |

### Recommendation Tables
`user_behavior_events`, `user_interest_profiles`, `user_category_scores`, `user_source_scores`, `user_topic_scores`, `recommendation_logs`, `user_segment_memberships`

### Background Jobs
| Job | Schedule |
|-----|----------|
| `CalculateInterestProfilesJob` | Hourly |
| `GenerateRecommendationsJob` | Every 15 min |
| `RefreshTrendingScoresJob` | Every 10 min |
| `GenerateUserSegmentsJob` | Daily |

## Phase 11 — Enterprise Notification Intelligence

### Intelligent Targeting Factors
| Factor | Weight | Description |
|--------|--------|-------------|
| User Interests | 35% | Category/source scores and preferences |
| User Segments | 20% | Segment keyword and confidence matching |
| Read History | 10% | Suppress already-read; boost familiar categories |
| Location | 10% | Geo-relevant article matching |
| Language | 10% | User language alignment |
| Freshness | 15% | Recency decay scoring |

### Notification Types
`manual`, `breaking`, `digest`, `recommendation`, `automated`

### Fatigue Protection
- Daily frequency caps per user
- Quiet hours (timezone-aware)
- Cooldown periods between sends
- Sensitivity scoring based on open rates

### Digest Schedule
| Digest | Default Hour |
|--------|-------------|
| Morning | 08:00 |
| Afternoon | 13:00 |
| Evening | 19:00 |

### Notification Intelligence APIs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/notifications/recommendations` | Pending AI notification recommendations |
| POST | `/api/v1/notifications/send` | Send recommendation/breaking/digest/segment notification |
| POST | `/api/v1/notifications/test` | Send test push to user or FCM token |
| GET | `/api/v1/notifications/analytics` | Delivery, open, CTR, conversion, retention metrics |
| POST | `/api/v1/notifications/analytics/snapshot` | Calculate daily analytics snapshot |
| POST | `/api/v1/client/notifications/open` | Track notification open (client, auth required) |

### Intelligence Tables
`notification_user_states`, `notification_recommendations`, `notification_digests`, `notification_ab_tests`, `notification_analytics_snapshots`

### Background Jobs
| Job | Schedule | Purpose |
|-----|----------|---------|
| `GenerateNotificationRecommendationsJob` | Every 15 min | Score users and send due recommendations |
| `GenerateDigestJob` | Hourly | Generate and send morning/afternoon/evening digests |
| `SendSegmentNotificationsJob` | Every 30 min | Segment-targeted article pushes |
| `AnalyzeNotificationPerformanceJob` | Daily | Snapshot delivery/open/CTR analytics |
| `ProcessBreakingNotificationJob` | On detection | Auto-push breaking news to targeted users |

## Scheduled Tasks

| Command | Schedule | Purpose |
|---------|----------|---------|
| `notifications:process-scheduled` | Every minute | Dispatch scheduled push notifications |
| `rss:monitor-health` | Hourly | Validate RSS source health |
| `rss:fetch` | Every 5 minutes | Fetch due RSS feeds |
| `news:detect-breaking` | Every 15 minutes | Breaking news detection |
| `CalculateTrendingJob` | Every 10 minutes | Recalculate trending scores |

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
