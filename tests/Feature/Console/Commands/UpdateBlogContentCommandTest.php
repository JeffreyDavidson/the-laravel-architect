<?php

use App\Console\Commands\UpdateBlogContent;
use Symfony\Component\Console\Tester\CommandTester;

it('delegates to the blog seeder and reports success', function () {
    $command = new class(UpdateBlogContent::SUCCESS) extends UpdateBlogContent
    {
        /** @var list<array{string, array<string, mixed>}> */
        public array $calls = [];

        public function __construct(private readonly int $exitCode)
        {
            parent::__construct();
        }

        public function call($command, array $arguments = [])
        {
            $this->calls[] = [$command, $arguments];

            return $this->exitCode;
        }
    };
    $command->setLaravel(app());

    $tester = new CommandTester($command);
    $exitCode = $tester->execute([]);

    expect($exitCode)->toBe(UpdateBlogContent::SUCCESS)
        ->and($command->calls)->toBe([[
            'db:seed',
            [
                '--class' => 'Database\\Seeders\\BlogSeeder',
                '--force' => true,
            ],
        ]])
        ->and($tester->getDisplay())->toContain('Refreshing blog post content from seeder...')
        ->and($tester->getDisplay())->toContain('Done! Blog posts updated.');
});

it('propagates seeder failure without reporting success', function () {
    $command = new class(UpdateBlogContent::FAILURE) extends UpdateBlogContent
    {
        public function __construct(private readonly int $exitCode)
        {
            parent::__construct();
        }

        public function call($command, array $arguments = [])
        {
            return $this->exitCode;
        }
    };
    $command->setLaravel(app());

    $tester = new CommandTester($command);
    $exitCode = $tester->execute([]);

    expect($exitCode)->toBe(UpdateBlogContent::FAILURE)
        ->and($tester->getDisplay())->toContain('Blog post refresh failed.')
        ->and($tester->getDisplay())->not->toContain('Done! Blog posts updated.');
});
