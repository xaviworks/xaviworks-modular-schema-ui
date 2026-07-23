<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Hidden extends Field
{
    public function type(): string
    {
        return 'hidden';
    }
}
