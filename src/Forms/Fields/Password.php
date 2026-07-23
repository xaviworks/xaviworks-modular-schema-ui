<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Password extends Field
{
    public function type(): string
    {
        return 'password';
    }

    public function canRestoreValue(): bool
    {
        return false;
    }
}
