<?php

namespace App\Services;

use App\Support\Content\Archives\ProductionContentSource;
use App\Support\Content\Archives\PublicContentArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class ProductionContentSynchronizer
{
    public function __construct(
        private PublicContentArchive $archive,
        private ProductionContentSource $source,
        private ResponsiveImageVariants $images,
    ) {}

    /**
     * @return array{counts: array<string, int>, mediaCount: int}
     */
    public function synchronize(): array
    {
        $directory = storage_path('framework/content-sync/'.Str::uuid());
        $archivePath = "{$directory}/content.json";
        File::ensureDirectoryExists($directory);

        try {
            $this->source->exportTo($archivePath);
            $contents = $this->archive->decode(File::get($archivePath));
            $mediaPaths = $this->archive->mediaPaths($contents);

            $this->source->copyMedia($mediaPaths);

            foreach ($this->archive->imagePaths($contents) as $path) {
                if (! $this->images->generate($path)) {
                    throw new RuntimeException('Responsive image regeneration failed during content synchronization.');
                }
            }

            return [
                'counts' => $this->archive->sync($contents),
                'mediaCount' => count($mediaPaths),
            ];
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
