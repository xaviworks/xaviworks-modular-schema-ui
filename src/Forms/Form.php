<?php

namespace XaviWorks\ModularSchemaUi\Forms;

use Illuminate\Support\Collection;
use XaviWorks\ModularSchemaUi\Contracts\Field as FieldContract;
use XaviWorks\ModularSchemaUi\Contracts\Payloadable;
use XaviWorks\ModularSchemaUi\Contracts\Validatable;

final class Form
{
    /** @var Collection<int, FieldContract> */
    private Collection $fields;

    private mixed $data = null;

    public function __construct()
    {
        $this->fields = collect();
    }

    public static function make(array|FieldContract ...$fields): self
    {
        return (new self)->fields(...$fields);
    }

    public function fields(array|FieldContract ...$fields): self
    {
        $this->fields = collect($this->normalizeItems($fields));

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

    /** @return array<string, list<string>> */
    public function validationRules(): array
    {
        return $this->fields
            ->filter(fn (FieldContract $field): bool => $field instanceof Validatable)
            ->mapWithKeys(fn (Validatable&FieldContract $field): array => [$field->name() => $field->validationRules()])
            ->filter(fn (array $rules): bool => $rules !== [])
            ->all();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $values = [];

        foreach ($this->fields as $field) {
            $values[$field->name()] = $this->valueFor($field);
        }

        return [
            'fields' => $this->fields
                ->map(fn (FieldContract $field): array => $field instanceof Payloadable
                    ? $field->toArray()
                    : [
                        'name' => $field->name(),
                        'label' => $field->labelText(),
                        'type' => $field->type(),
                        'required' => $field->isRequired(),
                        'placeholder' => $field->placeholderText(),
                        'help' => $field->helpTextValue(),
                        'readonly' => $field->isReadonly(),
                        'disabled' => $field->isDisabled(),
                        'options' => $field->optionValues(),
                    ])
                ->values()
                ->all(),
            'values' => $values,
        ];
    }

    /** @param array<int, array<int, FieldContract>|FieldContract> $items */
    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->flatMap(fn (array|FieldContract $item): array => is_array($item) ? $item : [$item])
            ->values()
            ->all();
    }
}
