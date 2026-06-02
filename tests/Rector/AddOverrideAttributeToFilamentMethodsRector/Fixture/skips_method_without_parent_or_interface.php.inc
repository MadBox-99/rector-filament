<?php

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Fixture;

class SkipsMethodWithoutParentOrInterface
{
    public function table(object $table): object
    {
        return $table;
    }
}
