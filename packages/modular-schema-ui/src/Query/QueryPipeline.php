<?php

namespace XaviWorks\ModularSchemaUi\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use XaviWorks\ModularSchemaUi\Contracts\Filter;
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

    public function search(Builder $query, Table $table, RequestState $state): Builder
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

    public function filters(Builder $query, Table $table, RequestState $state): Builder
    {
        $values = $state->filters();

        foreach ($table->getFilters() as $filter) {
            /** @var Filter $filter */
            if (array_key_exists($filter->name(), $values)) {
                $query = $filter->apply($query, $values[$filter->name()]);
            }
        }

        return $query;
    }

    /** @param list<int> $perPageOptions */
    public function paginate(
        Builder $query,
        RequestState $state,
        array $perPageOptions = [10, 25, 50],
        int $maximumPerPage = 100,
    ): LengthAwarePaginator {
        $allowedPerPage = array_values(array_filter(
            $perPageOptions,
            fn (int $option): bool => $option > 0 && $option <= $maximumPerPage,
        ));

        $perPage = in_array($state->perPage(), $allowedPerPage, true)
            ? $state->perPage()
            : ($allowedPerPage[0] ?? min(15, $maximumPerPage));

        return $query
            ->paginate($perPage, ['*'], 'page', $state->page())
            ->withQueryString();
    }
}
