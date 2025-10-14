<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickController extends Controller
{
    /**
     * Track product click (WhatsApp button).
     *
     * @param Request $request
     * @param int $productId
     * @return JsonResponse
     */
    public function trackClick(Request $request, int $productId): JsonResponse
    {
        try {
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Create click record
            Click::create([
                'product_id' => $productId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
                'clicked_at' => now(),
            ]);

            // Increment product click count
            $product->incrementClicks();

            return response()->json([
                'success' => true,
                'message' => 'Click tracked',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal tracking click',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get analytics for user's catalog.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Katalog belum dibuat',
                ], 404);
            }

            // Get total clicks
            $totalClicks = Click::whereHas('product', function ($query) use ($catalog) {
                $query->where('catalog_id', $catalog->id);
            })->count();

            // Get clicks per product (top 10)
            $topProducts = $catalog->products()
                ->orderBy('click_count', 'desc')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'click_count' => $product->click_count,
                        'view_count' => $product->view_count,
                    ];
                });

            // Get recent clicks (last 20)
            $recentClicks = Click::whereHas('product', function ($query) use ($catalog) {
                $query->where('catalog_id', $catalog->id);
            })
                ->with('product:id,name')
                ->orderBy('clicked_at', 'desc')
                ->take(20)
                ->get()
                ->map(function ($click) {
                    return [
                        'product_id' => $click->product_id,
                        'product_name' => $click->product->name,
                        'clicked_at' => $click->clicked_at,
                        'referrer' => $click->referrer,
                    ];
                });

            // Get clicks by date (last 30 days)
            $clicksByDate = Click::whereHas('product', function ($query) use ($catalog) {
                $query->where('catalog_id', $catalog->id);
            })
                ->where('clicked_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_clicks' => $totalClicks,
                    'total_products' => $catalog->products()->count(),
                    'total_products_in_stock' => $catalog->products()->where('in_stock', true)->count(),
                    'top_products' => $topProducts,
                    'recent_clicks' => $recentClicks,
                    'clicks_by_date' => $clicksByDate,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil analytics',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
