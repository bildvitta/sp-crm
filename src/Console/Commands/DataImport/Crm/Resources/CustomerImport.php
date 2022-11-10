<?php

namespace BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources;

use BildVitta\SpCrm\Models\Customer;
use BildVitta\SpCrm\Models\Bond;
use Illuminate\Support\Collection;
use stdClass;

class CustomerImport
{
    /**
     * @param stdClass $customer
     * @param Collection $customerBonds
     * @return void
     */
    public function import(stdClass $customer, Collection $customerBonds): void
    {
        $modelUser = config('sp-crm.model_user');
        $userHub = $modelUser::select(['id'])
            ->whereHubUuid($customer->user_uuid)
            ->first();
        if (!$modelCustomer = Customer::withTrashed()->where('uuid', $customer->uuid)->first()) {
            $modelCustomer = new Customer();
            $modelCustomer->uuid = $customer->uuid;
        }
        $modelCustomer->user_hub_id = $userHub?->id;
        $modelCustomer->name = $customer->name;
        $modelCustomer->phone = $customer->phone;
        $modelCustomer->phone_two = $customer->phone_two;
        $modelCustomer->email = $customer->email;
        $modelCustomer->type = $customer->type;
        $modelCustomer->document = $customer->document;
        $modelCustomer->nationality = $customer->nationality_name;
        $modelCustomer->occupation = $customer->occupation_name;
        $modelCustomer->birthday = $this->getBirthday($customer->birthday);
        $modelCustomer->civil_status = $customer->civil_status_name;
        $modelCustomer->binding_civil_status = $customer->civil_status_is_binding;
        $modelCustomer->income = $customer->income;
        $modelCustomer->is_incomplete_registration = $this->isIncompleteRegistration($customer);
        $modelCustomer->deleted_at = $customer->deleted_at;
        $modelCustomer->is_active = (bool) $customer->is_active;
        $modelCustomer->save();

        $this->syncBond($modelCustomer, $customerBonds);
    }

    /**
     * @param Customer $crmCustomer
     * @param Collection $customerBonds
     * @return void
     */
    private function syncBond(Customer $crmCustomer, Collection $customerBonds): void
    {
        Bond::where('crm_customer_id', $crmCustomer->id)->delete();
        foreach ($customerBonds as $customerBond) {
            $bond = new Bond();
            $bond->crm_customer_id = $crmCustomer->id;
            $bond->bond_crm_customer_uuid = $customerBond->customer_bond_uuid;
            $bond->kind = $customerBond->kind;
            if ($localCustomer = Customer::withTrashed()->where('uuid', $customerBond->customer_bond_uuid)->first()) {
                $bond->bond_crm_customer_id = $localCustomer->id;
            }
            $bond->save();
        }
        $bonds = Bond::where('bond_crm_customer_uuid', $crmCustomer->uuid)
            ->whereNull('bond_crm_customer_id')
            ->get();
        foreach ($bonds as $bondCustomer) {
            $bondCustomer->bond_crm_customer_id = $crmCustomer->id;
            $bondCustomer->save();
        }
    }

    /**
     * @param stdClass $customer
     * @return bool
     */
    private function isIncompleteRegistration(stdClass $customer): bool
    {
        $attributes = [
            'name',
            'document',
            'civil_status_name',
            'gender',
            'birthday',
        ];
        foreach ($attributes as $attribute) {
            if (empty($customer->$attribute)) {
                return true;
            }
        }

        if (!$customer->email && !$customer->phone && !$customer->phone_two) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $birthday
     * @return string|null
     */
    private function getBirthday($birthday): ?string
    {
        if ($birthday === '0000-00-00') {
            return null;
        }

        return $birthday;
    }
}
