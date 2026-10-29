<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Darna API

A Laravel 12 API-only real estate marketplace.

---

## Redis Caching

### Overview

Three categories of property data are cached to reduce database load:

| Category | Description | Cache key | TTL |
|---|---|---|---|
| Property detail | Single property with all relationships | `properties:detail:{id}` | 1800 s (30 min) |
| Featured properties | Top 6 publicly available properties, ordered by approval date | `properties:featured` | 900 s (15 min) |
| Governorate stats | Available-property counts grouped by governorate | `properties:stats:by-governorate` | 600 s (10 min) |

All key strings are defined as typed helpers in `app/Cache/PropertyCacheKeys.php`.  
TTL constants live in the same file and are the single source of truth.

---

### Cache driver

The default cache store is `database` (set by `CACHE_STORE=database` in `.env`).  
The application code uses `Cache::remember()` / `Cache::forget()` through Laravel's
driver-agnostic cache facade — **no driver-specific API is used** — so the same code
works with `database`, `file`, or `redis` stores without modification.

---

### Switching to Redis

#### Prerequisites

Redis requires **one** of the following PHP clients (neither is installed by default):

| Client | Install command |
|---|---|
| **phpredis** (recommended, C extension) | `pecl install redis` then enable `extension=redis` in `php.ini` |
| **predis** (pure PHP, no extension needed) | `composer require predis/predis` |

The `.env` already contains the correct Redis connection defaults:

```ini
REDIS_CLIENT=phpredis   # change to "predis" if using the Composer package
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

#### Enabling Redis as the cache store

Set the following in `.env`:

```ini
CACHE_STORE=redis
```

Then clear all application caches:

```bash
php artisan optimize:clear
```

#### Verifying Redis connectivity (optional)

```bash
php artisan tinker --execute="Cache::store('redis')->put('ping', 'pong', 10); echo Cache::store('redis')->get('ping');"
```

Expected output: `pong`

> **Note:** Redis connectivity on this machine is **not yet verified**.
> The `phpredis` extension is not loaded and `predis/predis` is not installed.
> The application falls back to the `database` store until one of the clients
> above is installed.

---

### Cache-tag support

Cache tags require Redis or Memcached.  The current implementation does **not**
use tags because the `database` store (the active default) does not support them.
Instead, each cache key is deleted individually via `Cache::forget()`.

If Redis is enabled in future and tags are desired, the key helpers in
`PropertyCacheKeys` can be extended to include tag-based operations.

---

### Invalidation rules

Invalidation happens **inside `PropertyService`**, immediately after a successful
database mutation.  No stale data is left in the cache after a failed operation.

| Event | Detail key | Featured | Governorate stats |
|---|---|---|---|
| `create()` | — (no prior entry) | ✓ flushed | ✓ flushed |
| `update()` | ✓ flushed | ✓ flushed | ✓ flushed |
| `destroy()` | ✓ flushed | ✓ flushed | ✓ flushed |
| `approve()` | ✓ flushed | ✓ flushed | ✓ flushed |
| `reject()` | ✓ flushed | ✓ flushed | ✓ flushed |

The featured and governorate-stats keys are always flushed together because any
status or governorate change can affect both.

---

### Authorization and caching

The property-detail cache stores **model-level data** (the Eloquent model with
eager-loaded relationships), not user-specific JSON.  The visibility/authorization
check in `PropertyService::show()` runs **before** the cache is consulted, so:

- A guest who requests a pending property still receives `404` — the cache entry
  is never served to them.
- The cache entry is populated once and shared across all authorised callers.
- Contact-phone and other fields are included in the cached model.  Visibility
  rules (e.g. hiding fields from certain roles) are enforced by the API Resource
  layer at response time, not at cache time.

---

### New endpoints added by this implementation

| Method | URI | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/properties/featured` | None | Top 6 available properties (cached) |
| `GET` | `/api/v1/properties/stats/by-governorate` | None | Available count per governorate (cached) |

Response shape for `/stats/by-governorate`:

```json
{
  "data": [
    { "governorate_id": 1, "total": 12 },
    { "governorate_id": 2, "total": 4 }
  ]
}
```

---

### Manually clearing cache entries

Clear everything (config + routes + application cache):

```bash
php artisan optimize:clear
```

Clear only the application cache:

```bash
php artisan cache:clear
```

Clear a single property-detail entry (replace `{id}` with the property ID):

```bash
php artisan tinker --execute="Cache::forget('properties:detail:{id}');"
```

Clear featured and governorate-stats:

```bash
php artisan tinker --execute="Cache::forget('properties:featured'); Cache::forget('properties:stats:by-governorate');"
```

> **Note:** The cache prefix configured in `config/cache.php` (`laravel-cache-` by
> default) is prepended automatically by Laravel.  The key strings above are the
> application-level keys — you do not need to include the prefix manually when
> using the `Cache` facade.

---

### Limitations and assumptions

- Redis is configured in `.env` but **not currently usable** on this machine
  because neither `phpredis` nor `predis` is installed.  All caching currently
  uses the `database` store.
- The "featured" concept uses `status = available AND approved_at IS NOT NULL`,
  ordered by `approved_at DESC`.  No dedicated `is_featured` database column
  exists or is required.
- The paginated property listing (`GET /api/v1/properties`) is **not cached**
  because the combination of user role, filter parameters, and page number
  produces too many unique key variations to cache effectively.
- Cache-aside (lazy population) is used throughout.  The first request after
  invalidation always hits the database.
