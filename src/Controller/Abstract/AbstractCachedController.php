<?php

namespace App\Controller\Abstract;

use App\Cache\AppCacheRegistry;
use App\Contract\OwnerScopedRepositoryInterface;
use App\Entity\User;
use LogicException;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

abstract class AbstractCachedController extends AbstractController
{
    protected readonly TagAwareCacheInterface $cache;
    protected readonly SerializerInterface $serializer;

    /**
     * Get the cache key from the Controller route name.
     */
    public static function getCacheKey(): string
    {
        $appCache = AppCacheRegistry::getCache();
        $controllerCache = $appCache->getItem(static::buildControllerCacheKey());

        if ($controllerCache->isHit()) {
            return $controllerCache->get();
        }

        $attributes = (new ReflectionClass(static::class))->getAttributes(Route::class);
        if (empty($attributes)) {
            throw new LogicException('Unable to generate a cache key for '.static::class);
        }

        $routeName = $attributes[0]->newInstance()->getName();
        if (!$routeName) {
            throw new LogicException('Unable to generate a cache key for '.static::class);
        }

        $cacheKey = rtrim(
            str_replace('api_', '', $routeName),
            '_',
        );
        if (!$cacheKey) {
            throw new LogicException('Unable to generate a cache key for '.static::class);
        }

        $controllerCache->set($cacheKey);
        $appCache->save($controllerCache);

        return $cacheKey;
    }

    protected function getAllCachedItems(OwnerScopedRepositoryInterface $repository, array $groups = [])
    {
        $serializer = $this->serializer;

        return $this->cache->get(
            $this->buildAllCacheKey(static::getCacheKey()),
            function (ItemInterface $item) use ($repository, $serializer, $groups) {
                $item->tag($this->buildTags(static::getCacheKey()));
                $data = $repository->findByOwner($this->getUser());
                $jsonData = $serializer->serialize($data, 'json', $groups);

                return $jsonData;
            },
        );
    }

    /**
     * Build cache key for the controller name.
     */
    private static function buildControllerCacheKey(): string
    {
        return 'controller_cache_'.preg_replace('/[^a-zA-Z0-9_]/', '_', static::class);
    }

    /**
     * Build cache key for 'All' route.
     */
    private function buildAllCacheKey(string $baseKey): string
    {
        return \sprintf('%s_all_controller_%s_key', $baseKey, $this->getUserIdentifier());
    }

    /**
     * Get cache tag.
     */
    protected function getTag(string $baseKey): string
    {
        return \sprintf('%s_tag_%s_cache', $baseKey, $this->getUserIdentifier());
    }

    /**
     * Get user cache (useful to reset cache from the user).
     */
    protected function getUserTag(): string
    {
        return \sprintf('user_%s_cache', $this->getUserIdentifier());
    }

    /**
     * Build tags (cache key, cache group key, cache user key).
     */
    private function buildTags(string $baseKey): array
    {
        return [
            $this->getTag($baseKey),
            $this->getUserTag(),
        ];
    }

    private function getUserIdentifier(): string
    {
        /** @var User */
        $user = $this->getUser();

        return (string) $user->getId();
    }
}
