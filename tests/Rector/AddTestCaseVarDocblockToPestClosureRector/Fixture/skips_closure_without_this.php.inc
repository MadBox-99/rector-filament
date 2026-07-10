<?php

it('does not touch closures without this', function (): void {
    expect(true)->toBeTrue();
});

beforeEach(function (): void {
    // nothing here uses the test case
});
