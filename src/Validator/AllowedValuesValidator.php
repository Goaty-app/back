<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AllowedValuesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AllowedValues) {
            throw new UnexpectedTypeException($constraint, AllowedValues::class);
        }

        if (empty($value)) {
            return;
        }

        if (!\in_array($value, $constraint->allowed, true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ allowed_values }}', implode(', ', $constraint->allowed))
                ->addViolation()
            ;
        }
    }
}
