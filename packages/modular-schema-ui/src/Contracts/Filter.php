<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

use Illuminate\Database\Query\Builder;

interface Filter
{
    public function name(): string;

    public function labelText(): string;

    public function type(): string;

    /** @return array<string|int, mixed> */
    public function optionValues(): array;

    public function apply(Builder $query, mixed $value): Builder;
}
