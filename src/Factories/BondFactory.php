<?php

namespace BildVitta\SpCrm\Factories;

use BildVitta\SpCrm\Models\Bond;
use BildVitta\SpCrm\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class BondFactory.
 *
 * @package BildVitta\SpCrm\Factories
 */
class BondFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bond::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first();
        $bond = Customer::inRandomOrder()->where('id', '!=', $customer->id)->first();

        return [
            'crm_customer_id' => $customer->id,
            'bond_crm_customer_id' => $bond->id,
            'bond_crm_customer_uuid' => $bond->uuid,
            'kind' => $bond->kind,
        ];
    }
}
