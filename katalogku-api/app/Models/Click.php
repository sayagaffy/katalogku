<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    /**
     * Disable default timestamps (we use clicked_at only).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'product_id',
        'ip_address',
        'user_agent',
        'referrer',
        'clicked_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    /**
     * Get the product that owns the click.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
