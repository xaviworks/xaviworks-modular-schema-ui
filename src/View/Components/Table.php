<?php

namespace XaviWorks\ModularSchemaUi\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Table as ModularTable;

final class Table extends Component
{
    public function __construct(
        public ModularTable $table,
        public ?RequestState $state = null,
        public ?string $filterAction = null,
    ) {}

    public function render(): View
    {
        return view('modular-schema-ui::tables.table');
    }
}
