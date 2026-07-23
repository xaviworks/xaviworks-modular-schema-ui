<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

interface Column
{
    public function name(): string;

    public function labelText(): string;

    public function type(): string;

    public function displayValue(mixed $record): string;
}
