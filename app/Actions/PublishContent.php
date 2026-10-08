<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\ContentNotReadyToPublish;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Publishing\ContentReadiness;
use Illuminate\Support\Str;

final class PublishContent
{
    /**
     * Publish content whose required details are present, keeping an existing publish
     * date (a future date schedules it) and otherwise using now. Readiness is checked
     * against the record as saved.
     *
     * @throws ContentNotReadyToPublish When a required-to-publish detail is missing.
     */
    public function handle(Post|Project|Episode|NewsletterIssue $content): void
    {
        $issues = new ContentReadiness($content)->publishingIssues();

        if ($issues !== []) {
            throw new ContentNotReadyToPublish(Str::headline(class_basename($content)), $issues);
        }

        $content->publish();
    }
}
