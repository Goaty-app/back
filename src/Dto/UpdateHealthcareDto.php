<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateHealthcareDto
{
    #[Assert\Positive()]
    public ?int $healthcareTypeId = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $description = null;
}
