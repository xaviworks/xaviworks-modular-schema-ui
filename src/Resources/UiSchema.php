<?php

namespace XaviWorks\ModularSchemaUi\Resources;

use LogicException;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Tables\Table;

/**
 * Base class for feature-level UI definitions.
 *
 * A feature can expose form methods such as form(), createForm(), and
 * editForm(), as well as table methods such as table() and archiveTable().
 */
abstract class UiSchema
{
    public function resolveForm(string $name = 'default', ?Form $form = null): Form
    {
        $method = $name === 'default' ? 'form' : "{$name}Form";

        if (! is_callable([$this, $method])) {
            throw new LogicException("UI form [{$name}] is not defined on ".static::class.'.');
        }

        /** @var callable(Form): Form $definition */
        $definition = [$this, $method];

        return $definition($form ?? Form::make());
    }

    public function resolveTable(string $name = 'default', ?Table $table = null): Table
    {
        $method = $name === 'default' ? 'table' : "{$name}Table";

        if (! is_callable([$this, $method])) {
            throw new LogicException("UI table [{$name}] is not defined on ".static::class.'.');
        }

        /** @var callable(Table): Table $definition */
        $definition = [$this, $method];

        return $definition($table ?? Table::make());
    }
}
