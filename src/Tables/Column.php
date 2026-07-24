<?php

namespace XaviWorks\ModularSchemaUi\Tables;

use XaviWorks\ModularSchemaUi\Contracts\Column as ColumnContract;
use XaviWorks\ModularSchemaUi\Contracts\Payloadable;
use XaviWorks\ModularSchemaUi\Contracts\ValueResolver;

/** @phpstan-consistent-constructor */
abstract class Column implements ColumnContract, Payloadable, ValueResolver
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

    public function valueFor(mixed $record): mixed
    {
        return $this->valueFrom($record);
    }

    public function displayValue(mixed $record): string
    {
        return (string) ($this->valueFrom($record) ?? '');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'label' => $this->labelText(),
            'type' => $this->type(),
            'sortable' => $this->isSortable(),
            'searchable' => $this->isSearchable(),
        ];
    }
}
