<?php

namespace BildVitta\SpCrm\Models;

use BildVitta\SpCrm\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Customer.
 *
 * @package BildVitta\SpCrm\Models
 */
class Customer extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_LIST = [
        'cpf' => 'Pessoa física',
        'cnpj' => 'Pessoa jurídica'
    ];

    public const KIND_LIST = [
        'customer' => 'Cliente',
        'guarantor' => 'Fiador',
        'representative' => 'Representante',
        'spouse' => 'Cônjuge',
        'procurator' => 'Procurador',
        'joint_purchase' => 'Compra conjunta'
    ];

    public const PWD_TYPE_LIST = [
        'visual' => 'Visual',
        'hearing' => 'Auditiva',
        'mental' => 'Mental',
        'physical' => 'Física',
        'multiple' => 'Múltipla',
    ];

    protected $fillable = [
        'uuid',
        'user_hub_id',
        'supervisor_id',
        'manager_id',
        'real_estate_agency_id',
        'name',
        'phone',
        'phone_two',
        'email',
        'type',
        'document',
        'nationality',
        'occupation',
        'birthday',
        'civil_status',
        'binding_civil_status',
        'binding_signer_civil_status',
        'income',
        'informal_income',
        'is_incomplete_registration',
        'is_active',
        'rg',
        'ie',
        'address',
        'street_number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'postal_code',
    ];

    protected $casts = [
        'is_incomplete_registration' => 'boolean',
        'binding_civil_status' => 'boolean',
        'binding_signer_civil_status' => 'boolean',
        'is_active' => 'boolean',
        'income' => 'real',
        'informal_income' => 'real',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = sprintf('%scustomers', config('sp-crm.table_prefix'));
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return CustomerFactory::new();
    }

    public function bonds(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, (new Bond())->getTable(), 'crm_customer_id', 'bond_crm_customer_id')
            ->withPivot(['kind', 'id', 'bond_crm_customer_uuid'])
            ->withTimestamps();
    }

    public function bonds_from(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, (new Bond())->getTable(), 'bond_crm_customer_id', 'crm_customer_id')
            ->withPivot(['kind', 'id', 'bond_crm_customer_uuid'])
            ->withTimestamps();
    }

    public function related_customers()
    {
        return $this->belongsToMany(Customer::class, (new Bond())->getTable(), 'crm_customer_id', 'bond_crm_customer_id')
            ->withPivot(['kind', 'id', 'bond_crm_customer_uuid'])
            ->withTimestamps();
    }

    public function getKindAttribute(): ?string
    {
        if (isset($this->pivot)) {
            return $this->pivot->kind;
        }
        return null;
    }

    public function getHasRelatedCustomersAttribute(): bool
    {
        return ! $this->bonds->isEmpty();
    }
}
