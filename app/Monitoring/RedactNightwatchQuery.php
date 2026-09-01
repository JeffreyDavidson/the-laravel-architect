<?php

namespace App\Monitoring;

use Laravel\Nightwatch\Records\Query;

final class RedactNightwatchQuery
{
    public function __invoke(Query $query): bool
    {
        $query->sql = preg_replace(
            [
                '/\/\*.*?\*\//s',
                '/--[^\r\n]*/',
                "/'(?:''|\\\\.|[^'\\\\])*'/s",
                '/(?<![$\w?])[-+]?(?:\d+(?:\.\d+)?|\.\d+)(?![\w?])/',
            ],
            ['/* redacted */', '-- redacted', '?', '?'],
            $query->sql,
        ) ?? '[redacted]';

        return true;
    }
}
