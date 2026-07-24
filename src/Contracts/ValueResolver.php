<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

interface ValueResolver
{
    public function valueFor(mixed $record): mixed;
}
