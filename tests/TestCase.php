<?php

namespace XaviWorks\ModularSchemaUi\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use XaviWorks\ModularSchemaUi\ModularSchemaUiServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ModularSchemaUiServiceProvider::class];
    }
}
