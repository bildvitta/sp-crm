<?php

namespace BildVitta\SpCrm\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Customer.
 *
 * @package BildVitta\SpCrm\Models
 */
class BaseModel extends Model
{
    /**
     * @const string
     */
    protected const KEY_UUID = 'uuid';

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return self::KEY_UUID;
    }
}
