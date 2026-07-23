<?php

namespace XaviWorks\ModularSchemaUi\Query;

use Illuminate\Database\Query\Builder;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Table;

final class QueryPipeline
{
    public static function make(): self
    {
        return new self;
    }

    public function sort(Builder $query, Table $table, RequestState $state): Builder
    {
        $sort = $state->sort();

        if ($sort === null || ! in_array($sort, $table->sortableColumnNames(), true)) {
            return $query;
        }

        return $query->orderBy($sort, $state->direction());
    }
}
