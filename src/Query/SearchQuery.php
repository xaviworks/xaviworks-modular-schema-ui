<?php

namespace XaviWorks\ModularSchemaUi\Query;

use Illuminate\Database\Query\Builder;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Table;

final class SearchQuery
{
    public function apply(Builder $query, Table $table, RequestState $state): Builder
    {
        $search = $state->search();
        $columns = $table->searchableColumnNames();

        if ($search === null || $columns === []) {
            return $query;
        }

        $search = addcslashes($search, '%_\\');

        return $query->where(function (Builder $query) use ($columns, $search): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}($column, 'like', "%{$search}%");
            }
        });
    }
}
