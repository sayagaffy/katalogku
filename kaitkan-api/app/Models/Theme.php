<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'key',
        'palette',
        'preview_image',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'palette' => 'array',
        ];
    }

    /**
     * Get catalogs using this theme.
     */
    public function catalogs(): HasMany
    {
        return $this->hasMany(Catalog::class);
    }
}

