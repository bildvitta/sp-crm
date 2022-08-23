<?php

namespace BildVitta\SpCrm\Console\Commands\Messages\Resources;

use Throwable;

trait LogHelper
{
    /**
     * @param Throwable $exception
     * @param mixed $message
     * @return void
     */
    private function logError(Throwable $exception, $message): void
    {
        try {
            $worker = new \App\Models\Worker();
            $worker->type = 'rabbitmq.worker.error';
            $worker->payload = [
                'message' => $message
            ];
            $worker->status = 'error';
            $worker->error = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
            $worker->schedule = now();
            $worker->save();
        } catch (Throwable $throwable) {
            throw $exception;
        }
    }
}
