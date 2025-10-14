<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'username',
        'description',
        'category',
        'whatsapp',
        'avatar',
        'theme',
        'is_published',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the catalog.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products for the catalog.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get published products ordered by sort_order.
     */
    public function publishedProducts(): HasMany
    {
        return $this->products()
            ->where('in_stock', true)
            ->orderBy('sort_order');
    }
}
