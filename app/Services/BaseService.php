<?php

namespace App\Services;

use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    use ApiResponseTrait;

    /**
     * Handle business logic exceptions and logging.
     *
     * @param Exception $e
     * @param string|null $customMessage
     * @return void
     * @throws Exception
     */
    protected function handleException(Exception $e, ?string $customMessage = null)
    {
        Log::error(($customMessage ?? 'Service Error') . ': ' . $e->getMessage(), [
            'exception' => get_class($e),
            'trace'     => $e->getTraceAsString()
        ]);

        throw $e;
    }
}
