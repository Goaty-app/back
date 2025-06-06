<?php

namespace App;

use App\Cache\AppCacheRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        if ($this->container instanceof ContainerInterface && $this->container->has('cache.app')) {
            /** @var CacheItemPoolInterface $appCache */
            $appCache = $this->container->get('cache.app');

            AppCacheRegistry::initialize($appCache);
        } else {
            throw new RuntimeException('cache.app service not found for AppCacheRegistry initialization.');
        }
    }
}
