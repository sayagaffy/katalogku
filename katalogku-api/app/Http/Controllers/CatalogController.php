<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get current user's catalog.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog()->with('products')->first();

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatCatalogResponse($catalog),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data katalog',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create or update catalog.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:100',
                'username' => 'required|string|min:3|max:50|regex:/^[a-z0-9_-]+$/|unique:catalogs,username,' . ($request->user()->catalog->id ?? 'NULL'),
                'description' => 'nullable|string|max:500',
                'category' => 'nullable|string|max:50',
                'whatsapp' => 'nullable|string|regex:/^(08|628|\+628)[0-9]{8,12}$/',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
                'theme' => 'nullable|string|in:default,blue,green,purple,pink',
                'is_published' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $catalog = $user->catalog;

            $data = [
                'name' => $request->input('name'),
                'username' => Str::lower($request->input('username')),
                'description' => $request->input('description'),
                'category' => $request->input('category'),
                'whatsapp' => $request->input('whatsapp', $user->whatsapp),
                'theme' => $request->input('theme', 'default'),
                'is_published' => $request->input('is_published', true),
            ];

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($catalog && $catalog->avatar) {
                    // Extract paths from JSON if stored as JSON, or use as is
                    $oldAvatar = json_decode($catalog->avatar, true);
                    if (is_array($oldAvatar)) {
                        $this->imageService->deleteImage($oldAvatar['webp'] ?? null, $oldAvatar['jpg'] ?? null);
                    }
                }

                $images = $this->imageService->uploadCatalog($request->file('avatar'));
                $data['avatar'] = json_encode($images);
            }

            if ($catalog) {
                // Update existing catalog
                $catalog->update($data);
            } else {
                // Create new catalog
                $data['user_id'] = $user->id;
                $catalog = Catalog::create($data);
            }

            return response()->json([
                'success' => true,
                'message' => $catalog->wasRecentlyCreated ? 'Katalog berhasil dibuat' : 'Katalog berhasil diperbarui',
                'data' => $this->formatCatalogResponse($catalog->fresh()),
            ], $catalog->wasRecentlyCreated ? 201 : 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan katalog',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get public catalog by username.
     *
     * @param string $username
     * @return JsonResponse
     */
    public function getPublicCatalog(string $username): JsonResponse
    {
        try {
            $catalog = Catalog::where('username', $username)
                ->where('is_published', true)
                ->with(['products' => function ($query) {
                    $query->where('in_stock', true)
                        ->orderBy('sort_order')
                        ->orderBy('created_at', 'desc');
                }])
                ->first();

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatCatalogResponse($catalog, true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil katalog',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Format catalog response with image URLs.
     *
     * @param Catalog $catalog
     * @param bool $includeProducts
     * @return array
     */
    protected function formatCatalogResponse(Catalog $catalog, bool $includeProducts = true): array
    {
        $avatar = null;
        if ($catalog->avatar) {
            $avatarData = json_decode($catalog->avatar, true);
            if (is_array($avatarData)) {
                $avatar = [
                    'webp' => $this->imageService->getImageUrl($avatarData['webp'] ?? null),
                    'jpg' => $this->imageService->getImageUrl($avatarData['jpg'] ?? null),
                ];
            }
        }

        $data = [
            'id' => $catalog->id,
            'name' => $catalog->name,
            'username' => $catalog->username,
            'description' => $catalog->description,
            'category' => $catalog->category,
            'whatsapp' => $catalog->whatsapp,
            'avatar' => $avatar,
            'theme' => $catalog->theme,
            'is_published' => $catalog->is_published,
            'url' => url("/c/{$catalog->username}"),
            'created_at' => $catalog->created_at,
            'updated_at' => $catalog->updated_at,
        ];

        if ($includeProducts && $catalog->relationLoaded('products')) {
            $data['products'] = $catalog->products->map(function ($product) {
                return $this->formatProductForCatalog($product);
            });
            $data['products_count'] = $catalog->products->count();
        }

        return $data;
    }

    /**
     * Format product for catalog response.
     *
     * @param $product
     * @return array
     */
    protected function formatProductForCatalog($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'image' => [
                'webp' => $this->imageService->getImageUrl($product->image_webp),
                'jpg' => $this->imageService->getImageUrl($product->image_jpg),
            ],
            'description' => $product->description,
            'external_link' => $product->external_link,
            'in_stock' => $product->in_stock,
            'view_count' => $product->view_count,
            'click_count' => $product->click_count,
        ];
    }
}
