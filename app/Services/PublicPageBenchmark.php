<?php

namespace App\Services;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PublicPageBenchmark
{
    private const array PAGES = [
        '/blog',
        '/podcasts/scale-test-podcast',
        '/projects',
    ];

    private bool $measuring = false;

    private int $queryCount = 0;

    private float $queryMilliseconds = 0.0;

    public function __construct()
    {
        DB::listen(function (QueryExecuted $query): void {
            if (! $this->measuring) {
                return;
            }

            $this->queryCount++;
            $queryTime = $query->time;
            $this->queryMilliseconds += $queryTime;
        });
    }

    /**
     * @return list<array{
     *     path: string,
     *     first: array{milliseconds: float, queries: int, query_milliseconds: float, memory_bytes: int},
     *     steady: array{milliseconds: float, queries: float, query_milliseconds: float, memory_bytes: float},
     * }>
     */
    public function measure(int $iterations): array
    {
        if ($iterations < 2 || $iterations > 25) {
            throw new RuntimeException('Iterations must be between 2 and 25.');
        }

        $configuredUrl = config('app.url');
        if (! is_string($configuredUrl) || trim($configuredUrl) === '') {
            throw new RuntimeException('APP_URL must be configured before benchmarking.');
        }
        $baseUrl = rtrim($configuredUrl, '/');

        $kernel = app(Kernel::class);
        $results = [];

        foreach (self::PAGES as $path) {
            $samples = [];

            for ($iteration = 0; $iteration < $iterations; $iteration++) {
                $this->queryCount = 0;
                $this->queryMilliseconds = 0.0;
                $memoryBefore = memory_get_usage(true);
                memory_reset_peak_usage();
                $request = Request::create($baseUrl.$path);
                $this->measuring = true;
                $startedAt = hrtime(true);

                try {
                    $response = $kernel->handle($request);
                    $kernel->terminate($request, $response);
                } finally {
                    $this->measuring = false;
                }
                $duration = (hrtime(true) - $startedAt) / 1_000_000;

                if (! $response->isSuccessful()) {
                    throw new RuntimeException("Benchmark request for {$path} returned HTTP {$response->getStatusCode()}.");
                }

                $samples[] = [
                    'milliseconds' => $duration,
                    'queries' => $this->queryCount,
                    'query_milliseconds' => $this->queryMilliseconds,
                    'memory_bytes' => max(0, memory_get_peak_usage(true) - $memoryBefore),
                ];
            }

            $steadySamples = array_slice($samples, 1);
            $results[] = [
                'path' => $path,
                'first' => $samples[0],
                'steady' => [
                    'milliseconds' => $this->average($steadySamples, 'milliseconds'),
                    'queries' => $this->average($steadySamples, 'queries'),
                    'query_milliseconds' => $this->average($steadySamples, 'query_milliseconds'),
                    'memory_bytes' => $this->average($steadySamples, 'memory_bytes'),
                ],
            ];
        }

        return $results;
    }

    /**
     * @param  list<array{milliseconds: float, queries: int, query_milliseconds: float, memory_bytes: int}>  $samples
     */
    private function average(array $samples, string $metric): float
    {
        return array_sum(array_column($samples, $metric)) / count($samples);
    }
}
