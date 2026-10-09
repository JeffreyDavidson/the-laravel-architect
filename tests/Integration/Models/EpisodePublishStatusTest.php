<?php

use App\Models\Episode;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

pest()->use(RefreshDatabase::class);

it('persists every supported episode publish status', function () {
    foreach (PublishStatus::cases() as $status) {
        $episode = Episode::factory()->create(['status' => $status]);

        expect($episode->refresh()
            ->status)->toBe($status);
    }
});

it('rejects inserting an episode publish status that the application does not support', function () {
    DB::table('episodes')->insert([
        'title' => 'Unknown status episode',
        'slug' => 'unknown-status-episode',
        'description' => 'Episode description.',
        'status' => 'unknown',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('rejects changing an episode to a publish status that the application does not support', function () {
    $id = DB::table('episodes')->insertGetId([
        'title' => 'Draft episode',
        'slug' => 'draft-episode',
        'description' => 'Episode description.',
        'status' => PublishStatus::Draft->value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('episodes')
        ->where('id', $id)
        ->update(['status' => 'unknown']);
})->throws(QueryException::class);
