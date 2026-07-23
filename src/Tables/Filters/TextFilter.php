<?php

namespace XaviWorks\ModularSchemaUi\Tables\Filters;

use Illuminate\Database\Query\Builder;

final class TextFilter extends Filter
{
    public function type(): string
    {
        return 'text';
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (! is_scalar($value) || trim((string) $value) === '') {
            return $query;
        }

        $value = addcslashes(trim((string) $value), '%_\\');

        return $query->where($this->name, 'like', "%{$value}%");
    }
}
