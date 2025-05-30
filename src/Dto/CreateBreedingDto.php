<?php

namespace App\Dto;

use App\Enum\BreedingStatus;
use Symfony\Component\Validator\Constraints as Assert;

class CreateBreedingDto
{
    #[Assert\NotNull()]
    #[Assert\Positive()]
    public int $femaleId;

    #[Assert\NotNull()]
    #[Assert\Positive()]
    public int $maleId;

    #[Assert\DateTime(format: 'Y-m-d H:i:s')]
    public ?string $matingDateStart = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s')]
    public ?string $matingDateEnd = null;

    #[Assert\PositiveOrZero]
    public ?int $expectedChildCount = null;

    #[Assert\Choice(callback: [BreedingStatus::class, 'enumValues'])]
    public ?string $status = null;
}
