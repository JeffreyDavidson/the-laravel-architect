<?php

use Symfony\Component\Process\Process;

/** Run the chaining check against one PHP snippet and return the process. */
function checkChaining(string $code): Process
{
    $file = tempnam(sys_get_temp_dir(), 'tla-chaining-').'.php';
    file_put_contents($file, "<?php\n\n{$code}\n");

    $process = new Process([PHP_BINARY, dirname(__DIR__, 3).'/scripts/check-method-chaining.php', $file]);
    $process->run();
    unlink($file);

    return $process;
}

it('flags a method chain continued on the same line', function (string $code) {
    $process = checkChaining($code);

    expect($process->getExitCode())
        ->toBe(1)
        ->and($process->getErrorOutput())
        ->toContain('chains more than one call on one line');
})->with([
    'query chain' => '$query->where("a", 1)->first();',
    'static start' => 'Post::query()->published()->get();',
    'helper start' => 'fake()->unique()->safeEmail();',
    'this call' => '$this->issue()->wasSent();',
    'property after a call' => '$user->refresh()->email;',
    'nullsafe after a call' => '$user->profile()?->name;',
    'nested argument chain' => '$a->b($c->d())->e();',
]);

it('allows one call per line and separate object accesses', function (string $code) {
    $process = checkChaining($code);

    expect($process->getExitCode())
        ->toBe(0);
})->with([
    'single call from a static start' => 'Post::query()->first();',
    'access inside an argument' => '$this->save($model->id);',
    'enum value inside an argument' => '$column->default(PublishStatus::Draft->value);',
    'property chain' => '$post->category->name;',
    'expectation modifier' => 'expect($a)->not->toBeNull();',
    'separate expressions' => '$a->b() && $c->d();',
    'interpolated string' => '$label = "{$user->profile()->name}";',
    'multiline chain' => "\$query->where('a', 1)\n    ->first();",
]);

it('does not check merged migrations', function (string $path) {
    $process = new Process(
        [PHP_BINARY, 'scripts/check-method-chaining.php', $path],
        dirname(__DIR__, 3),
    );
    $process->run();

    expect($process->getErrorOutput())
        ->not
        ->toContain('database/migrations/');
})->with([
    'migrations directory' => 'database/migrations',
    'repository root' => '.',
]);
