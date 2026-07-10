<?php

describe('group', function (): void {
    it('has export action on list page', function (): void {
        $this->assertTrue(true);
    });
});

?>
-----
<?php

describe('group', function (): void {
    it('has export action on list page', function (): void {
        /** @var \Tests\TestCase $this */
        $this->assertTrue(true);
    });
});

?>
