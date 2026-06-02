<?php

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source\ParentResource;

class AddsOverrideWhenOverridingParent extends ParentResource
{
    public static function getNavigationLabel(): string
    {
        return 'child';
    }
}

?>
-----
<?php

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source\ParentResource;

class AddsOverrideWhenOverridingParent extends ParentResource
{
    #[\Override]
    public static function getNavigationLabel(): string
    {
        return 'child';
    }
}

?>
