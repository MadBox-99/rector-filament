<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source;

use Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source\User;

/**
 * The assignments below mirror what a Pest `beforeEach` closure would contain.
 * They are never executed; the rule only parses this file to infer property types.
 */
function scenario(): void
{
    $this->user = User::factory()->create();
    $this->comments = Comment::factory()->count(3)->create();
    $this->service = new Service();
    $this->label = 'hello';
    $this->attempts = 5;
    $this->existing = User::factory()->create();
}
