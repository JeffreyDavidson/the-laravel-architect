<?php

namespace App\Console\Commands;

use App\Services\PublicPageBenchmark;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('content:benchmark {--iterations=5 : Requests per page, from 2 to 25}')]
#[Description('Measure local response time, database queries, SQL time, and memory for representative public pages')]
class BenchmarkPublicPages extends Command
{
    public function handle(PublicPageBenchmark $benchmark): int
    {
        if (! app()->environment('local')) {
            $this->error('Public page benchmarking is limited to the local environment.');

            return self::FAILURE;
        }

        $iterations = filter_var($this->option('iterations'), FILTER_VALIDATE_INT);
        if (! is_int($iterations) || $iterations < 2 || $iterations > 25) {
            $this->error('The iteration count must be an integer between 2 and 25.');

            return self::INVALID;
        }

        try {
            $results = $benchmark->measure($iterations);
        } catch (\RuntimeException $exception) {
            $message = $exception->getMessage();
            $this->error($message);

            return self::FAILURE;
        }

        $rows = array_map(fn (array $result): array => [
            $result['path'],
            number_format($result['first']['milliseconds'], 2),
            number_format($result['steady']['milliseconds'], 2),
            $result['first']['queries'],
            number_format($result['steady']['queries'], 1),
            number_format($result['steady']['query_milliseconds'], 2),
            number_format($result['steady']['memory_bytes'] / 1_048_576, 2),
        ], $results);

        $this->table(
            ['Page', 'First ms', 'Steady avg ms', 'First queries', 'Steady avg queries', 'Steady SQL ms', 'Steady memory MiB'],
            $rows,
        );
        $this->line('App-kernel measurements; steady averages exclude the first request.');

        return self::SUCCESS;
    }
}
