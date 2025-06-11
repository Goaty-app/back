<?php

namespace App\Util\FilterRule;

use App\Contract\FilterRuleInterface;

class LikeFilterRule implements FilterRuleInterface
{
    public function getOperator(): string
    {
        return 'LIKE';
    }

    public function formatValue(string $value): string
    {
        return '%'.$value.'%';
    }
}
