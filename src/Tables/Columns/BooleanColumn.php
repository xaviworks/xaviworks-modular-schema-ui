<?php

namespace XaviWorks\ModularSchemaUi\Tables\Columns;

use XaviWorks\ModularSchemaUi\Tables\Column;

final class BooleanColumn extends Column
{
    private string $trueLabel = 'Yes';

    private string $falseLabel = 'No';

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;

        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;

        return $this;
    }

    public function type(): string
    {
        return 'boolean';
    }

    public function displayValue(mixed $record): string
    {
        return (bool) $this->valueFrom($record) ? $this->trueLabel : $this->falseLabel;
    }
}
