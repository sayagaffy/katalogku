<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'catalog_id',
        'link_group_id',
        'title',
        'url',
        'type',
        'icon',
        'thumbnail_webp',
        'thumbnail_jpg',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Catalog owner of the link.
     */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    /**
     * Optional group for the link.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(LinkGroup::class, 'link_group_id');
    }
}

