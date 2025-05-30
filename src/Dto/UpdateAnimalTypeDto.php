<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateAnimalTypeDto
{
    #[Assert\Length(
        max: 50,
    )]
    public ?string $name = null;
}
