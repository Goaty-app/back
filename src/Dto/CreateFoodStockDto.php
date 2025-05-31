<?php

namespace App\Dto;

use App\Enum\QuantityUnit;
use Symfony\Component\Validator\Constraints as Assert;

class CreateFoodStockDto
{
    #[Assert\NotBlank()]
    #[Assert\Length(
        max: 255,
    )]
    public string $name;

    #[Assert\NotBlank()]
    #[Assert\Choice(callback: [QuantityUnit::class, 'enumValues'], message: 'assert.choice')]
    public string $quantityUnit;

    #[Assert\NotNull()]
    #[Assert\Positive()]
    public int $foodStockTypeId;
}
