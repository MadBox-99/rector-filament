<?php

namespace Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Fixture;

class AppliesLaravelIfHelpers
{
    public function show(bool $isVisible): void
    {
        if (! $isVisible) {
            abort(404);
        }
    }
}

?>
-----
<?php

namespace Madbox99\RectorFilament\Tests\Set\LaravelAppSet\Fixture;

class AppliesLaravelIfHelpers
{
    public function show(bool $isVisible): void
    {
        abort_unless($isVisible, 404);
    }
}

?>
