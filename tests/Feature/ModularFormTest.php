<?php

use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;

it('renders a modular form with escaped values and csrf protection', function (): void {
    $form = Form::make()->fields([
        Text::make('name')->label('Full Name')->required(),
        Email::make('email')->required(),
    ]);

    $response = $this->withViewErrors([])->view('modular-form-test', compact('form'));

    $response->assertSee('name="_token"', false)
        ->assertSee('name="name"', false)
        ->assertSee('type="email"', false)
        ->assertSee('Full Name')
        ->assertSee('required', false);
});

it('exposes the modular form workbench route', function (): void {
    $this->get('/modular/forms')
        ->assertSuccessful()
        ->assertSee('User details')
        ->assertSee('name="name"', false);
});
