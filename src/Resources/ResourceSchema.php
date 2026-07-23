<?php

namespace XaviWorks\ModularSchemaUi\Resources;

use Illuminate\Database\Query\Builder;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Query\QueryPipeline;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Table;

abstract class ResourceSchema
{
    public function form(Form $form): Form
    {
        return $form;
    }

    public function table(Table $table): Table
    {
        return $table;
    }

    public function resolveForm(): Form
    {
        return $this->form(Form::make());
    }

    public function resolveTable(Builder $query, RequestState $state): Table
    {
        $table = $this->table(Table::make());
        $paginator = QueryPipeline::make()->resolve(
            $query,
            $table,
            $state,
            $table->getPerPageOptions(),
        );

        return $table->paginate($paginator);
    }
}
