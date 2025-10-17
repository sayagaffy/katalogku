<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SendOTPRequest;
use App\Http\Requests\VerifyOTPRequest;
use App\Http\Requests\ResetPinRequest;
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
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Reset PIN using OTP verification (no auth required).
     * If success, also returns a new access token for convenience.
     */
    public function resetPin(ResetPinRequest $request): JsonResponse
    {
        $whatsapp = $request->input('whatsapp');
        $otp = $request->input('otp');
        $pin = $request->input('pin');

        // Verify OTP
        $result = $this->otpService->verifyOTP($whatsapp, $otp);
        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        // Find user
        $user = User::where('whatsapp', $whatsapp)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan',
            ], 404);
        }

        // Set PIN
        $user->pin_hash = Hash::make($pin);
        if (!$user->verified_at) {
            $user->verified_at = Carbon::now();
        }
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil direset',
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
     * Set or change 6-digit PIN for the authenticated user.
     */
    public function setPin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pin' => ['required', 'digits:6', 'confirmed'],
        ], [
            'pin.required' => 'PIN harus diisi',
            'pin.digits' => 'PIN harus 6 digit',
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $user->pin_hash = Hash::make($request->input('pin'));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil diset',
        ]);
    }

    /**
     * Login using WhatsApp + 6-digit PIN.
     */
    public function loginWithPin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'whatsapp' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,12}$/'],
            'pin' => ['required', 'digits:6'],
        ], [
            'whatsapp.required' => 'Nomor WhatsApp harus diisi',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid',
            'pin.required' => 'PIN harus diisi',
            'pin.digits' => 'PIN harus 6 digit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('whatsapp', $request->input('whatsapp'))->first();
        if (!$user || !$user->pin_hash || !Hash::check($request->input('pin'), $user->pin_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp atau PIN salah',
            ], 401);
        }

        if (!$user->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi. Silakan daftar ulang.',
            ], 403);
        }

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
    }

    /**
     * Generate Firebase custom token for the authenticated user.
     * Stubbed to 501 if Firebase SDK/credentials are not configured.
     */
    public function firebaseToken(Request $request): JsonResponse
    {
        try {
            // Ensure Firebase SDK available
            if (!class_exists('Kreait\\Firebase\\Factory')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Firebase tidak terkonfigurasi',
                ], 501);
            }

            $user = $request->user();

            // Resolve Firebase factory
            $factory = new \Kreait\Firebase\Factory();

            $credentials = env('FIREBASE_CREDENTIALS');
            if ($credentials) {
                $factory = $factory->withServiceAccount($credentials);
            }

            $auth = $factory->createAuth();

            // Ensure Firebase user exists
            if (!$user->firebase_uid) {
                try {
                    $firebaseUser = $auth->getUserByPhoneNumber($user->whatsapp);
                } catch (\Throwable $e) {
                    $firebaseUser = $auth->createUser([
                        'phoneNumber' => $user->whatsapp,
                        'displayName' => $user->name,
                    ]);
                }
                $user->firebase_uid = $firebaseUser->uid;
                $user->save();
            }

            $customToken = $auth->createCustomToken($user->firebase_uid);

            return response()->json([
                'success' => true,
                'data' => [
                    'custom_token' => (string) $customToken,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token Firebase',
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
