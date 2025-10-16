<?php

namespace App\Http\Controllers;

use App\Enums\LinkType;
use App\Models\Link;
use App\Models\LinkGroup;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LinkController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * List links for current user's catalog.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            $links = Link::where('catalog_id', $catalog->id)
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $links->map(fn ($l) => $this->formatLink($l))->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil tautan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new link.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|min:1|max:150',
                'url' => 'required|url|max:500',
                'type' => 'sometimes|string|in:' . implode(',', LinkType::values()),
                'icon' => 'sometimes|nullable|string|max:50',
                'is_active' => 'sometimes|boolean',
                'link_group_id' => 'sometimes|nullable|integer|exists:link_groups,id',
                'thumbnail' => 'sometimes|file|image|mimes:jpeg,jpg,png,webp|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            // Ensure group (if provided) belongs to the same catalog
            $groupId = $data['link_group_id'] ?? null;
            if ($groupId) {
                $group = LinkGroup::where('id', $groupId)->where('catalog_id', $catalog->id)->first();
                if (!$group) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Grup tautan tidak valid',
                    ], 403);
                }
            }

            $link = new Link();
            $link->catalog_id = $catalog->id;
            $link->link_group_id = $groupId;
            $link->title = $data['title'];
            $link->url = $data['url'];
            $link->type = $data['type'] ?? LinkType::GENERAL->value;
            $link->icon = $data['icon'] ?? null;
            $link->is_active = $data['is_active'] ?? true;
            $link->sort_order = $data['sort_order'] ?? 0;

            if ($request->hasFile('thumbnail')) {
                $images = $this->imageService->uploadLinkThumbnail($request->file('thumbnail'));
                $link->thumbnail_webp = $images['webp'] ?? null;
                $link->thumbnail_jpg = $images['jpg'] ?? null;
            }

            $link->save();

            return response()->json([
                'success' => true,
                'message' => 'Tautan dibuat',
                'data' => $this->formatLink($link),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tautan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update link by id.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;
            $link = Link::where('id', $id)->where('catalog_id', $catalog->id)->first();
            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tautan tidak ditemukan',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|min:1|max:150',
                'url' => 'sometimes|required|url|max:500',
                'type' => 'sometimes|string|in:' . implode(',', LinkType::values()),
                'icon' => 'sometimes|nullable|string|max:50',
                'is_active' => 'sometimes|boolean',
                'link_group_id' => 'sometimes|nullable|integer|exists:link_groups,id',
                'thumbnail' => 'sometimes|file|image|mimes:jpeg,jpg,png,webp|max:10240',
                'sort_order' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if (isset($data['link_group_id'])) {
                if ($data['link_group_id']) {
                    $groupValid = LinkGroup::where('id', $data['link_group_id'])->where('catalog_id', $catalog->id)->exists();
                    if (!$groupValid) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Grup tautan tidak valid',
                        ], 403);
                    }
                }
                $link->link_group_id = $data['link_group_id'];
            }

            foreach (['title', 'url', 'type', 'icon'] as $field) {
                if (array_key_exists($field, $data)) {
                    $link->{$field} = $data[$field];
                }
            }

            if (array_key_exists('is_active', $data)) {
                $link->is_active = (bool) $data['is_active'];
            }

            if (array_key_exists('sort_order', $data)) {
                $link->sort_order = (int) $data['sort_order'];
            }

            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnails
                if ($link->thumbnail_webp || $link->thumbnail_jpg) {
                    $this->imageService->deleteImage($link->thumbnail_webp, $link->thumbnail_jpg);
                }
                $images = $this->imageService->uploadLinkThumbnail($request->file('thumbnail'));
                $link->thumbnail_webp = $images['webp'] ?? null;
                $link->thumbnail_jpg = $images['jpg'] ?? null;
            }

            $link->save();

            return response()->json([
                'success' => true,
                'message' => 'Tautan diperbarui',
                'data' => $this->formatLink($link),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tautan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Reorder links using ordered list of IDs.
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;

            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|distinct',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            // Ensure all links belong to the user's catalog
            $links = Link::whereIn('id', $ids)->where('catalog_id', $catalog->id)->get(['id']);
            if ($links->count() !== count($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tautan tidak valid untuk penyusunan ulang',
                ], 403);
            }

            // Apply new order
            foreach ($ids as $index => $id) {
                Link::where('id', $id)->update(['sort_order' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Urutan tautan diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a link by id.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;
            $link = Link::where('id', $id)->where('catalog_id', $catalog->id)->first();
            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tautan tidak ditemukan',
                ], 404);
            }

            // Delete thumbnails if exist
            if ($link->thumbnail_webp || $link->thumbnail_jpg) {
                $this->imageService->deleteImage($link->thumbnail_webp, $link->thumbnail_jpg);
            }

            $link->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tautan dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tautan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    protected function formatLink(Link $link): array
    {
        return [
            'id' => $link->id,
            'title' => $link->title,
            'url' => $link->url,
            'type' => $link->type,
            'icon' => $link->icon,
            'is_active' => $link->is_active,
            'sort_order' => $link->sort_order,
            'link_group_id' => $link->link_group_id,
            'thumbnail' => [
                'webp' => $this->imageService->getImageUrl($link->thumbnail_webp),
                'jpg' => $this->imageService->getImageUrl($link->thumbnail_jpg),
            ],
            'created_at' => $link->created_at,
            'updated_at' => $link->updated_at,
        ];
    }
}

