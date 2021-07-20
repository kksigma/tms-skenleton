<?php

namespace Kksigma\TMS;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kksigma\TMS\TMS
 */
class TMSFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tms';
    }
}
