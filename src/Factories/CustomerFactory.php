<?php

namespace BildVitta\SpCrm\Factories;

use BildVitta\SpCrm\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class CustomerFactory.
 *
 * @package BildVitta\SpCrm\Factories
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),

            'name' => fake()->name,
            'phone' => fake()->phoneNumber,
            'phone_two' => fake()->phoneNumber,
            'email' => fake()->unique()->safeEmail,
            'document' => fake()->cpf,
            
            'user_hub_id' => config('sp-crm.model_user')::inRandomOrder()->first(),
            'type' => fake()->randomElement(array_keys(Customer::TYPE_LIST)),
            'nationality' => fake()->words(2, true),
            'occupation' => fake()->words(2, true),
            'birthday' => fake()->date,
            'civil_status' => fake()->words(2, true),
            'binding_civil_status' => fake()->words(2, true),
            'income' => fake()->randomFloat(2, 1000, 10000),
            'is_incomplete_registration' => fake()->boolean,
        ];
    }
}
