<?php

namespace App\Monitoring;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;

final class ResolveNightwatchUser
{
    /** @return array{id: string} */
    public function __invoke(Authenticatable $user): array
    {
        $appKey = Config::get('app.key');
        $identifier = $user->getAuthIdentifier();

        if (! is_string($appKey) || $appKey === '' || (! is_int($identifier) && ! is_string($identifier))) {
            return ['id' => '[redacted]'];
        }

        return [
            'id' => hash_hmac('sha256', (string) $identifier, $appKey),
        ];
    }
}
