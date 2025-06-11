<?php

namespace App\Util\FilterRule;

use App\Contract\FilterRuleInterface;

class EqualFilterRule implements FilterRuleInterface
{
    public function getOperator(): string
    {
        return '=';
    }

    public function formatValue(string $value): string
    {
        return $value;
    }
}
