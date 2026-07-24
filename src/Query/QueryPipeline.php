<?php

namespace XaviWorks\ModularSchemaUi\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Table;

final class QueryPipeline
{
    public static function make(): self
    {
        return new self;
    }

    public function __construct(
        private readonly SearchQuery $searchQuery = new SearchQuery,
        private readonly FilterQuery $filterQuery = new FilterQuery,
        private readonly SortQuery $sortQuery = new SortQuery,
        private readonly PaginationQuery $paginationQuery = new PaginationQuery,
    ) {}

    public function sort(Builder $query, Table $table, RequestState $state): Builder
    {
        return $this->sortQuery->apply($query, $table, $state);
    }

    public function apply(Builder $query, Table $table, RequestState $state): Builder
    {
        $query = $this->search($query, $table, $state);
        $query = $this->filters($query, $table, $state);

        return $this->sort($query, $table, $state);
    }

    public function search(Builder $query, Table $table, RequestState $state): Builder
    {
        return $this->searchQuery->apply($query, $table, $state);
    }

    public function filters(Builder $query, Table $table, RequestState $state): Builder
    {
        return $this->filterQuery->apply($query, $table, $state);
    }

    /** @param list<int> $perPageOptions */
    public function paginate(
        Builder $query,
        RequestState $state,
        array $perPageOptions = [10, 25, 50],
        int $maximumPerPage = 100,
    ): LengthAwarePaginator {
        return $this->paginationQuery->apply($query, $state, $perPageOptions, $maximumPerPage);
    }

    /** @param list<int> $perPageOptions */
    public function resolve(
        Builder $query,
        Table $table,
        RequestState $state,
        array $perPageOptions = [10, 25, 50],
        int $maximumPerPage = 100,
    ): LengthAwarePaginator {
        return $this->paginate(
            $this->apply($query, $table, $state),
            $state,
            $perPageOptions,
            $maximumPerPage,
        );
    }
}
