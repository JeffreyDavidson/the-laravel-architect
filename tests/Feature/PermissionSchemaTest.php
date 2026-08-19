<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('does not retain obsolete permission tables', function () {
    expect(Schema::hasTable('permissions'))->toBeFalse()
        ->and(Schema::hasTable('roles'))->toBeFalse()
        ->and(Schema::hasTable('model_has_permissions'))->toBeFalse()
        ->and(Schema::hasTable('model_has_roles'))->toBeFalse()
        ->and(Schema::hasTable('role_has_permissions'))->toBeFalse();
});
