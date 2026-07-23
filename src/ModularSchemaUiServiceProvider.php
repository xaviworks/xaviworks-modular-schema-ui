<?php

namespace XaviWorks\ModularSchemaUi;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use XaviWorks\ModularSchemaUi\View\Components\Form;
use XaviWorks\ModularSchemaUi\View\Components\Table;

final class ModularSchemaUiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'modular-schema-ui');

        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade): void {
            $blade->component('modular-schema-ui::form', Form::class);
            $blade->component('modular-schema-ui::table', Table::class);
        });
    }
}
