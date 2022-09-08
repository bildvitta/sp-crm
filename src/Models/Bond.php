<?php

namespace BildVitta\SpCrm\Models;

use BildVitta\SpCrm\Factories\BondFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Customer.
 *
 * @package BildVitta\SpCrm\Models
 */
class Bond extends Model
{
    use HasFactory;
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = sprintf('%sbonds', config('sp-crm.table_prefix'));
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return BondFactory::new();
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
