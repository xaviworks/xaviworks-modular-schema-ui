<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Checkbox extends Field
{
    public function type(): string
    {
        return 'checkbox';
    }
}
