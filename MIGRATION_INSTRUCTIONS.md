# Migration and Seeding Instructions

## Prerequisites

1. **Composer dependencies installed**
   - If `vendor` directory doesn't exist, run: `composer install`

2. **Database configured**
   - Ensure your `.env` file has correct database credentials
   - Database should be created and accessible

## Option 1: Using Laragon Terminal (Recommended)

1. Open **Laragon Terminal** (Laragon has PHP and Composer in PATH)

2. Navigate to project directory:
   ```bash
   cd c:\laragon\www\translation-management-service
   ```

3. Install dependencies (if not done):
   ```bash
   composer install
   ```

4. Run migrations:
   ```bash
   php artisan migrate
   ```

5. Run seeders:
   ```bash
   php artisan db:seed
   ```

## Option 2: Using Batch Script

1. Double-click `migrate-and-seed.bat` in the project root
   - The script will automatically find PHP in Laragon
   - It will run migrations and seeders

## Option 3: Using Docker

If you have Docker installed:

1. Start containers:
   ```bash
   docker-compose up -d
   ```

2. Install dependencies:
   ```bash
   docker-compose exec app composer install
   ```

3. Run migrations:
   ```bash
   docker-compose exec app php artisan migrate
   ```

4. Run seeders:
   ```bash
   docker-compose exec app php artisan db:seed
   ```

## Verification

After running migrations and seeders, verify the data:

```bash
php artisan tinker
```

Then in Tinker:
```php
// Check locales
App\Models\Locale::all();

// Check tags
App\Models\Tag::all();

// Check if relationships work
$translation = App\Models\Translation::with(['locale', 'translationKey', 'tags'])->first();
```

## Expected Output

After successful migration and seeding:

- **Locales**: 3 records (en, fr, es)
- **Tags**: 3 records (mobile, desktop, web)
- **Tables created**: locales, tags, translation_keys, translations, translation_tag

## Troubleshooting

### "vendor/autoload.php not found"
- Run `composer install` first

### "SQLSTATE[HY000] [1045] Access denied"
- Check your `.env` file database credentials
- Ensure MySQL is running in Laragon

### "SQLSTATE[HY000] [1049] Unknown database"
- Create the database first (or update `.env` with correct database name)
- Database name from `.env`: `DB_DATABASE=translation_service`

### Migration already ran
- If tables already exist, migrations will skip them
- To reset: `php artisan migrate:fresh --seed` (⚠️ This will drop all tables!)
