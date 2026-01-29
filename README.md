# Translation Management Service

A Laravel API-driven service for managing translations across multiple locales with support for tagging, searching, and high-performance JSON export.

## Features

- Multi-locale translation management (en, fr, es, and extensible)
- Tag-based organization (mobile, desktop, web, etc.)
- Full CRUD operations for translations
- Advanced search by tags, keys, or content
- High-performance JSON export endpoint for frontend applications
- Token-based authentication (Laravel Sanctum)
- Optimized for scalability (100k+ records)
- Docker support for easy development

## Requirements

- PHP 8.2+
- Composer
- Docker & Docker Compose (for containerized setup)
- MySQL 8.0+
- Redis (for caching)

## Installation

### Using Docker (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd translation-management-service
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Build and start containers:
```bash
docker-compose up -d --build
```

4. Install dependencies:
```bash
docker-compose exec app composer install
```

5. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

6. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

7. Seed initial data (optional):
```bash
docker-compose exec app php artisan db:seed
```

### Local Development (Without Docker)

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Configure your `.env` file with database credentials

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Seed initial data (locales, tags, admin user):
```bash
php artisan db:seed
```

### Scalability testing (100k+ records)

To populate the database with a large dataset for performance testing:

```bash
php artisan translations:seed-large --count=100000
```

- **Options**: `--count=100000` (default), `--chunk=2000` (bulk insert chunk size), `--no-pivot` (skip attaching tags).
- **Requires**: Locales and tags to exist (run `php artisan db:seed` first).
- **Expected time**: Typically under 5 minutes for 100k translations (depends on DB and disk).
- **Performance**: After seeding, list and export endpoints should remain within targets (<200ms list, <500ms export).

## Code Quality

### PSR-12 Compliance

This project follows PSR-12 coding standards. To check and fix code style:

```bash
# Check code style
composer lint

# Auto-fix code style issues
composer lint-fix
```

Or using PHP-CS-Fixer directly:
```bash
vendor/bin/php-cs-fixer fix
```

## Testing

Run tests:
```bash
composer test
```

Or using PHPUnit directly:
```bash
vendor/bin/phpunit
```

## API Documentation

OpenAPI 3 (Swagger) documentation is available at:

- **URL**: `/docs/` (e.g. `http://127.0.0.1:8000/docs/`)
- **Spec**: `/docs/openapi.yaml`

All endpoints (health, auth, locales, tags, translations, export) are documented with request/response examples and 401/422/500 responses.

## CDN support (export endpoint)

The **export** endpoint (`GET /api/v1/export`) is designed to be used by frontends (e.g. Vue i18n) and can be put behind a CDN.

- **Headers**: Responses include `ETag`, `Last-Modified`, and `Cache-Control: public, max-age=0, must-revalidate` so CDNs and browsers can cache and revalidate.
- **Recommendation**: Purge the export URL from your CDN when translations are updated (e.g. on deploy or via a webhook that runs after translation create/update/delete). The API invalidates its own cache on write so the next request returns fresh data; a CDN in front should be purged so edge caches don’t serve stale JSON.
- **Optional**: Set a config or env value for the “export base URL” so the frontend points to the CDN URL (e.g. `https://cdn.example.com/export?locale=en`).

## Design choices

- **Schema**: Locales, tags, and translation keys are normalized in separate tables; translations are key+locale+value with a many-to-many pivot for tags. Indexes on `(locale_id, translation_key_id)`, `updated_at`, and full-text on key/value keep list and export fast.
- **Cache**: Export responses are cached (Redis or file fallback); cache is invalidated on every translation create/update/delete so “always return updated” is satisfied.
- **SOLID**: Controllers are thin (validate → service → JSON); business logic lives in `TranslationService`, `TranslationExportService`, `TranslationSearchService`; no external CRUD/translation libraries.
- **Auth**: Laravel Sanctum token auth; protected routes use `auth:sanctum` and `throttle:60,1`; export and health remain public.

## Development Phases

This project is being built in phases:

- ✅ Phase 1: Foundation (Laravel setup, Docker, PSR-12)
- ✅ Phase 2: Database Schema and Models
- ✅ Phase 3: Locales and Tags API
- ✅ Phase 4: Translations CRUD and Search
- ✅ Phase 5: Export and Performance
- ✅ Phase 6: Authentication and Security
- ✅ Phase 7: Scalability and 100k+ Seeder
- ✅ Phase 8: OpenAPI, CDN, README
- ⏳ Phase 9: Testing and Coverage

## License

MIT
