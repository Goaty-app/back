<?php

namespace App\Dto;

use App\Enum\Operation;
use Symfony\Component\Validator\Constraints as Assert;

class CreateFoodStockHistoryDto
{
    #[Assert\NotBlank()]
    #[Assert\PositiveOrZero]
    public float $quantity;

    #[Assert\NotBlank()]
    #[Assert\Choice(callback: [Operation::class, 'enumValues'])]
    public string $operation;
}
