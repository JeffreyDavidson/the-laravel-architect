<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The database cache store only removes an expired entry when its key is read again, and
 * Laravel has no prune command for it, so per-IP rate limiter rows would otherwise stay
 * forever. Locks are left to the store's own lottery.
 */
#[Signature('cache:prune-expired')]
#[Description('Delete expired entries from the database cache table')]
final class PruneExpiredCacheEntries extends Command
{
    public function handle(): int
    {
        $connection = config('cache.stores.database.connection');

        $deleted = DB::connection(is_string($connection) ? $connection : null)
            ->table(config()->string('cache.stores.database.table'))
            ->where('expiration', '<=', now()->getTimestamp())
            ->delete();

        $this->info("Deleted {$deleted} expired cache entries.");

        return self::SUCCESS;
    }
}
