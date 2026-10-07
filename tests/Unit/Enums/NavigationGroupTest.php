<?php

use App\Enums\NavigationGroup;

it('labels the admin navigation groups in sidebar order', function () {
    $labels = array_map(
        fn (NavigationGroup $group): string => $group->getLabel(),
        NavigationGroup::cases(),
    );

    expect($labels)->toBe(['Publish', 'Library', 'Audience', 'Operations']);
});
