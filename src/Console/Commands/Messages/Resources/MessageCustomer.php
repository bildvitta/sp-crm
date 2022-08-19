<?php

namespace BildVitta\SpCrm\Console\Commands\Messages\Resources;

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
        try {
            $properties = $message->get_properties();
            $customer = json_decode($message->getBody());
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
            $this->logError($exception, $customer);
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
            'income' => $customer->income,
            'is_incomplete_registration' => $customer->is_incomplete_registration,
            'kind' => $customer->kind,
        ];
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
