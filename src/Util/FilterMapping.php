<?php

namespace App\Util;

use App\Contract\FilterRuleInterface;
use App\Util\FilterRule\LikeFilterRule;
use Symfony\Component\HttpFoundation\Request;

class FilterMapping
{
    private array $mapping = [];

    public function __construct(
        private Request $request,
    ) {
    }

    public function add(
        string $name,
        ?FilterRuleInterface $rule = new LikeFilterRule(),
    ): self {
        $requestValue = trim($this->request->query->get($name) ?? '');
        if (empty($requestValue)) {
            return $this;
        }

        $this->mapping[] = new FilterItem(
            $name,
            $rule->formatValue($requestValue),
            $rule->getOperator(),
        );

        return $this;
    }

    public function get(): array
    {
        return $this->mapping;
    }
}
