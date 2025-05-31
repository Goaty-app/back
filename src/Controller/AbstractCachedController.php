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

    protected readonly TagAwareCacheInterface $cache;
    protected readonly SerializerInterface $serializer;

    /**
     * Get the cache key from the Controller.
     */
    abstract public static function getCacheKey(): string;

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
}
