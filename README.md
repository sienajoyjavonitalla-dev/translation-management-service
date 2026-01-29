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

API documentation will be available at `/api/documentation` once OpenAPI/Swagger is configured (Phase 8).

## Development Phases

This project is being built in phases:

- ✅ Phase 1: Foundation (Laravel setup, Docker, PSR-12)
- ⏳ Phase 2: Database Schema and Models
- ⏳ Phase 3: Locales and Tags API
- ⏳ Phase 4: Translations CRUD and Search
- ⏳ Phase 5: Export and Performance
- ⏳ Phase 6: Authentication and Security
- ⏳ Phase 7: Scalability and 100k+ Seeder
- ⏳ Phase 8: OpenAPI, CDN, README
- ⏳ Phase 9: Testing and Coverage

## License

MIT
