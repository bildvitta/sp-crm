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
     * @const string|null
     */
    protected ?string $table_name = null;

    /**
     * @const string
     */
    protected const KEY_UUID = 'uuid';

    public function __construct()
    {
        parent::__construct();
        if ($this->table_name) {
            $this->table = sprintf('%s%s', config('sp-crm.table_prefix'), $this->table_name);
        }
    }

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
