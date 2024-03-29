<?php

namespace BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources;

use Illuminate\Support\Facades\DB;

class DbCrmCustomer
{
    /**
     * @param bool $withSalesTeam
     * @return int
     */
    public function totalRecords(bool $withSalesTeam = false): int
    {
        $query = "SELECT count(1) as total FROM customers";
        if ($withSalesTeam) {
            $query .= ' WHERE supervisor_id IS NOT NULL AND manager_id IS NOT NULL';
        }
        $customers = DB::connection('crm')->select($query);

        return (int) $customers[0]->total;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $withSalesTeam
     * @return array
     */
    public function getCustomers(int $limit, int $offset, bool $withSalesTeam = false): array
    {
        $query = "SELECT
            customers.id, 
            customers.uuid,
            customers.name,
            customers.birthday,
            customers.gender,
            customers.phone,
            customers.phone_two,
            customers.email,
            customers.type,
            customers.document,
            customers.income,
            customers.deleted_at,
            customers.is_active,
            broker.hub_uuid AS user_uuid,
            supervisor.hub_uuid AS supervisor_uuid,
            manager.hub_uuid AS manager_uuid,
            hub_companies.uuid AS real_estate_agency_uuid,
            nationalities.name AS nationality_name,
            occupations.name AS occupation_name,
            civil_statuses.name AS civil_status_name,
            civil_statuses.is_binding AS civil_status_is_binding
        FROM customers 
        LEFT JOIN users broker ON customers.user_id = broker.id
        LEFT JOIN users supervisor ON customers.supervisor_id = supervisor.id
        LEFT JOIN users manager ON customers.manager_id = manager.id
        LEFT JOIN hub_companies ON customers.real_estate_agency_id = hub_companies.id
        LEFT JOIN nationalities ON customers.nationality_id = nationalities.id
        LEFT JOIN occupations ON customers.occupation_id = occupations.id
        LEFT JOIN civil_statuses ON customers.civil_status_id = civil_statuses.id";
        if ($withSalesTeam) {
            $query .= " WHERE customers.supervisor_id IS NOT NULL AND customers.manager_id IS NOT NULL";
        }
        $query .= " LIMIT :limit OFFSET :offset";
        
        return DB::connection('crm')->select($query, [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCustomerBonds(array $customerIds): array
    {
        if (empty($customerIds)) {
            return [];
        }
        $customerIds = implode(',', $customerIds);
        $query = "SELECT customer_bonds.customer_id,
            customer_bonds.bond_customer_id,
            customer_bonds.kind,
            a.uuid as customer_uuid,
            b.uuid as customer_bond_uuid
            FROM customer_bonds
            LEFT JOIN customers a ON customer_bonds.customer_id = a.id
            LEFT JOIN customers b ON customer_bonds.bond_customer_id = b.id 
            WHERE customer_bonds.customer_id IN({$customerIds})";

        return DB::connection('crm')->select($query);
    }
}
