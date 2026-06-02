<?php

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source\FormTrait;

class SkipsMethodProvidedOnlyByTrait
{
    use FormTrait;

    public function form(object $schema): object
    {
        return $schema;
    }
}
