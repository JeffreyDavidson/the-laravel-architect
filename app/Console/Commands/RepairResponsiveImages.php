<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('media:repair-responsive-images {--force : Regenerate variants that already pass verification}')]
#[Description('Repair responsive image variants for stored project, post, and podcast media')]
class RepairResponsiveImages extends Command implements Isolatable
{
    protected $isolated = true;

    protected $isolatedExitCode = self::FAILURE;

    public function handle(): int
    {
        $arguments = $this->option('force') ? ['--force' => true] : [];
        $status = self::SUCCESS;

        foreach ([
            'projects:generate-image-variants',
            'posts:generate-image-variants',
            'podcasts:generate-image-variants',
        ] as $command) {
            if ($this->call($command, $arguments) !== self::SUCCESS) {
                $status = self::FAILURE;
            }
        }

        if ($this->call('media:verify-responsive-images') !== self::SUCCESS) {
            $status = self::FAILURE;
        }

        if ($status === self::FAILURE) {
            $this->error('Responsive image repair completed with failures.');

            return self::FAILURE;
        }

        $this->info('Responsive image repair completed successfully.');

        return self::SUCCESS;
    }
}
