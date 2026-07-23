<?php

namespace XaviWorks\ModularSchemaUi\Tables\Columns;

use XaviWorks\ModularSchemaUi\Tables\Column;

final class TextColumn extends Column
{
    public function type(): string
    {
        return 'text';
    }
}
