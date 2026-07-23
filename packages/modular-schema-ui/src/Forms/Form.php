<?php

namespace XaviWorks\ModularSchemaUi\Forms;

use Illuminate\Support\Collection;
use XaviWorks\ModularSchemaUi\Contracts\Field as FieldContract;

final class Form
{
    /** @var Collection<int, FieldContract> */
    private Collection $fields;

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

    /** @return Collection<int, FieldContract> */
    public function getFields(): Collection
    {
        return $this->fields;
    }
}
