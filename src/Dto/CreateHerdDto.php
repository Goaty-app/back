<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateHerdDto
{
    #[Assert\NotBlank()]
    #[Assert\Length(
        max: 50,
    )]
    public string $name;

    #[Assert\Length(
        max: 100,
    )]
    public ?string $location = null;
}
