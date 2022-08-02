<?php

namespace BildVitta\SpCrm\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Customer.
 *
 * @package BildVitta\SpCrm\Models
 */
class Bond extends Model
{
    /**
     * @var string|null $table_name
     */
    protected ?string $table_name = 'bonds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crm_customer_id',
        'bond_crm_customer_id',
        'bond_crm_customer_uuid',
        'kind',
    ];
}
