<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SendOTPRequest;
use App\Http\Requests\VerifyOTPRequest;
use App\Models\Catalog;
use App\Models\OTPCode;
use App\Models\User;
use App\Services\OTPService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP code to WhatsApp number
     *
     * @param SendOTPRequest $request
     * @return JsonResponse
     */
    public function sendOTP(SendOTPRequest $request): JsonResponse
    {
        try {
            $whatsapp = $request->input('whatsapp');
            $ipAddress = $request->ip();

            $result = $this->otpService->sendOTP($whatsapp, $ipAddress);

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke WhatsApp Anda',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }
    }

    /**
     * Verify OTP and register new user
     *
     * @param VerifyOTPRequest $request
     * @return JsonResponse
     */
    public function verifyOTP(VerifyOTPRequest $request): JsonResponse
    {
        try {
            $whatsapp = $request->input('whatsapp');
            $code = $request->input('otp');

            $result = $this->otpService->verifyOTP($whatsapp, $code);

            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            // Check if user already exists
            $existingUser = User::where('whatsapp', $whatsapp)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp sudah terdaftar. Silakan login.',
                ], 409);
            }

            // Create new user with suggested username
            $name = $request->input('name');
            $suggestedUsername = $this->generateUniqueUsername($name ?: $whatsapp);

            $user = User::create([
                'name' => $name,
                'whatsapp' => $whatsapp,
                'username' => $suggestedUsername,
                'password' => Hash::make($request->input('password')),
                'verified_at' => Carbon::now(),
            ]);

            // Create draft catalog (profile) for onboarding
            $catalogName = $name ?: 'Toko ' . Str::substr($whatsapp, -4);
            Catalog::create([
                'user_id' => $user->id,
                'name' => $catalogName,
                'username' => $suggestedUsername,
                'description' => null,
                'category' => null,
                'whatsapp' => $whatsapp,
                'avatar' => null,
                'theme' => 'default',
                'is_published' => false,
            ]);

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'whatsapp' => $user->whatsapp,
                        'username' => $user->username,
                        'avatar' => $user->avatar,
                        'verified_at' => $user->verified_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Login user with WhatsApp and password
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $whatsapp = $request->input('whatsapp');
            $password = $request->input('password');

            // Find user by WhatsApp
            $user = User::where('whatsapp', $whatsapp)->first();

            if (!$user || !Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp atau password salah',
                ], 401);
            }

            // Check if user is verified
            if (!$user->verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun belum diverifikasi. Silakan daftar ulang.',
                ], 403);
            }

            // Revoke old tokens (optional - for single session)
            // $user->tokens()->delete();

            // Generate new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'whatsapp' => $user->whatsapp,
                        'username' => $user->username,
                        'avatar' => $user->avatar,
                        'verified_at' => $user->verified_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Logout current user (revoke token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'whatsapp' => $user->whatsapp,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'verified_at' => $user->verified_at,
                    'created_at' => $user->created_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generate a unique username slug for both users and catalogs.
     */
    protected function generateUniqueUsername(string $base): string
    {
        $slug = Str::slug($base);
        // Fallback if slug is empty (e.g., base is numbers only)
        if (!$slug) {
            $slug = 'user';
        }

        $slug = Str::limit($slug, 40, ''); // reserve space for suffix if needed
        $candidate = $slug;
        $suffix = 0;

        while (
            User::where('username', $candidate)->exists() ||
            Catalog::where('username', $candidate)->exists()
        ) {
            $suffix++;
            $candidate = Str::limit($slug, 40, '') . '-' . $suffix;
            if ($suffix > 9999) {
                $candidate = $slug . '-' . Str::random(4);
                break;
            }
        }

        return $candidate;
    }
}
