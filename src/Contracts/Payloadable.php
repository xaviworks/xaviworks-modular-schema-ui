<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

interface Payloadable
{
    /** @return array<string, mixed> */
    public function toArray(): array;
}
