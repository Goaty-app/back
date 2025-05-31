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
     * Get cache tag.
     */
    protected function getTag(string $baseKey): string
    {
        return \sprintf('%s_tag_%s_cache', $baseKey, $this->getUser()->getId());
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
    private function buildTags(string $baseKey): array
    {
        return [
            $this->getTag($baseKey),
            $this->getUserTag(),
        ];
    }
}
