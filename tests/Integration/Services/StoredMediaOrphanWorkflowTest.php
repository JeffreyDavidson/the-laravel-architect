<?php

use App\Enums\PublishStatus;
use App\Models\Project;
use App\Services\StoredMediaOrphanWorkflow;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

/** Store old enough files under projects/ that the delete phase may remove them. */
function storeDeletableOrphans(int $count): void
{
    foreach (range(1, $count) as $number) {
        $path = "projects/orphan-{$number}.png";
        Storage::disk('public')->put($path, 'orphan');
        touch(Storage::disk('public')->path($path), now()->subDays(2)
            ->getTimestamp());
    }
}

it('scans references a fixed number of times however many orphans it deletes', function () {
    $workflow = app(StoredMediaOrphanWorkflow::class);
    $queriesToDelete = function (int $orphans) use ($workflow): int {
        storeDeletableOrphans($orphans);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $report = $workflow->audit(delete: true);

        DB::disableQueryLog();

        expect($report['deleted'])->toBe($orphans);

        return count(DB::getQueryLog());
    };

    expect($queriesToDelete(5))
        ->toBe($queriesToDelete(1));
});

it('keeps a file that became referenced after the scan and before the delete phase', function () {
    storeDeletableOrphans(1);
    $referenced = false;

    // The content scan reads SEO images last, so a reference saved right after that query
    // lands between the scan and the delete phase, as an editor saving content would.
    DB::listen(function (QueryExecuted $query) use (&$referenced): void {
        if ($referenced || ! str_contains($query->sql, 'from "seo"')) {
            return;
        }

        $referenced = true;
        Project::withoutEvents(fn () => Project::query()->create([
            'title' => 'Project',
            'slug' => 'project',
            'description' => 'Description',
            'status' => PublishStatus::Draft,
            'featured_image_path' => 'projects/orphan-1.png',
        ]));
    });

    $report = app(StoredMediaOrphanWorkflow::class)->audit(delete: true);

    expect($report)
        ->toMatchArray(['orphaned' => 1, 'deleted' => 0, 'skipped' => 1]);
    Storage::disk('public')->assertExists('projects/orphan-1.png');
});
