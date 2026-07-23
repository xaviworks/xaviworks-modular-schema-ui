<?php

use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Hidden;
use XaviWorks\ModularSchemaUi\Forms\Fields\Password;
use XaviWorks\ModularSchemaUi\Forms\Fields\Select;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Fields\Textarea;
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

it('renders the expanded modular field set safely', function (): void {
    $form = Form::make()->fields([
        Textarea::make('bio'),
        Select::make('role')->options([
            'admin' => 'Administrator',
            'user' => 'User',
        ]),
        Password::make('password'),
        Hidden::make('token'),
    ]);

    $response = $this->withViewErrors([])->view('modular-form-test', compact('form'));

    $response->assertSee('<textarea', false)
        ->assertSee('Administrator')
        ->assertSee('value="admin"', false)
        ->assertSee('type="password"', false)
        ->assertSee('type="hidden"', false);
});

it('resolves model values and field defaults without populating passwords', function (): void {
    $form = Form::make()
        ->model((object) [
            'name' => 'Junn Xavier',
            'bio' => 'Building modular Laravel tools.',
            'role' => 'admin',
            'password' => 'never-render-this',
        ])
        ->fields([
            Text::make('name'),
            Textarea::make('bio'),
            Select::make('role')->options(['admin' => 'Administrator']),
            Password::make('password'),
            Text::make('fallback')->default('Default value'),
        ]);

    $response = $this->withViewErrors([])->view('modular-form-test', compact('form'));

    $response->assertSee('value="Junn Xavier"', false)
        ->assertSee('Building modular Laravel tools.')
        ->assertSee('value="admin" selected', false)
        ->assertSee('value="Default value"', false)
        ->assertDontSee('never-render-this');
});
