<?php

namespace BildVitta\SpCrm\Console\Commands\Messages;

use BildVitta\SpCrm\Console\Commands\Messages\Resources\MessageCustomer;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

class CustomersWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmqworker:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets and processes messages';

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var MessageCustomer
     */
    private MessageCustomer $messageCustomer;

    /**
     * @param MessageCustomer $messageCustomer
     */
    public function __construct(MessageCustomer $messageCustomer)
    {
        parent::__construct();
        $this->messageCustomer = $messageCustomer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            try {
                $this->process();
            } catch (AMQPExceptionInterface $exception) {
                $this->closeChannel();
                $this->closeConnection();
                sleep(5);
            }
        }

        return 0;
    }

    /**
     * @return void
     */
    private function process(): void
    {
        $this->connect();
        $this->channel = $this->connection->channel();
        
        $queueName = config('sp-crm.rabbitmq.queue.customers');
        $callback = [$this->messageCustomer, 'process'];
        $this->channel->basic_consume(
            queue: $queueName,
            callback: $callback
        );

        $this->channel->consume();
        
        $this->closeChannel();
        $this->closeConnection();
    }

    /**
     * @return void
     */
    private function closeChannel(): void
    {
        try {
            if ($this->channel) {
                $this->channel->close();
                $this->channel = null;
            }
        } catch (Throwable $exception) {
        }
    }

    /**
     * @return void
     */
    private function closeConnection(): void
    {
        try {
            if ($this->connection) {
                $this->connection->close();
                $this->connection = null;
            }
        } catch (Throwable $exception) {
        }
    }

    /**
     * @return void
     */
    private function connect(): void
    {
        $host = config('sp-crm.rabbitmq.host');
        $port = config('sp-crm.rabbitmq.port');
        $user = config('sp-crm.rabbitmq.user');
        $password = config('sp-crm.rabbitmq.password');
        $virtualhost = config('sp-crm.rabbitmq.virtualhost');
        $heartbeat = 20;
        $sslOptions = [
            'verify_peer' => false
        ];
        $options = [
            'heartbeat' => $heartbeat
        ];
        
        if (app()->isLocal()) {
            $this->connection = new AMQPStreamConnection(
                host: $host,
                port: $port,
                user: $user,
                password: $password,
                vhost: $virtualhost,
                heartbeat: $heartbeat
            );
        } else {
            $this->connection = new AMQPSSLConnection(
                host: $host,
                port: $port,
                user: $user,
                password: $password,
                vhost: $virtualhost,
                ssl_options: $sslOptions,
                options: $options
            );
        }
    }
}
