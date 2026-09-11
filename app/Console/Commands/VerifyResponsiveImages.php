<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Services\ResponsiveImageVariants;
use App\Services\ResponsiveImageWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:verify-responsive-images')]
#[Description('Verify responsive image variants for stored project, post, and podcast media')]
class VerifyResponsiveImages extends Command
{
    public function handle(ResponsiveImageVariants $images, ResponsiveImageWorkflow $workflow): int
    {
        $project = $workflow->verify(Project::class, 'featured_image_path', $images);
        $post = $workflow->verify(Post::class, 'featured_image_path', $images);
        $podcast = $workflow->verify(Podcast::class, 'cover_image_path', $images);

        $results = [
            'Projects' => [$project['checked'], $project['failed']],
            'Posts' => [$post['checked'], $post['failed']],
            'Podcasts' => [$podcast['checked'], $podcast['failed']],
        ];

        foreach ($results as $label => [$checked, $failed]) {
            $verified = $checked - $failed;

            $this->line("{$label}: {$checked} checked, {$verified} verified, {$failed} failed.");
        }

        $failures = $project['failed'] + $post['failed'] + $podcast['failed'];

        if ($failures > 0) {
            $this->error('Responsive image verification failed.');

            return self::FAILURE;
        }

        $this->info('Responsive image verification passed.');

        return self::SUCCESS;
    }
}
