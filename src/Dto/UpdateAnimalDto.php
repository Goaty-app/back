<?php

namespace App\Dto;

use App\Enum\Country;
use App\Enum\Gender;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateAnimalDto
{
    #[Assert\Positive()]
    public ?int $herdId = null;

    #[Assert\Length(
        max: 50,
    )]
    public ?string $idNumber = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $status = null;

    #[Assert\Positive()]
    public ?int $animalTypeId = null;

    #[Assert\Length(
        max: 50,
    )]
    public ?string $name = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $behaviorNotes = null;

    #[Assert\Choice(callback: [Country::class, 'enumValues'], message: 'assert.choice')]
    public ?string $originCountry = null;

    #[Assert\Choice(callback: [Gender::class, 'enumValues'], message: 'assert.choice')]
    public ?string $gender = null;
}
