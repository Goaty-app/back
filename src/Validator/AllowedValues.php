<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class AllowedValues extends Constraint
{
    public array $allowed = [];
    public string $message = 'The value {{ value }} is not in : {{ allowed_values }}.';

    public function __construct(array $allowed, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        $this->allowed = $allowed;
        parent::__construct([], $groups, $payload);
        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
