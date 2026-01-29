<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Locale\StoreLocaleRequest;
use App\Http\Requests\Locale\UpdateLocaleRequest;
use App\Models\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocaleController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min($perPage, 100));

        $paginator = Locale::query()
            ->orderBy('code')
            ->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreLocaleRequest $request): JsonResponse
    {
        $locale = Locale::create($request->validated());

        return response()->json([
            'data' => $locale,
        ], 201);
    }

    public function show(Locale $locale): JsonResponse
    {
        return response()->json([
            'data' => $locale,
        ]);
    }

    public function update(UpdateLocaleRequest $request, Locale $locale): JsonResponse
    {
        $locale->fill($request->validated());
        $locale->save();

        return response()->json([
            'data' => $locale->fresh(),
        ]);
    }

    public function destroy(Locale $locale): JsonResponse
    {
        $locale->delete();

        return response()->json(null, 204);
    }
}

