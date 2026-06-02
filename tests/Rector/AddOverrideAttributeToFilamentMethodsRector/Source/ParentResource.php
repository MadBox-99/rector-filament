<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source;

class ParentResource
{
    public static function getNavigationLabel(): string
    {
        return 'parent';
    }
}
