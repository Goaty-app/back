<?php

namespace App\Controller\Abstract;

use App\Contract\OwnerScopedRepositoryInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

abstract class AbstractCachedController extends AbstractController
{
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

    /**
     * Build cache key for 'All'.
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
