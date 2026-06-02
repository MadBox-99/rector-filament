<?php

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source\TableContract;

class AddsOverrideWhenImplementingInterface implements TableContract
{
    public function table(object $table): object
    {
        return $table;
    }
}

?>
-----
<?php

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source\TableContract;

class AddsOverrideWhenImplementingInterface implements TableContract
{
    #[\Override]
    public function table(object $table): object
    {
        return $table;
    }
}

?>
