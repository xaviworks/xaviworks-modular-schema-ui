<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class DateTime extends Field
{
    public function type(): string
    {
        return 'datetime-local';
    }
}
