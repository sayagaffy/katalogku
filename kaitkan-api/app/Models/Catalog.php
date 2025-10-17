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
        'bg_image_webp',
        'bg_image_jpg',
        'bg_overlay_opacity',
        'social_icons_position',
        'theme',
        'theme_id',
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
            'bg_overlay_opacity' => 'float',
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

    /**
     * Get the links for the catalog.
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the link groups for the catalog.
     */
    public function linkGroups(): HasMany
    {
        return $this->hasMany(LinkGroup::class);
    }

    /**
     * Theme associated with the catalog.
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
