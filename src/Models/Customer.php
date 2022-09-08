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


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'user_hub_id',
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
        'income',
        'is_incomplete_registration',
        'kind',
    ];

    protected $casts = [
        'is_incomplete_registration' => 'boolean',
        'binding_civil_status' => 'boolean',
    ];

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

    public function related_customer()
    {
        return $this->belongsTo(Customer::class, 'related_customer_id', 'id');
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
        return $this->kind ?? null;
    }

    public function kindName()
    {
        if (isset($this->kind)) {
            return self::KIND_LIST[$this->kind];
        }

        return '';
    }

    public function getHasRelatedCustomersAttribute(): bool
    {
        return ! $this->bonds->isEmpty();
    }
}
