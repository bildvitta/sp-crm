<?php

namespace BildVitta\SpCrm\Console\Commands\Messages\Resources;

use BildVitta\Hub\Entities\HubCompany;
use BildVitta\SpCrm\Models\Customer;
use BildVitta\SpCrm\Models\Bond;
use PhpAmqpLib\Message\AMQPMessage;
use stdClass;
use Throwable;

class MessageCustomer
{
    use LogHelper;

    /**
     * @var string
     */
    public const UPDATED = 'customers.updated';

    /**
     * @var string
     */
    public const CREATED = 'customers.created';
    
    /**
     * @var string
     */
    public const DELETED = 'customers.deleted';

    /**
     * @param AMQPMessage $message
     * @return void
     */
    public function process(AMQPMessage $message): void
    {
        $message->ack();
        $customer = null;
        $messageBody = null;
        try {
            $messageBody = $message->getBody();
            $customer = json_decode($messageBody);
            $properties = $message->get_properties();
            $operation = $properties['type'];
            switch ($operation) {
                case self::CREATED:
                case self::UPDATED:
                    $this->updateOrCreate($customer);
                    break;
                case self::DELETED:
                    $this->delete($customer);
                    break;
                default:
                    break;
            }
        } catch (Throwable $exception) {
            $this->logError($exception, $messageBody);
        }
    }

    /**
     * @param stdClass $customer
     * @return void
     */
    private function updateOrCreate(stdClass $customer): void
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
            'nationality' => $customer->nationality,
            'occupation' => $customer->occupation,
            'birthday' => $customer->birthday === '0000-00-00' ? null : $customer->birthday,
            'civil_status' => $customer->civil_status,
            'binding_civil_status' => $customer->binding_civil_status,
            'binding_signer_civil_status' => $customer->binding_signer_civil_status,
            'income' => $customer->income,
            'informal_income' => $customer->informal_income,
            'is_incomplete_registration' => $customer->is_incomplete_registration,
            'rg' => $customer->rg,
            'ie' => $customer->ie,
            'address' => $customer->address,
            'street_number' => $customer->street_number,
            'complement' => $customer->complement,
            'neighborhood' => $customer->neighborhood,
            'city' => $customer->city,
            'state' => $customer->state,
            'postal_code' => $customer->postal_code,
        ];
        if (isset($customer->is_active)) {
            $data['is_active'] = $customer->is_active;
        }

        if (config('sp-crm.customer_with_sales_team')) {
            $managerId = null;
            if (isset($customer->manager)) {
                $managerId = $modelUser::where('hub_uuid', $customer->manager)->value('id');
            }
            $supervisorId = null;
            if (isset($customer->supervisor)) {
                $supervisorId = $modelUser::where('hub_uuid', $customer->supervisor)->value('id');
            }
            $realEstateAgencyId = null;
            if (isset($customer->real_estate_agency)) {
                $realEstateAgencyId = HubCompany::where('uuid', $customer->real_estate_agency)->value('id');
            }

            $data['manager_id'] = $managerId;
            $data['supervisor_id'] = $supervisorId;
            $data['real_estate_agency_id'] = $realEstateAgencyId;
        }

        $crmCustomer = Customer::updateOrCreate(['uuid' => $customer->uuid], $data);
        $this->syncBond($crmCustomer, $customer);
    }

    /**
     * @param Customer $crmCustomer
     * @param stdClass $customer
     * @return void
     */
    private function syncBond(Customer $crmCustomer, stdClass $customer): void
    {
        Bond::where('crm_customer_id', $crmCustomer->id)->delete();
        foreach ($customer->bonds as $bond) {
            $crmBond = new Bond();
            $crmBond->crm_customer_id = $crmCustomer->id;
            $crmBond->bond_crm_customer_uuid = $bond->bond_customer_uuid;
            $crmBond->kind = $bond->kind;
            if ($localCustomer = Customer::where('uuid', $bond->bond_customer_uuid)->first()) {
                $crmBond->bond_crm_customer_id = $localCustomer->id;
            }
            $crmBond->save();
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
     * @return void
     */
    private function delete(stdClass $customer): void
    {
        if ($localCustomer = Customer::where('uuid', $customer->uuid)->first()) {
            $localCustomer->delete();
        }
    }
}
