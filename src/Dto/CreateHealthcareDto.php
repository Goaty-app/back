<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateHealthcareDto
{
    #[Assert\NotNull()]
    #[Assert\Positive()]
    public int $healthcareTypeId;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $description = null;
}
