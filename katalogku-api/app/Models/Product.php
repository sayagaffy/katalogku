<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'catalog_id',
        'category',
        'name',
        'slug',
        'price',
        'image_webp',
        'image_jpg',
        'description',
        'external_link',
        'in_stock',
        'sort_order',
        'view_count',
        'click_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'in_stock' => 'boolean',
            'view_count' => 'integer',
            'click_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the catalog that owns the product.
     */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    /**
     * Get the clicks for the product.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}
