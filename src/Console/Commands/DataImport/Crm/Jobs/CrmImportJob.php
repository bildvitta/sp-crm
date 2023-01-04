<?php

namespace BildVitta\SpCrm\Console\Commands\DataImport\Crm\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Worker;
use BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources\DbCrmCustomer;
use BildVitta\SpCrm\Console\Commands\DataImport\Crm\Resources\CustomerImport;
use Throwable;

class CrmImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var int
     */
    private int $workerId;

    /**
     * @var int
     */
    private int $limit;

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var int
     */
    private int $total;

    /**
     * @var bool
     */
    private bool $withSalesTeam;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * @var DbCrmCustomer
     */
    private $dbCrmCustomer;

    /**
     * @var CustomerImport
     */
    private $customerImport;

    /**
     * @param int $workerId
     */
    public function __construct(int $workerId)
    {
        $this->onQueue('default');
        $this->workerId = $workerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->worker = Worker::find($this->workerId);
        if (! $this->worker) {
            return;
        }
        $this->init();

        $customers = collect($this->dbCrmCustomer->getCustomers($this->limit, $this->offset, $this->withSalesTeam));
        $customerIds = $customers->pluck('id')->toArray();
        $customerBonds = collect($this->dbCrmCustomer->getCustomerBonds($customerIds));
        
        foreach ($customers as $customer) {
            $relatedCustomerBonds = $customerBonds->filter(function ($bond) use ($customer) {
                return $bond->customer_id === $customer->id;
            });
            $this->customerImport->import($customer, $relatedCustomerBonds);
        }
        $this->dispatchNext();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        $this->configConnection();
        $this->dbCrmCustomer = new DbCrmCustomer();
        $this->customerImport = new CustomerImport();
        $this->limit = (int) $this->worker->payload->limit;
        $this->offset = (int) $this->worker->payload->offset;
        $this->total = (int) $this->worker->payload->total;
        $this->withSalesTeam = (bool) $this->worker->payload->with_sales_team;
        $this->worker->status = 'in_progress';
        $this->worker->save();
    }

    /**
     * @return void
     */
    private function dispatchNext(): void
    {
        if (! $worker = Worker::find($this->workerId)) {
            return;
        }
        $newOffset = $this->offset + $this->limit;

        if ($newOffset < $this->total) {
            $worker->schedule = now();
            $worker->created_at = now();
            $worker->payload = [
                'limit' => $this->limit,
                'offset' => $newOffset,
                'total' => $this->total,
                'progress_percentage' => round(($newOffset * 100) / $this->total, 2),
                'with_sales_team' => $this->withSalesTeam,
            ];
            $worker->save();
            CrmImportJob::dispatch($worker->id);
        } else {
            $payload = $worker->payload;
            $payload->progress_percentage = 100;
            $worker->payload = $payload;
            $worker->status = 'finished';
            $worker->save();
        }
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        if (! $worker = Worker::find($this->workerId)) {
            return;
        }
        $worker->error = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
        $worker->status = 'error';
        $worker->save();
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
