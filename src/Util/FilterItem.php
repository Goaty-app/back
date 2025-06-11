<?php

namespace App\Util;

class FilterItem
{
    public function __construct(
        private readonly string $column,
        private readonly string|int $value,
        private readonly string $operator,
    ) {
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getValue(): string|int
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
