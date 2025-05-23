<?php

namespace App\Trait;

trait UserCacheNamingTrait
{
    private function buildAllCacheKey(string $baseKey): string
    {
        return \sprintf('%s_all_controller_%s_key', $baseKey, $this->getUser()->getId());
    }

    private function buildInCacheKey(string $baseKey): string
    {
        return \sprintf('%s_in_controller_%s_key', $baseKey, $this->getUser()->getId());
    }

    protected function getTag(string $baseKey): string
    {
        return \sprintf('%s_tag_%s_cache', $baseKey, $this->getUser()->getId());
    }

    private function getTags(array $baseKeys): array
    {
        return array_map(fn ($baseKey) => $this->getTag($baseKey), $baseKeys);
    }

    protected function getUserTag(): string
    {
        return \sprintf('user_%s_cache', $this->getUser()->getId());
    }

    private function buildTags(array $baseKeys): array
    {
        return [
            ...$this->getTags($baseKeys),
            $this->getUserTag(),
        ];
    }
}
