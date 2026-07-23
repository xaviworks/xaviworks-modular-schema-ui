<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Select extends Field
{
    /** @var array<string|int, mixed> */
    private array $options = [];

    /** @param array<string|int, mixed> $options */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function type(): string
    {
        return 'select';
    }

    /** @return array<string|int, mixed> */
    public function optionValues(): array
    {
        return $this->options;
    }
}
