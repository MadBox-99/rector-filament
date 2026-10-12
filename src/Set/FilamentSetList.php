<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Set;

final class FilamentSetList
{
    public const FILAMENT_CODE_QUALITY = __DIR__.'/../../config/sets/filament-code-quality.php';

    public const FILAMENT_TESTS = __DIR__.'/../../config/sets/filament-tests.php';

    /**
     * Laravel code quality, collection, type declaration and if-helper sets
     * together with both Filament sets.
     */
    public const LARAVEL_APP = __DIR__.'/../../config/sets/laravel-app.php';
}
