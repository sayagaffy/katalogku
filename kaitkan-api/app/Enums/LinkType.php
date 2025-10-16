<?php

namespace App\Enums;

enum LinkType: string
{
    case GENERAL = 'general';
    case SOCIAL = 'social';
    case PRODUCT = 'product';
    case SHOP_COLLECTION = 'shop_collection';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

