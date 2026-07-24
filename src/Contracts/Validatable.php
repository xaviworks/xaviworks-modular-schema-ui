<?php

namespace XaviWorks\ModularSchemaUi\Contracts;

interface Validatable
{
    /** @return list<string> */
    public function validationRules(): array;
}
