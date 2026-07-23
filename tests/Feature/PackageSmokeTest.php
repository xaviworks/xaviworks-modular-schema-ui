<?php

namespace XaviWorks\ModularSchemaUi\Tests\Feature;

use XaviWorks\ModularSchemaUi\Tests\TestCase;

final class PackageSmokeTest extends TestCase
{
    public function test_package_views_are_loaded(): void
    {
        $this->assertTrue(view()->exists('modular-schema-ui::forms.form'));
        $this->assertTrue(view()->exists('modular-schema-ui::tables.table'));
    }
}
