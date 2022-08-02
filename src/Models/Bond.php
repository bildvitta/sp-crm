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
    public function __construct()
    {
        parent::__construct();
        $this->table = sprintf('%sbonds', config('sp-crm.table_prefix'));
    }

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
