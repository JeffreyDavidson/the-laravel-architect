<?php

it('forces PHPUnit to use an isolated testing database', function (): void {
    $configuration = file_get_contents(base_path('phpunit.xml'));

    expect($configuration)->toBeString();

    foreach ([
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'DB_URL' => '',
    ] as $name => $value) {
        $pattern = sprintf(
            '/<env name="%s" value="%s" force="true"\/>/',
            preg_quote($name, '/'),
            preg_quote($value, '/'),
        );

        expect(preg_match($pattern, $configuration))->toBe(1);
    }
});
