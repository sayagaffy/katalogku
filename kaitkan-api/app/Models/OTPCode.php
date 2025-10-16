<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTPCode extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'otp_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'whatsapp',
        'code',
        'expires_at',
        'verified_at',
        'ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }
}
