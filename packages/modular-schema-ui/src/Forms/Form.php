<?php

namespace XaviWorks\ModularSchemaUi\Forms;

use Illuminate\Support\Collection;
use XaviWorks\ModularSchemaUi\Contracts\Field as FieldContract;

final class Form
{
    /** @var Collection<int, FieldContract> */
    private Collection $fields;

    private mixed $data = null;

    public function __construct()
    {
        $this->fields = collect();
    }

    public static function make(): self
    {
        return new self;
    }

    /** @param array<int, FieldContract> $fields */
    public function fields(array $fields): self
    {
        $this->fields = collect($fields);

        return $this;
    }

    public function model(mixed $model): self
    {
        $this->data = $model;

        return $this;
    }

    /** @param array<string, mixed>|object $values */
    public function values(array|object $values): self
    {
        $this->data = $values;

        return $this;
    }

    public function valueFor(FieldContract $field): mixed
    {
        if (! $field->canRestoreValue()) {
            return null;
        }

        $fallback = $field->defaultValue();

        if ($this->data !== null) {
            $fallback = data_get($this->data, $field->name(), $fallback);
        }

        return old($field->name(), $fallback);
    }

    /** @return Collection<int, FieldContract> */
    public function getFields(): Collection
    {
        return $this->fields;
    }
}
