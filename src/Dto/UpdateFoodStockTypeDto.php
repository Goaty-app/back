<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateFoodStockTypeDto
{
    #[Assert\Length(
        max: 50,
    )]
    public ?string $name = null;
}
