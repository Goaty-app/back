<?php

namespace App\Controller;

use App\Entity\Interface\OwnedEntityRepository;
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

    abstract public static function getCacheKey(): string;

    public static function getGroupCacheKey(): string
    {
        return static::getCacheKey().'Group';
    }

    private static function filterCacheTags(): array
    {
        return array_unique([
            static::getCacheKey(),
            static::getGroupCacheKey(),
        ]);
    }

    public function __construct(TagAwareCacheInterface $cache, SerializerInterface $serializer)
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    protected function getAllCachedItems(OwnedEntityRepository $repository, array $groups = [])
    {
        $serializer = $this->serializer;

        return $this->cache->get(
            $this->buildAllCacheKey(static::getCacheKey()),
            function (ItemInterface $item) use ($repository, $serializer, $groups) {
                $item->tag($this->buildTags(static::filterCacheTags()));
                $data = $repository->findByOwner($this->getUser());
                $jsonData = $serializer->serialize($data, 'json', $groups);

                return $jsonData;
            },
        );
    }

    protected function getInCachedItems(OwnedEntityRepository $repository, string $column, int $value, array $groups = [])
    {
        $serializer = $this->serializer;

        return $this->cache->get(
            $this->buildInCacheKey(static::getCacheKey()),
            function (ItemInterface $item) use ($repository, $serializer, $groups, $column, $value) {
                $item->tag($this->buildTags(static::filterCacheTags()));
                $data = $repository->findByOwnerFlex($column, $value, $this->getUser());
                $jsonData = $serializer->serialize($data, 'json', $groups);

                return $jsonData;
            },
        );
    }
}
