<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateBirthDto
{
    #[Assert\Positive()]
    public ?int $childId = null;

    #[Assert\Positive()]
    public ?int $breedingId = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s')]
    public ?string $birthDate = null;

    #[Assert\Type(type: 'float')]
    #[Assert\PositiveOrZero]
    public ?float $birthWeight = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $notes = null;
}
