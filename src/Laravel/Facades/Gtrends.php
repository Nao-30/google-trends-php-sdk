<?php

namespace Gtrends\Sdk\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array trending(?string $region = null, int $limit = 10, bool $includeNews = false)
 * @method static array relatedTopics(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0')
 * @method static array relatedQueries(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0')
 * @method static array compare(array $topics, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0')
 * @method static array suggestions(string $query, ?string $region = null, string $contentType = 'all')
 * @method static array opportunities(string $niche, ?string $region = null, int $count = 10)
 * @method static array growth(string $query, string $timeframe = 'today 12-m')
 * @method static array geo(string $query, ?string $region = null, string $resolution = 'COUNTRY', string $timeframe = 'today 12-m', string $category = '0', int $count = 20)
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
