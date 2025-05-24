<?php

namespace Gtrends\Sdk\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array trending(array $params = [])
 * @method static array related(array $params = [])
 * @method static array comparison(array $params = [])
 * @method static array suggestions(array $params = [])
 * @method static array opportunities(array $params = [])
 * @method static array growth(array $params = [])
 * @method static array geo(array $params = [])
 * @method static array health()
 *
 * @see \Gtrends\Sdk\Client
 */
class Gtrends extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gtrends';
    }
} 