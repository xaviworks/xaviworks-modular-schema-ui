<?php

namespace XaviWorks\ModularSchemaUi\Forms;

use XaviWorks\ModularSchemaUi\Contracts\Field as FieldContract;

abstract class Field implements FieldContract
{
    protected string $label;

    protected bool $required = false;

    protected mixed $defaultValue = null;

    /** @var array<string, mixed> */
    protected array $attributes = [];

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

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    /** @param array<string, mixed> $attributes */
    public function attributes(array $attributes): static
    {
        $this->attributes = [...$this->attributes, ...$attributes];

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

    public function isRequired(): bool
    {
        return $this->required;
    }

    /** @return array<string|int, mixed> */
    public function optionValues(): array
    {
        return [];
    }

    public function canRestoreValue(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /** @return array<string, mixed> */
    public function htmlAttributes(): array
    {
        return $this->attributes;
    }
}
