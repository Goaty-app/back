<?php

namespace App\Contract;

interface FilterRuleInterface
{
    public function getOperator(): string;

    public function formatValue(string $value): string;
}
