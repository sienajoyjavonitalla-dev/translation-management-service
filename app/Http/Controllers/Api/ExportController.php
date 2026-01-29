<?php

namespace App\Http\Controllers\Api;

use App\Services\TranslationExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExportController extends ApiController
{
    public function __construct(private readonly TranslationExportService $exportService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('locale');
        $nested = filter_var($request->query('nested', false), FILTER_VALIDATE_BOOLEAN);

        $localeModel = $this->exportService->resolveLocale(is_string($locale) ? $locale : null);
        $localeCode = $localeModel?->code ?? (is_string($locale) ? $locale : null);

        $lastModified = $this->getLastModified($localeModel?->id);
        $etag = $this->makeEtag($localeCode, $nested, $lastModified);

        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, 304, $this->headers($etag, $lastModified));
        }

        $ifModifiedSince = $request->header('If-Modified-Since');
        if ($ifModifiedSince && $lastModified !== null) {
            $since = Carbon::parse($ifModifiedSince);
            if ($since->greaterThanOrEqualTo($lastModified)) {
                return response()->json(null, 304, $this->headers($etag, $lastModified));
            }
        }

        $cacheKey = $this->cacheKey($localeCode, $nested, $lastModified?->timestamp);

        /** @var array<string, mixed> $payload */
        $payload = $this->cache()->remember($cacheKey, now()->addMinutes(10), function () use ($locale, $nested) {
            return $this->exportService->export(is_string($locale) ? $locale : null, $nested);
        });

        return response()->json($payload, 200, $this->headers($etag, $lastModified));
    }

    public static function forgetExportCache(?int $localeId = null): void
    {
        // Best-effort invalidation: bump a version key so future cache keys change.
        // We keep it simple and invalidate all export shapes/locales.
        self::cacheStatic()->increment('export.version.all');
    }

    private function cacheKey(?string $localeCode, bool $nested, ?int $lastModifiedTs): string
    {
        $localePart = $localeCode ? "locale:{$localeCode}" : 'all';
        $shape = $nested ? 'nested' : 'flat';

        // For locale-specific keys, keep the version tied to locale_id to avoid ambiguity.
        $version = $this->cache()->get('export.version.all', 1);

        return "export.v{$version}.{$localePart}.{$shape}." . ($lastModifiedTs ?? 0);
    }

    private function makeEtag(?string $localeCode, bool $nested, ?Carbon $lastModified): string
    {
        $scope = $localeCode ? "locale={$localeCode}" : 'all';
        $shape = $nested ? 'nested' : 'flat';
        $ts = $lastModified?->timestamp ?? 0;

        return sprintf('W/"%s:%s:%d"', $scope, $shape, $ts);
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $etag, ?Carbon $lastModified): array
    {
        $headers = [
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=0, must-revalidate',
        ];

        if ($lastModified) {
            $headers['Last-Modified'] = $lastModified->toRfc7231String();
        }

        return $headers;
    }

    private function getLastModified(?int $localeId): ?Carbon
    {
        $query = DB::table('translations');

        if ($localeId !== null) {
            $query->where('locale_id', $localeId);
        }

        $max = $query->max('updated_at');

        return $max ? Carbon::parse($max) : null;
    }

    private function cache()
    {
        return self::cacheStatic();
    }

    private static function cacheStatic()
    {
        // Local Windows/Laragon setups often don't have ext-redis enabled, even if CACHE_STORE=redis.
        // If the Redis extension is missing, fall back to file cache so export works for development.
        $defaultStore = (string) config('cache.default', 'file');

        if ($defaultStore === 'redis' && !class_exists('Redis')) {
            return Cache::store('file');
        }

        return Cache::store($defaultStore);
    }
}

