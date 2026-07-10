<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Rector\CollectTestCasePropertiesRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(CollectTestCasePropertiesRector::class, [
        CollectTestCasePropertiesRector::TEST_CASE_CLASS => 'Madbox99\\RectorFilament\\Tests\\Rector\\CollectTestCasePropertiesRector\\Fixture\\SampleTestCase',
        CollectTestCasePropertiesRector::SCAN_PATHS => [__DIR__.'/../Source'],
    ]);
