<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateAnimalTypeDto
{
    #[Assert\NotBlank()]
    #[Assert\Length(
        max: 50,
    )]
    public string $name;
}
