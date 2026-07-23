<?php

namespace XaviWorks\ModularSchemaUi\Tables\Filters;

use XaviWorks\ModularSchemaUi\Contracts\Filter as FilterContract;

abstract class Filter implements FilterContract
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
}
