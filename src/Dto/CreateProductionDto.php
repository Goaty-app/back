<?php

namespace App\Dto;

use App\Enum\QuantityUnit;
use Symfony\Component\Validator\Constraints as Assert;

class CreateProductionDto
{
    #[Assert\NotBlank()]
    #[Assert\DateTime(format: 'Y-m-d H:i:s')]
    public string $production_date;

    #[Assert\NotBlank()]
    public float $quantity;

    #[Assert\NotBlank()]
    #[Assert\Choice(callback: [QuantityUnit::class, 'enumValues'])]
    public string $quantityUnit;

    #[Assert\NotBlank()]
    #[Assert\Positive()]
    public int $productionTypeId;

    #[Assert\DateTime(format: 'Y-m-d H:i:s')]
    public ?string $expiration_date = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $notes = null;
}
