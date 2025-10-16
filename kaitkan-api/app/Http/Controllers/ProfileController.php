<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get current user's profile (catalog).
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;

            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil belum dibuat',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatProfile($catalog),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil profil',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Patch profile fields (no avatar upload here).
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;
            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil belum dibuat',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|min:3|max:100',
                'username' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^[a-z0-9_-]+$/',
                    'unique:catalogs,username,' . $catalog->id,
                    'unique:users,username,' . $user->id,
                ],
                'description' => 'sometimes|nullable|string|max:500',
                'category' => 'sometimes|nullable|string|max:50',
                'whatsapp' => 'sometimes|nullable|string|min:10|max:15',
                'theme' => 'sometimes|nullable|string|in:default,blue,green,purple,pink',
                'theme_id' => 'sometimes|nullable|exists:themes,id',
                'is_published' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if (isset($data['name'])) {
                $catalog->name = $data['name'];
                $user->name = $data['name'];
            }

            if (isset($data['username'])) {
                $catalog->username = Str::lower($data['username']);
                $user->username = Str::lower($data['username']);
            }

            foreach (['description', 'category', 'theme', 'theme_id'] as $field) {
                if (array_key_exists($field, $data)) {
                    $catalog->{$field} = $data[$field];
                }
            }

            if (array_key_exists('whatsapp', $data)) {
                $catalog->whatsapp = $data['whatsapp'] ?: $user->whatsapp;
            }

            if (array_key_exists('is_published', $data)) {
                $catalog->is_published = (bool) $data['is_published'];
            }

            $user->save();
            $catalog->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil diperbarui',
                'data' => $this->formatProfile($catalog->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Upload or replace avatar image for profile.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;
            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil belum dibuat',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Delete old avatar if exists
            if ($catalog->avatar) {
                $oldAvatar = json_decode($catalog->avatar, true);
                if (is_array($oldAvatar)) {
                    $this->imageService->deleteImage($oldAvatar['webp'] ?? null, $oldAvatar['jpg'] ?? null);
                }
            }

            $images = $this->imageService->uploadCatalog($request->file('avatar'));
            $catalog->avatar = json_encode($images);
            $catalog->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar diperbarui',
                'data' => $this->formatProfile($catalog->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah avatar',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Onboarding step to set basic profile fields.
     */
    public function onboarding(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $catalog = $user->catalog;
            if (!$catalog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil belum dibuat',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|min:3|max:100',
                'username' => [
                    'sometimes', 'required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9_-]+$/',
                    'unique:catalogs,username,' . $catalog->id,
                    'unique:users,username,' . $user->id,
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            if (isset($data['name'])) {
                $catalog->name = $data['name'];
                $user->name = $data['name'];
            }

            if (isset($data['username'])) {
                $catalog->username = Str::lower($data['username']);
                $user->username = Str::lower($data['username']);
            }

            $catalog->is_published = false; // keep draft until explicit publish
            $user->save();
            $catalog->save();

            return response()->json([
                'success' => true,
                'message' => 'Onboarding profil disimpan',
                'data' => $this->formatProfile($catalog->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan onboarding',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    protected function formatProfile(Catalog $catalog): array
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

        return [
            'id' => $catalog->id,
            'name' => $catalog->name,
            'username' => $catalog->username,
            'description' => $catalog->description,
            'category' => $catalog->category,
            'whatsapp' => $catalog->whatsapp,
            'avatar' => $avatar,
            'theme' => $catalog->theme,
            'theme_id' => $catalog->theme_id,
            'is_published' => $catalog->is_published,
            'url' => url("/c/{$catalog->username}"),
            'created_at' => $catalog->created_at,
            'updated_at' => $catalog->updated_at,
        ];
    }
}

