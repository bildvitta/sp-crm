<?php

namespace BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources;

use BildVitta\SpCrm\Models\Customer;
use BildVitta\SpCrm\Models\Bond;
use Illuminate\Support\Collection;
use stdClass;

class CustomerImport
{
    public function import(stdClass $customer, Collection $customerBonds): void
    {
        $modelUser = config('sp-crm.model_user');
        $userHub = $modelUser::select(['id'])
            ->whereHubUuid($customer->user_uuid)
            ->first();
        $data = [
            'uuid' => $customer->uuid,
            'user_hub_id' => $userHub?->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'phone_two' => $customer->phone_two,
            'email' => $customer->email,
            'type' => $customer->type,
            'document' => $customer->document,
            'nationality' => $customer->nationality_name,
            'occupation' => $customer->occupation_name,
            'birthday' => $this->getBirthday($customer->birthday),
            'civil_status' => $customer->civil_status_name,
            'binding_civil_status' => $customer->civil_status_is_binding,
            'income' => $customer->income,
            'is_incomplete_registration' => $this->isIncompleteRegistration($customer),
            'kind' => $customer->kind,
        ];
        $crmCustomer = Customer::updateOrCreate(['uuid' => $customer->uuid], $data);
        $this->syncBond($crmCustomer, $customerBonds);
    }

    private function syncBond(Customer $crmCustomer, Collection $customerBonds): void
    {
        Bond::where('crm_customer_id', $crmCustomer->id)->delete();
        foreach ($customerBonds as $customerBond) {
            $bond = new Bond();
            $bond->crm_customer_id = $crmCustomer->id;
            $bond->bond_crm_customer_uuid = $customerBond->customer_bond_uuid;
            $bond->kind = $customerBond->kind;
            if ($localCustomer = Customer::where('uuid', $customerBond->customer_bond_uuid)->first()) {
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

    private function getBirthday($birthday): ?string
    {
        if ($birthday === '0000-00-00') {
            return null;
        }

        return $birthday;
    }
}
