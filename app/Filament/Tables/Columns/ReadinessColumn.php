<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Enums\ContentReadinessStatus;
use App\Enums\ReadinessCheck;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Publishing\ContentReadiness;
use Filament\Tables\Columns\TextColumn;
use WeakMap;

/**
 * Readiness badge for content tables, with the missing public details as its description.
 * The readiness check runs once per row and is shared by the badge and the description.
 */
final class ReadinessColumn extends TextColumn
{
    protected bool $showsProgress = false;

    /** @var WeakMap<Post|Project|Podcast|Episode|NewsletterIssue|Video, array{status: ContentReadinessStatus, description: string}> */
    private WeakMap $resolvedReadiness;

    public static function getDefaultName(): string
    {
        return 'readiness';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolvedReadiness = new WeakMap;

        $this
            ->label('Readiness')
            ->state(fn (Post|Project|Podcast|Episode|NewsletterIssue|Video $record): ContentReadinessStatus => $this->readinessFor($record)['status'])
            ->description(fn (Post|Project|Podcast|Episode|NewsletterIssue|Video $record): string => $this->readinessFor($record)['description'])
            ->badge();
    }

    /** Prefix the description with how many checks are complete, such as "4/6 complete". */
    public function withProgress(): static
    {
        $this->showsProgress = true;

        return $this;
    }

    /**
     * @return array{status: ContentReadinessStatus, description: string}
     */
    private function readinessFor(Post|Project|Podcast|Episode|NewsletterIssue|Video $record): array
    {
        if (isset($this->resolvedReadiness[$record])) {
            return $this->resolvedReadiness[$record];
        }

        $readiness = new ContentReadiness($record);
        $missingSummary = $this->missingSummary($readiness->missing());

        return $this->resolvedReadiness[$record] = [
            'status' => $readiness->status(),
            'description' => $this->showsProgress
                ? "{$this->progress($readiness)} · {$missingSummary}"
                : $missingSummary,
        ];
    }

    /** How many checks are complete, such as "4/6 complete". */
    private function progress(ContentReadiness $readiness): string
    {
        $total = count($readiness->checks());
        $complete = $total - count($readiness->missing());

        return "{$complete}/{$total} complete";
    }

    /** @param list<ReadinessCheck> $missing */
    private function missingSummary(array $missing): string
    {
        if ($missing === []) {
            return 'All public details are complete.';
        }

        return 'Missing: '.implode(', ', array_map(fn (ReadinessCheck $check): string => $check->getLabel(), $missing));
    }
}
