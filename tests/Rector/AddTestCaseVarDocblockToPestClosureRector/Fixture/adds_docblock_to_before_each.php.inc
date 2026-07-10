<?php

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = 'user';
});

?>
-----
<?php

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    /** @var \Tests\TestCase $this */
    $this->user = 'user';
});

?>
