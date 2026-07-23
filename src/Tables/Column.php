<?php

namespace XaviWorks\ModularSchemaUi\Tables;

use XaviWorks\ModularSchemaUi\Contracts\Column as ColumnContract;

abstract class Column implements ColumnContract
{
    protected string $label;

    protected bool $sortable = false;

    protected bool $searchable = false;

    public function __construct(protected string $name)
    {
        $this->label = str($name)->headline()->toString();
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function labelText(): string
    {
        return $this->label;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    protected function valueFrom(mixed $record): mixed
    {
        return data_get($record, $this->name);
    }

    public function displayValue(mixed $record): string
    {
        return (string) ($this->valueFrom($record) ?? '');
    }
}
