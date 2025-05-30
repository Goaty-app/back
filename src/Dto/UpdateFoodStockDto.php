<?php

namespace App\Dto;

use App\Enum\QuantityUnit;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateFoodStockDto
{
    #[Assert\Positive()]
    public ?int $herdId = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $name = null;

    #[Assert\Choice(callback: [QuantityUnit::class, 'enumValues'])]
    public ?string $quantityUnit = null;

    #[Assert\Positive()]
    public ?int $foodStockTypeId = null;
}
