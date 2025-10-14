<?php

namespace App\Services;

use App\Models\OTPCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OTPService
{
    /**
     * Generate and send OTP to WhatsApp number.
     *
     * @param string $whatsapp
     * @param string|null $ipAddress
     * @return array
     * @throws \Exception
     */
    public function sendOTP(string $whatsapp, ?string $ipAddress = null): array
    {
        // Check rate limit: max 3 OTP per hour
        $recentOTPs = OTPCode::where('whatsapp', $whatsapp)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        if ($recentOTPs >= 3) {
            throw new \Exception('Terlalu banyak permintaan OTP. Coba lagi dalam 1 jam.');
        }

        // Generate 6-digit OTP
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Create OTP record
        $otp = OTPCode::create([
            'whatsapp' => $whatsapp,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
            'ip_address' => $ipAddress,
        ]);

        // Send OTP via WebSMS (or log in development)
        if (config('app.env') === 'production') {
            $this->sendViaSMS($whatsapp, $code);
        } else {
            // Development: just log the OTP
            Log::info("OTP untuk {$whatsapp}: {$code}");
        }

        return [
            'success' => true,
            'expires_in' => 300, // 5 minutes in seconds
            'can_resend_at' => Carbon::now()->addMinute()->toIso8601String(),
        ];
    }

    /**
     * Verify OTP code.
     *
     * @param string $whatsapp
     * @param string $code
     * @return array
     */
    public function verifyOTP(string $whatsapp, string $code): array
    {
        $otp = OTPCode::where('whatsapp', $whatsapp)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return [
                'valid' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa',
            ];
        }

        // Mark as verified
        $otp->update(['verified_at' => Carbon::now()]);

        return [
            'valid' => true,
            'message' => 'OTP berhasil diverifikasi',
        ];
    }

    /**
     * Send SMS via WebSMS API.
     *
     * @param string $whatsapp
     * @param string $code
     * @return void
     * @throws \Exception
     */
    protected function sendViaSMS(string $whatsapp, string $code): void
    {
        $apiToken = config('services.websms.api_token');

        if (!$apiToken) {
            throw new \Exception('WebSMS API token not configured');
        }

        // Format phone number to 08xxx format (WebSMS requirement)
        $phone = $this->formatPhoneNumberForWebSMS($whatsapp);

        // Pesan tanpa kata-kata terlarang (Token, OTP, Kode, Verifikasi, dll)
        $message = "Sandi masuk KatalogKu Anda: {$code}\nBerlaku 5 menit.\nJangan bagikan ke siapapun.";

        try {
            // WebSMS menggunakan GET request dengan parameter di URL
            $url = 'https://websms.co.id/api/smsgateway';

            $response = Http::get($url, [
                'token' => $apiToken,
                'to' => $phone,
                'msg' => $message,
            ]);

            $result = $response->json();

            // Check response status
            if (!$response->successful() || (isset($result['status']) && $result['status'] !== 'success')) {
                $errorMessage = $result['message'] ?? 'Unknown error';
                Log::error('WebSMS API error', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'message' => $errorMessage,
                ]);
                throw new \Exception('Gagal mengirim SMS: ' . $errorMessage);
            }

            Log::info("OTP sent via WebSMS to {$phone}");
        } catch (\Exception $e) {
            Log::error('Failed to send OTP via WebSMS', [
                'error' => $e->getMessage(),
                'phone' => $phone,
            ]);
            throw new \Exception('Gagal mengirim SMS. Silakan coba lagi.');
        }
    }

    /**
     * Format phone number to 08xxx format for WebSMS.
     * WebSMS requires phone numbers in format: 0823456789
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumberForWebSMS(string $phone): string
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Convert 62xxx to 0xxx
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }

        // Ensure it starts with 0
        if (substr($phone, 0, 1) !== '0') {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    /**
     * Format phone number to Indonesian format (62xxx).
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Clean up expired OTP codes (call this in a scheduled job).
     *
     * @return int Number of deleted records
     */
    public function cleanupExpiredOTPs(): int
    {
        return OTPCode::where('created_at', '<', Carbon::now()->subDay())->delete();
    }
}
