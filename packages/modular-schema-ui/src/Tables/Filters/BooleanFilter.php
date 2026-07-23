<?php

namespace XaviWorks\ModularSchemaUi\Tables\Filters;

use Illuminate\Database\Query\Builder;

final class BooleanFilter extends Filter
{
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
