<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Rector\AddOverrideAttributeToFilamentMethodsRector;
use Madbox99\RectorFilament\Rector\FinalizeFilamentResourceRector;
use Madbox99\RectorFilament\Rector\HeroiconStringToEnumRector;
use Madbox99\RectorFilament\Rector\ImproveNavigationIconPropertyTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        HeroiconStringToEnumRector::class,
        AddOverrideAttributeToFilamentMethodsRector::class,
        FinalizeFilamentResourceRector::class,
        ImproveNavigationIconPropertyTypeRector::class,
    ]);
