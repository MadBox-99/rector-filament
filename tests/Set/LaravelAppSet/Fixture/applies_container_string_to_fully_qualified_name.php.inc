<?php

namespace Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Fixture;

class AppliesContainerStringToFullyQualifiedName
{
    public function cache(): mixed
    {
        return app('cache');
    }
}

?>
-----
<?php

namespace Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Fixture;

class AppliesContainerStringToFullyQualifiedName
{
    public function cache(): mixed
    {
        return resolve(\Illuminate\Contracts\Cache\Factory::class);
    }
}

?>
