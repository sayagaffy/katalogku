<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    /**
     * List curated themes.
     */
    public function index(): JsonResponse
    {
        try {
            $themes = Theme::query()
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name', 'key', 'palette', 'preview_image', 'is_default']);

            return response()->json([
                'success' => true,
                'data' => $themes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar tema',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

