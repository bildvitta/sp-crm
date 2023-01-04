<?php

namespace BildVitta\SpCrm\Console\Commands\DataImport\Crm;

use BildVitta\SpCrm\Console\Commands\DataImport\Crm\Jobs\CrmImportJob;
use BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources\DbCrmCustomer;
use Illuminate\Console\Command;

class CustomerImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataimport:crm_customers {--select=500} {--offset=0} {--with_sales_team}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call init sync customers in database';

    /**
     * @var DbCrmCustomer
     */
    private DbCrmCustomer $dbCrmCustomer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DbCrmCustomer $dbCrmCustomer)
    {
        parent::__construct();
        $this->dbCrmCustomer = $dbCrmCustomer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting import');
        
        if (! class_exists('\App\Models\Worker')) {
            $this->info('Error: class \App\Models\Worker not exists');
            return 1;
        }

        $selectLimit = 500;
        if ($optionSelect = $this->option('select')) {
            $selectLimit = (int) $optionSelect;
        }

        $offset = 0;
        if ($optionOffset = $this->option('offset')) {
            $offset = (int) $optionOffset;
        }

        $withSalesTeam = $this->option('with_sales_team');
        
        $this->configConnection();
        $totalRecords = $this->dbCrmCustomer->totalRecords($withSalesTeam);
        $this->info('Total records: ' . $totalRecords);
        
        $worker = new \App\Models\Worker();
        $worker->type = 'sp-crm.dataimport.customers';
        $worker->status = 'created';
        $worker->schedule = now();
        $worker->payload = [
            'limit' => $selectLimit,
            'offset' => $offset,
            'total' => $totalRecords,
            'progress_percentage' => 0,
            'with_sales_team' => $withSalesTeam,
        ];
        $worker->save();
       
        CrmImportJob::dispatch($worker->id);

        $this->info('Worker type: sp-crm.dataimport.customers');
        $this->info('Job started, command execution ended');

        return 0;
    }

    /**
     * @return void
     */
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
