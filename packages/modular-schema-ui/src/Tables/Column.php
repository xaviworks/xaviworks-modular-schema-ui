<?php

namespace XaviWorks\ModularSchemaUi\Tables;

use XaviWorks\ModularSchemaUi\Contracts\Column as ColumnContract;

abstract class Column implements ColumnContract
{
    protected string $label;

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

    protected function valueFrom(mixed $record): mixed
    {
        return data_get($record, $this->name);
    }

    public function displayValue(mixed $record): string
    {
        return (string) ($this->valueFrom($record) ?? '');
    }
}
