<?php

namespace App\Dto;

use App\Validator\AllowedValues;
use Symfony\Component\Validator\Constraints as Assert;

class AnimalStatsQueryDto
{
    #[Assert\NotBlank()]
    #[AllowedValues(
        allowed: ['originCountry', 'gender'],
        message: 'assert.query_parameters',
    )]
    public ?string $groupBy = null;
}
