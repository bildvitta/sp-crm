<?php

namespace BildVitta\SpCrm\Console\Commands\DataImport\Crm;

use BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources\DbCrmCustomer;
use BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources\CustomerImport;
use Illuminate\Console\Command;

class CustomerImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataimport:crm_customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call init sync customers in database';

    private int $selectLimit = 300;

    private DbCrmCustomer $dbCrmCustomer;

    private CustomerImport $customerImport;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DbCrmCustomer $dbCrmCustomer, CustomerImport $customerImport)
    {
        parent::__construct();
        $this->dbCrmCustomer = $dbCrmCustomer;
        $this->customerImport = $customerImport;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting import');
        $this->configConnection();
       
        $totalRecords = $this->dbCrmCustomer->totalRecords();

        $this->newLine();
        $bar = $this->output->createProgressBar($totalRecords);
        $bar->start();
        
        $loop = ceil($totalRecords / $this->selectLimit);
        for ($i = 0; $i < $loop; $i++) {
            $offset = $this->selectLimit * $i;
            
            $customers = collect($this->dbCrmCustomer->getCustomers($this->selectLimit, $offset));
            $customerIds = $customers->pluck('id')->toArray();
            $customerBonds = collect($this->dbCrmCustomer->getCustomerBonds($customerIds));
            
            foreach ($customers as $customer) {
                $relatedCustomerBonds = $customerBonds->filter(function ($bond) use ($customer) {
                    return $bond->customer_id === $customer->id;
                });
                $this->customerImport->import($customer, $relatedCustomerBonds);
                $bar->advance(1);
            }
        }
        $bar->finish();

        $this->newLine(2);
        $this->info('Import finished');

        return 0;
    }

    private function configConnection(): void
    {
        config([
            'database.connections.crm' => [
                'driver' => 'mysql',
                'host' => config('sp-crm.db.host'),
                'port' => config('sp-crm.db.port'),
                'database' => config('sp-crm.db.database'),
                'username' => config('sp-crm.db.username'),
                'password' => config('sp-crm.db.password'),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
                'options' => [],
            ]
        ]);
    }
}
