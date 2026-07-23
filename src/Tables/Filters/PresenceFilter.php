<?php

namespace XaviWorks\ModularSchemaUi\Tables\Filters;

use Illuminate\Database\Query\Builder;

final class PresenceFilter extends Filter
{
    public function type(): string
    {
        return 'presence';
    }

    /** @return array<int, string> */
    public function optionValues(): array
    {
        return [1 => 'Present', 0 => 'Not present'];
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        return match ((string) $value) {
            '1', 'true', 'yes' => $query->whereNotNull($this->name),
            '0', 'false', 'no' => $query->whereNull($this->name),
            default => $query,
        };
    }
}
