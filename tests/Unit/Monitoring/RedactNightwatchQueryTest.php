<?php

use App\Monitoring\RedactNightwatchQuery;
use Laravel\Nightwatch\QueryConnectionType;
use Laravel\Nightwatch\Records\Query;

it('removes literal values and comments from query telemetry', function () {
    $query = nightwatchQuery(<<<'SQL'
        select * from `subscribers`
        where `email` = 'private@example.test'
        and `attempts` > 3
        /* token=private-token */
        -- private note
        limit 1
        SQL);

    $redacted = app(RedactNightwatchQuery::class)($query);

    expect($redacted)->toBeTrue()
        ->and($query->sql)->toContain(
            '`email` = ?',
            '`attempts` > ?',
            '/* redacted */',
            '-- redacted',
            'limit ?',
        )
        ->not->toContain('private@example.test', 'private-token', 'private note');
});

it('preserves positional placeholders and identifiers containing numbers', function () {
    $query = nightwatchQuery('select * from `oauth2_tokens` where `id` = ? or `legacy_id` = $1');

    app(RedactNightwatchQuery::class)($query);

    expect($query->sql)->toBe('select * from `oauth2_tokens` where `id` = ? or `legacy_id` = $1');
});

function nightwatchQuery(string $sql): Query
{
    return new Query(
        sql: $sql,
        file: '/app/Queries/SubscriberQuery.php',
        line: 10,
        duration: 1,
        connection: 'mysql',
        connectionType: QueryConnectionType::Read,
    );
}
