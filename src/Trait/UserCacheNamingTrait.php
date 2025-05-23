<?php

namespace App\Trait;

trait UserCacheNamingTrait
{
    /**
     * Build cache key for 'All'.
     */
    private function buildAllCacheKey(string $baseKey): string
    {
        return \sprintf('%s_all_controller_%s_key', $baseKey, $this->getUser()->getId());
    }

    /**
     * Build cache key for 'AllIn'.
     */
    private function buildInCacheKey(string $baseKey): string
    {
        return \sprintf('%s_in_controller_%s_key', $baseKey, $this->getUser()->getId());
    }

    /**
     * Get cache tag.
     */
    protected function getTag(string $baseKey): string
    {
        return \sprintf('%s_tag_%s_cache', $baseKey, $this->getUser()->getId());
    }

    /**
     * Get cache tags.
     */
    private function getTags(array $baseKeys): array
    {
        return array_map(fn ($baseKey) => $this->getTag($baseKey), $baseKeys);
    }

    /**
     * Get user cache (useful to reset cache from the user).
     */
    protected function getUserTag(): string
    {
        return \sprintf('user_%s_cache', $this->getUser()->getId());
    }

    /**
     * Build tags (cache key, cache group key, cache user key).
     */
    private function buildTags(array $baseKeys): array
    {
        return [
            ...$this->getTags($baseKeys),
            $this->getUserTag(),
        ];
    }
}
