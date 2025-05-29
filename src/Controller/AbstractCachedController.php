<?php

namespace App\Controller;

use App\Contract\OwnerScopedRepositoryInterface;
use App\Trait\UserCacheNamingTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

abstract class AbstractCachedController extends AbstractController
{
    use UserCacheNamingTrait;

    protected TagAwareCacheInterface $cache;
    protected SerializerInterface $serializer;

    /**
     * Get the cache key from the Controller.
     */
    abstract public static function getCacheKey(): string;

    /**
     * Generate a cache group key, the user can override this function.
     */
    public static function getGroupCacheKey(): string
    {
        return static::getCacheKey().'Group';
    }

    public function __construct(TagAwareCacheInterface $cache, SerializerInterface $serializer)
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    protected function getAllCachedItems(OwnerScopedRepositoryInterface $repository, array $groups = [])
    {
        $serializer = $this->serializer;

        return $this->cache->get(
            $this->buildAllCacheKey(static::getCacheKey()),
            function (ItemInterface $item) use ($repository, $serializer, $groups) {
                $item->tag($this->buildTags([
                    static::getCacheKey(),
                    static::getGroupCacheKey(),
                ]));
                $data = $repository->findByOwner($this->getUser());
                $jsonData = $serializer->serialize($data, 'json', $groups);

                return $jsonData;
            },
        );
    }

    protected function getInCachedItems(OwnerScopedRepositoryInterface $repository, string $column, int $value, array $groups = [])
    {
        $serializer = $this->serializer;

        return $this->cache->get(
            $this->buildInCacheKey(static::getCacheKey(), $value),
            function (ItemInterface $item) use ($repository, $serializer, $groups, $column, $value) {
                $item->tag($this->buildTags([
                    static::getCacheKey(),
                    static::getGroupCacheKey(),
                ]));
                $data = $repository->findByOwnerFlex($column, $value, $this->getUser());
                $jsonData = $serializer->serialize($data, 'json', $groups);

                return $jsonData;
            },
        );
    }

    protected function getInCachedItemsCustomRequest(OwnerScopedRepositoryInterface $repository, int $value, callable $dataFetcherCallback, array $groups = [])
    {
        $serializer = $this->serializer;

        return $this->cache->get(
            $this->buildInCacheKey(static::getCacheKey(), $value),
            function (ItemInterface $item) use ($repository, $serializer, $value, $groups, $dataFetcherCallback) {
                $item->tag($this->buildTags([
                    static::getCacheKey(),
                    static::getGroupCacheKey(),
                ]));

                $data = $dataFetcherCallback($repository, $value);

                $jsonData = $serializer->serialize($data, 'json', $groups);

                return $jsonData;
            },
        );

    }
}
