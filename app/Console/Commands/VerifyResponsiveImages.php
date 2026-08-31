<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Services\ResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:verify-responsive-images')]
#[Description('Verify responsive image variants for stored project, post, and podcast media')]
class VerifyResponsiveImages extends Command
{
    public function handle(ResponsiveImageVariants $images): int
    {
        $projectChecks = 0;
        $projectFailures = 0;

        Project::query()
            ->whereNotNull('featured_image_path')
            ->select(['id', 'featured_image_path'])
            ->eachById(function (Project $project) use ($images, &$projectChecks, &$projectFailures): void {
                $projectChecks++;

                if (! is_string($project->featured_image_path)
                    || ! $images->hasRequiredVariants($project->featured_image_path)) {
                    $projectFailures++;
                }
            });

        $postChecks = 0;
        $postFailures = 0;

        Post::query()
            ->whereNotNull('featured_image_path')
            ->select(['id', 'featured_image_path'])
            ->eachById(function (Post $post) use ($images, &$postChecks, &$postFailures): void {
                $postChecks++;

                if (! is_string($post->featured_image_path)
                    || ! $images->hasRequiredVariants($post->featured_image_path)) {
                    $postFailures++;
                }
            });

        $podcastChecks = 0;
        $podcastFailures = 0;

        Podcast::query()
            ->whereNotNull('cover_image_path')
            ->select(['id', 'cover_image_path'])
            ->eachById(function (Podcast $podcast) use ($images, &$podcastChecks, &$podcastFailures): void {
                $podcastChecks++;

                if (! is_string($podcast->cover_image_path)
                    || ! $images->hasRequiredVariants($podcast->cover_image_path)) {
                    $podcastFailures++;
                }
            });

        $results = [
            'Projects' => [$projectChecks, $projectFailures],
            'Posts' => [$postChecks, $postFailures],
            'Podcasts' => [$podcastChecks, $podcastFailures],
        ];

        foreach ($results as $label => [$checked, $failed]) {
            $verified = $checked - $failed;

            $this->line("{$label}: {$checked} checked, {$verified} verified, {$failed} failed.");
        }

        $failures = $projectFailures + $postFailures + $podcastFailures;

        if ($failures > 0) {
            $this->error('Responsive image verification failed.');

            return self::FAILURE;
        }

        $this->info('Responsive image verification passed.');

        return self::SUCCESS;
    }
}
