<?php

declare(strict_types=1);

use Cegem360\RectorFilament\Rector\AddOverrideAttributeToFilamentMethodsRector;
use Cegem360\RectorFilament\Rector\FinalizeFilamentResourceRector;
use Cegem360\RectorFilament\Rector\HeroiconStringToEnumRector;
use Cegem360\RectorFilament\Rector\ImproveNavigationIconPropertyTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        HeroiconStringToEnumRector::class,
        AddOverrideAttributeToFilamentMethodsRector::class,
        FinalizeFilamentResourceRector::class,
        ImproveNavigationIconPropertyTypeRector::class,
    ]);
