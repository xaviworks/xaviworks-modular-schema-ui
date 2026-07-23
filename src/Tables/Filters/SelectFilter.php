<?php

namespace XaviWorks\ModularSchemaUi\Tables\Filters;

use Illuminate\Database\Query\Builder;

final class SelectFilter extends Filter
{
    /** @var array<string|int, mixed> */
    private array $options = [];

    /** @param array<string|int, mixed> $options */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        $allowedValues = array_map('strval', array_keys($this->options));

        if (! is_scalar($value) || ! in_array((string) $value, $allowedValues, true)) {
            return $query;
        }

        return $query->where($this->name, (string) $value);
    }

    public function type(): string
    {
        return 'select';
    }

    /** @return array<string|int, mixed> */
    public function optionValues(): array
    {
        return $this->options;
    }
}
