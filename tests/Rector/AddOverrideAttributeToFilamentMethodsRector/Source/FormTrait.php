<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source;

trait FormTrait
{
    public function form(object $schema): object
    {
        return $schema;
    }
}
