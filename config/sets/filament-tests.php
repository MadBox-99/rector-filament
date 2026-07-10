<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Rector\AddTestCaseVarDocblockToPestClosureRector;
use Madbox99\RectorFilament\Rector\CollectTestCasePropertiesRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(AddTestCaseVarDocblockToPestClosureRector::class, [
        AddTestCaseVarDocblockToPestClosureRector::TEST_CASE_CLASS => 'Tests\\TestCase',
    ])
    ->withConfiguredRule(CollectTestCasePropertiesRector::class, [
        CollectTestCasePropertiesRector::TEST_CASE_CLASS => 'Tests\\TestCase',
    ]);
