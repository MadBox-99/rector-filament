<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Set\FilamentSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([FilamentSetList::LARAVEL_APP]);
