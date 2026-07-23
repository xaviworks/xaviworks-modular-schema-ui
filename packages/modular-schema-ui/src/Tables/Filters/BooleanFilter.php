<?php

namespace XaviWorks\ModularSchemaUi\Tables\Filters;

use Illuminate\Database\Query\Builder;

final class BooleanFilter extends Filter
{
    public function type(): string
    {
        return 'boolean';
    }

    /** @return array<int, string> */
    public function optionValues(): array
    {
        return [1 => 'Yes', 0 => 'No'];
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        $normalized = match (strtolower((string) $value)) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => null,
        };

        if ($normalized === null) {
            return $query;
        }

        return $query->where($this->name, $normalized);
    }
}
