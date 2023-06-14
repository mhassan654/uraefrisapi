<?php

namespace Mhassan654\Uraefrisapi;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mhassan654\Uraefrisapi\Skeleton\SkeletonClass
 */
class UraefrisapiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'uraefrisapi';
    }
}
