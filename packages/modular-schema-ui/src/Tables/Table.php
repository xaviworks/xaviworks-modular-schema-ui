<?php

namespace XaviWorks\ModularSchemaUi\Tables;

use Illuminate\Support\Collection;
use XaviWorks\ModularSchemaUi\Contracts\Column as ColumnContract;

final class Table
{
    /** @var Collection<int, ColumnContract> */
    private Collection $columns;

    /** @var Collection<int, mixed> */
    private Collection $records;

    private string $emptyMessage = 'No records found.';

    public function __construct()
    {
        $this->columns = collect();
        $this->records = collect();
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

    public function emptyStateMessage(): string
    {
        return $this->emptyMessage;
    }
}
