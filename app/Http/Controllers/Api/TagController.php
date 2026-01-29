<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min($perPage, 100));

        $paginator = Tag::query()
            ->orderBy('name')
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

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = Tag::create($request->validated());

        return response()->json([
            'data' => $tag,
        ], 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json([
            'data' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $tag->fill($request->validated());
        $tag->save();

        return response()->json([
            'data' => $tag->fresh(),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}

