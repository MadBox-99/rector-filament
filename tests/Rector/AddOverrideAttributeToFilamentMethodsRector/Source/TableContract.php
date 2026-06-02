<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Tests\Rector\AddOverrideAttributeToFilamentMethodsRector\Source;

interface TableContract
{
    public function table(object $table): object;
}
