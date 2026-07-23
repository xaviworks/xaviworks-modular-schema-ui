<?php

namespace XaviWorks\ModularSchemaUi\Forms;

use XaviWorks\ModularSchemaUi\Contracts\Field as FieldContract;

abstract class Field implements FieldContract
{
    protected string $label;

    protected bool $required = false;

    protected mixed $defaultValue = null;

    protected ?string $placeholder = null;

    protected ?string $help = null;

    protected bool $readonly = false;

    protected bool $disabled = false;

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

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function helpText(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

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

    public function placeholderText(): ?string
    {
        return $this->placeholder;
    }

    public function helpTextValue(): ?string
    {
        return $this->help;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /** @return array<string, mixed> */
    public function htmlAttributes(): array
    {
        return $this->attributes;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'label' => $this->labelText(),
            'type' => $this->type(),
            'required' => $this->isRequired(),
            'default' => $this->canRestoreValue() ? $this->defaultValue() : null,
            'placeholder' => $this->placeholderText(),
            'help' => $this->helpTextValue(),
            'readonly' => $this->isReadonly(),
            'disabled' => $this->isDisabled(),
            'attributes' => $this->htmlAttributes(),
            'options' => $this->optionValues(),
        ];
    }
}
