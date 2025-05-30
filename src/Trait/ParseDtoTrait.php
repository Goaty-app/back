<?php

namespace App\Trait;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

trait ParseDtoTrait
{
    private function createWithDto(mixed $dto, string $type): mixed
    {
        return $this->denormalizer->denormalize(
            $dto,
            $type,
        );
    }

    private function updateWithDto(mixed $dto, string $type, $objectToPopulate): mixed
    {
        return $this->serializer->deserialize(
            $this->parseDto($dto),
            $type,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $objectToPopulate,
            ],
        );
    }

    private function parseDto(mixed $dto): string
    {
        return $this->serializer->serialize($dto, 'json', [
            ObjectNormalizer::SKIP_NULL_VALUES => true,
        ]);
    }
}
