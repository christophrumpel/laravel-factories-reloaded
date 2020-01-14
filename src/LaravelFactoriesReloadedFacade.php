<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Christophrumpel\LaravelFactoriesReloaded\Skeleton\SkeletonClass
 */
class LaravelFactoriesReloadedFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-factories-reloaded';
    }
}
