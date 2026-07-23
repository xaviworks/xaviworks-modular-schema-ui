<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

interface Action
{
    public function name(): string;

    public function labelText(): string;

    public function method(): string;

    public function urlTemplate(): ?string;

    public function confirmationMessage(): ?string;

    /** @return array<string, mixed> */
    public function toArray(): array;
}
