<?php

namespace Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source\User;

abstract class SampleTestCase
{
    public User $existing;
}

?>
-----
<?php

namespace Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Fixture;

use Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source\User;

abstract class SampleTestCase
{
    public User $existing;
    public \Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source\User $user;
    public \Illuminate\Database\Eloquent\Collection $comments;
    public \Madbox99\RectorFilament\Tests\Rector\CollectTestCasePropertiesRector\Source\Service $service;
    public string $label;
    public int $attempts;
}

?>
