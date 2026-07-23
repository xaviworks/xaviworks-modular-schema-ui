<?php

namespace XaviWorks\ModularSchemaUi\Tables;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use XaviWorks\ModularSchemaUi\Contracts\Column as ColumnContract;
use XaviWorks\ModularSchemaUi\Contracts\Filter as FilterContract;

final class Table
{
    /** @var Collection<int, ColumnContract> */
    private Collection $columns;

    /** @var Collection<int, mixed> */
    private Collection $records;

    /** @var Collection<int, FilterContract> */
    private Collection $filters;

    private string $emptyMessage = 'No records found.';

    private ?LengthAwarePaginator $paginator = null;

    /** @var list<int> */
    private array $perPageOptions = [10, 25, 50];

    public function __construct()
    {
        $this->columns = collect();
        $this->records = collect();
        $this->filters = collect();
    }

    public static function make(): self
    {
        return new self;
    }

    /** @param array<int, ColumnContract> $columns */
    public function columns(array $columns): self
    {
        $this->columns = collect($columns);

        return $this;
    }

    /** @param iterable<mixed> $records */
    public function records(iterable $records): self
    {
        $this->records = collect($records);

        return $this;
    }

    public function paginate(LengthAwarePaginator $paginator): self
    {
        $this->paginator = $paginator;
        $this->records = collect($paginator->items());

        return $this;
    }

    /** @param list<int> $options */
    public function perPageOptions(array $options): self
    {
        $this->perPageOptions = array_values(array_filter(
            $options,
            fn (mixed $option): bool => is_int($option) && $option > 0,
        ));

        return $this;
    }

    /** @param array<int, FilterContract> $filters */
    public function filters(array $filters): self
    {
        $this->filters = collect($filters);

        return $this;
    }

    public function emptyMessage(string $message): self
    {
        $this->emptyMessage = $message;

        return $this;
    }

    /** @return Collection<int, ColumnContract> */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /** @return Collection<int, mixed> */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function getPaginator(): ?LengthAwarePaginator
    {
        return $this->paginator;
    }

    /** @return list<int> */
    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    /** @return list<string> */
    public function sortableColumnNames(): array
    {
        return $this->columns
            ->filter(fn (ColumnContract $column): bool => $column->isSortable())
            ->map(fn (ColumnContract $column): string => $column->name())
            ->values()
            ->all();
    }

    /** @return list<string> */
    public function searchableColumnNames(): array
    {
        return $this->columns
            ->filter(fn (ColumnContract $column): bool => $column->isSearchable())
            ->map(fn (ColumnContract $column): string => $column->name())
            ->values()
            ->all();
    }

    /** @return Collection<int, FilterContract> */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    /** @return list<string> */
    public function filterNames(): array
    {
        return $this->filters
            ->map(fn (FilterContract $filter): string => $filter->name())
            ->values()
            ->all();
    }

    public function emptyStateMessage(): string
    {
        return $this->emptyMessage;
    }
}
