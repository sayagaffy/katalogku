<?php

namespace App\Http\Controllers;

use App\Models\LinkGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LinkGroupController extends Controller
{
    /**
     * List groups for current user's catalog.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            $groups = LinkGroup::where('catalog_id', $catalog->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $groups,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil grup',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new group.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:1|max:100',
                'description' => 'sometimes|nullable|string|max:500',
                'is_collapsible' => 'sometimes|boolean',
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
            $group = new LinkGroup();
            $group->catalog_id = $catalog->id;
            $group->name = $data['name'];
            $group->description = $data['description'] ?? null;
            $group->is_collapsible = $data['is_collapsible'] ?? false;
            $group->sort_order = $data['sort_order'] ?? 0;
            $group->save();

            return response()->json([
                'success' => true,
                'message' => 'Grup dibuat',
                'data' => $group,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat grup',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update group.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            $group = LinkGroup::where('id', $id)->where('catalog_id', $catalog->id)->first();
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grup tidak ditemukan',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|min:1|max:100',
                'description' => 'sometimes|nullable|string|max:500',
                'is_collapsible' => 'sometimes|boolean',
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
            foreach (['name', 'description'] as $field) {
                if (array_key_exists($field, $data)) {
                    $group->{$field} = $data[$field];
                }
            }
            if (array_key_exists('is_collapsible', $data)) {
                $group->is_collapsible = (bool) $data['is_collapsible'];
            }
            if (array_key_exists('sort_order', $data)) {
                $group->sort_order = (int) $data['sort_order'];
            }
            $group->save();

            return response()->json([
                'success' => true,
                'message' => 'Grup diperbarui',
                'data' => $group,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui grup',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete group.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            $group = LinkGroup::where('id', $id)->where('catalog_id', $catalog->id)->first();
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grup tidak ditemukan',
                ], 404);
            }

            $group->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grup dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus grup',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

