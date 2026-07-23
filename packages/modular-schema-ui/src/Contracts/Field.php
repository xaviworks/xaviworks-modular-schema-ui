<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

interface Field
{
    public function name(): string;

    public function labelText(): string;

    public function type(): string;

    public function isRequired(): bool;

    /** @return array<string, mixed> */
    public function htmlAttributes(): array;
}
