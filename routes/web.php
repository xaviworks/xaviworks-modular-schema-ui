<?php

use Illuminate\Support\Facades\Route;
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;

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
