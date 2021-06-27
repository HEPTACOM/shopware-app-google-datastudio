<?php

namespace App\Models\Shopware6;

use App\Models\UuidsAsPrimaryKeysTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string      $shop_id
 * @property string      $shop_url
 * @property string      $shop_secret
 * @property string|null $api_key
 * @property string|null $secret_key
 */
class Shop extends Model
{
    use UuidsAsPrimaryKeysTrait;

    protected $table = 'shopware_6_shop';

    protected $fillable = [
        'id',
        'shop_id',
        'shop_url',
        'shop_secret',
        'api_key',
        'secret_key',
    ];

    protected $hidden = [
        'shop_secret',
        'api_key',
        'secret_key',
    ];

    protected $casts = [
        'shop_secret' => 'encrypted',
        'api_key' => 'encrypted',
        'secret_key' => 'encrypted',
    ];
}
