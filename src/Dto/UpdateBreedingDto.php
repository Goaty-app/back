<?php

namespace App\Dto;

use App\Enum\BreedingStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateBreedingDto
{
    #[Assert\Positive()]
    public ?int $femaleId = null;

    #[Assert\Positive()]
    public ?int $maleId = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    public ?string $matingDateStart = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    public ?string $matingDateEnd = null;

    #[Assert\PositiveOrZero]
    public ?int $expectedChildCount = null;

    #[Assert\Choice(callback: [BreedingStatus::class, 'enumValues'], message: 'assert.choice')]
    public ?string $status = null;
}
