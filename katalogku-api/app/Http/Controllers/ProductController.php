<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get all products for current user's catalog.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            $products = $catalog->products()
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products->map(fn($product) => $this->formatProductResponse($product)),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get single product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            $product = $catalog->products()->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatProductResponse($product),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create new product.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buat katalog terlebih dahulu',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:50',
                'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
                'description' => 'nullable|string|max:1000',
                'external_link' => 'nullable|url|max:500',
                'in_stock' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Upload image
            $images = $this->imageService->uploadProduct($request->file('image'));

            // Create product
            $product = Product::create([
                'catalog_id' => $catalog->id,
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'category' => $request->input('category'),
                'image_webp' => $images['webp'],
                'image_jpg' => $images['jpg'],
                'description' => $request->input('description'),
                'external_link' => $request->input('external_link'),
                'in_stock' => $request->input('in_stock', true),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $this->formatProductResponse($product),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            $product = $catalog->products()->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:200',
                'price' => 'sometimes|required|numeric|min:0',
                'category' => 'sometimes|required|string|max:50',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
                'description' => 'nullable|string|max:1000',
                'external_link' => 'nullable|url|max:500',
                'in_stock' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->only(['name', 'price', 'category', 'description', 'external_link', 'in_stock', 'sort_order']);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Delete old images
                $this->imageService->deleteImage($product->image_webp, $product->image_jpg);

                // Upload new images
                $images = $this->imageService->uploadProduct($request->file('image'));
                $data['image_webp'] = $images['webp'];
                $data['image_jpg'] = $images['jpg'];
            }

            $product->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data' => $this->formatProductResponse($product->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            $product = $catalog->products()->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Delete images
            $this->imageService->deleteImage($product->image_webp, $product->image_jpg);

            // Delete product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Reorder products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'products' => 'required|array',
                'products.*.id' => 'required|integer|exists:products,id',
                'products.*.sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            foreach ($request->input('products') as $item) {
                $product = $catalog->products()->find($item['id']);
                if ($product) {
                    $product->update(['sort_order' => $item['sort_order']]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Urutan produk berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan produk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Format product response with image URLs.
     *
     * @param Product $product
     * @return array
     */
    protected function formatProductResponse(Product $product): array
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
            'sort_order' => $product->sort_order,
            'view_count' => $product->view_count,
            'click_count' => $product->click_count,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }
}
