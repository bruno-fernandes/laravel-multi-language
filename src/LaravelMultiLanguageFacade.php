<?php

namespace BrunoFernandes\LaravelMultiLanguage;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BrunoFernandes\LaravelMultiLanguage\Skeleton\SkeletonClass
 */
class LaravelMultiLanguageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-multi-language';
    }
}
