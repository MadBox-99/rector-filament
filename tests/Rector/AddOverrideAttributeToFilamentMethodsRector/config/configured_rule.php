<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Rector\AddOverrideAttributeToFilamentMethodsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([AddOverrideAttributeToFilamentMethodsRector::class]);
