<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Number extends Field
{
    public function type(): string
    {
        return 'number';
    }
}
