<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateHerdDto
{
    #[Assert\Length(
        max: 50,
    )]
    public ?string $name = null;

    #[Assert\Length(
        max: 100,
    )]
    public ?string $location = null;
}
