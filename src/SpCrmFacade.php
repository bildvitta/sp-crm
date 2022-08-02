<?php

namespace BildVitta\SpCrm;

use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * @see \BildVitta\SpCrm\QueueCrm
 */
class SpCrmFacade extends Facade
{
    /**
     * @const string
     */
    private const FACADE_ACCESSOR = 'sp-crm';

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return self::FACADE_ACCESSOR;
    }
}
