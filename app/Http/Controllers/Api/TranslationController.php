<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Translation\StoreTranslationRequest;
use App\Http\Requests\Translation\UpdateTranslationRequest;
use App\Models\Translation;
use App\Services\TranslationSearchService;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends ApiController
{
    public function __construct(
        private readonly TranslationService $translationService,
        private readonly TranslationSearchService $translationSearchService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 50);

        $filters = [
            'locale' => $request->query('locale'),
            'tag' => $request->query('tag'),
            'key' => $request->query('key'),
            'content' => $request->query('content'),
        ];

        $paginator = $this->translationSearchService->search($filters, $perPage);

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

    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->create($request->validated());

        return response()->json([
            'data' => $translation,
        ], 201);
    }

    public function show(Translation $translation): JsonResponse
    {
        $translation->load(['translationKey', 'locale', 'tags']);

        return response()->json([
            'data' => $translation,
        ]);
    }

    public function update(UpdateTranslationRequest $request, Translation $translation): JsonResponse
    {
        $translation = $this->translationService->update($translation, $request->validated());

        return response()->json([
            'data' => $translation,
        ]);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $this->translationService->delete($translation);

        return response()->json(null, 204);
    }
}

