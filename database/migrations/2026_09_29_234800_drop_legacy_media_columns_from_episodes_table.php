<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Episodes are played through Transistor (and YouTube), so the retired hosted audio,
     * uploaded audio and Spotify/Apple embed columns are dropped. This cannot be undone,
     * so it refuses to run while any episode, trashed or not, still holds a value in one
     * of them. Safe to run again once the columns are gone.
     */
    public function up(): void
    {
        $columns = array_values(array_filter(
            ['audio_url', 'audio_path', 'embed_url'],
            fn (string $column): bool => Schema::hasColumn('episodes', $column),
        ));

        if ($columns === []) {
            return;
        }

        $holdingLegacyMedia = DB::table('episodes')
            ->where(function ($query) use ($columns): void {
                foreach ($columns as $column) {
                    $query->orWhere(fn ($query) => $query->whereNotNull($column)->where($column, '!=', ''));
                }
            })
            ->count();

        if ($holdingLegacyMedia > 0) {
            throw new RuntimeException("{$holdingLegacyMedia} episode(s) still hold legacy media (hosted audio, uploaded audio or an embed). Move them to Transistor or clear those values, then run the migration again.");
        }

        Schema::table('episodes', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
