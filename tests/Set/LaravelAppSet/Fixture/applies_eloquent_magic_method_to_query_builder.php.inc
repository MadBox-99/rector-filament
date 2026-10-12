<?php

namespace Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Fixture;

use Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Source\SomeModel;

class AppliesEloquentMagicMethodToQueryBuilder
{
    public function active(): ?SomeModel
    {
        return SomeModel::where('is_active', true)->first();
    }
}

?>
-----
<?php

namespace Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Fixture;

use Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Source\SomeModel;

class AppliesEloquentMagicMethodToQueryBuilder
{
    public function active(): ?SomeModel
    {
        return SomeModel::query()->where('is_active', true)->first();
    }
}

?>
