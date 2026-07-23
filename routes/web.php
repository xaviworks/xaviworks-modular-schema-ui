<?php

use Illuminate\Support\Facades\Route;
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Tables\Columns\BooleanColumn;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Table;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/modular/forms', function () {
    return view('modular-demo', [
        'form' => Form::make()->fields([
            Text::make('name')->label('Full Name')->required(),
            Email::make('email')->label('Email Address')->required(),
        ]),
    ]);
});

Route::get('/modular/table', function () {
    return view('modular-table-demo', [
        'table' => Table::make()
            ->records([
                ['name' => 'Junn Xavier', 'email' => 'junn@example.test', 'active' => true],
                ['name' => 'XaviWorks', 'email' => 'hello@example.test', 'active' => false],
            ])
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('email')->label('Email'),
                BooleanColumn::make('active')->label('Status'),
            ]),
    ]);
});
