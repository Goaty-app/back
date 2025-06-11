<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UniqueUserEmail extends Constraint
{
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
