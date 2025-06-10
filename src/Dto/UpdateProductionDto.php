<?php

namespace App\Dto;

use App\Enum\QuantityUnit;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateProductionDto
{
    #[Assert\Positive()]
    public ?int $herdId = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    public ?string $productionDate = null;

    public ?float $quantity = null;

    #[Assert\Choice(callback: [QuantityUnit::class, 'enumValues'], message: 'assert.choice')]
    public ?string $quantityUnit = null;

    #[Assert\Positive()]
    public ?int $productionTypeId = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    public ?string $expirationDate = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $notes = null;
}
