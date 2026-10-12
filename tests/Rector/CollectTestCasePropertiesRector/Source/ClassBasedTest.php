<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source;

/**
 * A class-based test that declares its own state: its properties must not be lifted
 * onto the base test case, or PHP rejects the narrower visibility in this subclass.
 */
final class ClassBasedTest
{
    private User $owner;

    public function setUp(): void
    {
        $this->owner = User::factory()->create();
    }
}
