<?php

namespace App\Cache;

use LogicException;
use Psr\Cache\CacheItemPoolInterface;

final class AppCacheRegistry
{
    private static ?CacheItemPoolInterface $cacheInstance = null;

    public static function initialize(CacheItemPoolInterface $cache): void
    {
        if (null === self::$cacheInstance) {
            self::$cacheInstance = $cache;
        }
    }

    public static function getCache(): CacheItemPoolInterface
    {
        if (null === self::$cacheInstance) {
            throw new LogicException('AppCacheRegistry has not been initialized. Call initialize() first.');
        }

        return self::$cacheInstance;
    }
}
