<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\MediaHealthRecord;
use App\Enums\MediaHealthStatus;
use App\Enums\MediaHealthType;
use App\Enums\MediaSourceStatus;
use App\Enums\MediaVariantStatus;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Services\ImageUploadOptimizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageVariants;
use Throwable;

/**
 * Reads the stored image of every project, post and podcast and reports whether the
 * source is optimized and its responsive variants exist. Image metadata is cached for
 * five minutes per file version.
 */
final readonly class MediaHealthQuery
{
    public function __construct(private ResponsiveImageVariants $images) {}

    /**
     * Records matching every given filter, by content type and then id. The search matches
     * the title or the image's filename.
     *
     * @return list<MediaHealthRecord>
     */
    public function get(?MediaHealthType $type = null, ?string $search = null, ?MediaHealthStatus $status = null): array
    {
        $records = [];

        foreach (MediaHealthType::cases() as $sourceType) {
            if ($type instanceof MediaHealthType && $sourceType !== $type) {
                continue;
            }

            $source = $this->source($sourceType);

            $source['model']::query()
                ->select(['id', $source['title'], $source['path']])
                ->orderBy('id')
                ->lazyById(100)
                ->each(function (Model $model) use (&$records, $sourceType, $source, $search, $status): void {
                    $title = $model->getAttribute($source['title']);
                    $path = $model->getAttribute($source['path']);

                    if (filled($search) && ! Str::contains(
                        Str::lower((is_string($title) ? $title : '').' '.(is_string($path) ? basename($path) : '')),
                        Str::lower($search),
                    )) {
                        return;
                    }

                    $record = $this->inspect($model, $sourceType, $source);

                    if ($status instanceof MediaHealthStatus && $record->status !== $status) {
                        return;
                    }

                    $records[] = $record;
                });
        }

        return $records;
    }

    /** The stored image path of one record, or null when the record or its image is missing. */
    public function sourcePath(MediaHealthType $type, string $recordKey): ?string
    {
        $source = $this->source($type);
        $model = $source['model']::query()->find($recordKey);
        $path = $model?->getAttribute($source['path']);

        return is_string($path) && filled($path) ? $path : null;
    }

    /** @return array{model: class-string<Model>, title: string, path: string} */
    private function source(MediaHealthType $type): array
    {
        return match ($type) {
            MediaHealthType::Project => ['model' => Project::class, 'title' => 'title', 'path' => 'featured_image_path'],
            MediaHealthType::Post => ['model' => Post::class, 'title' => 'title', 'path' => 'featured_image_path'],
            MediaHealthType::Podcast => ['model' => Podcast::class, 'title' => 'name', 'path' => 'cover_image_path'],
        };
    }

    /** @param  array{model: class-string<Model>, title: string, path: string}  $source */
    private function inspect(Model $model, MediaHealthType $type, array $source): MediaHealthRecord
    {
        $path = $model->getAttribute($source['path']);
        $recordKey = $model->getKey();
        $recordKey = is_int($recordKey) || is_string($recordKey) ? (string) $recordKey : 'unknown';
        $title = $model->getAttribute($source['title']);
        $title = is_string($title) ? $title : '';

        if (! is_string($path) || blank($path)) {
            return $this->unavailable($type, $recordKey, $title, null, MediaSourceStatus::Missing);
        }

        $disk = Storage::disk('public');
        $filename = basename($path);

        if (! $disk->exists($path)) {
            return $this->unavailable($type, $recordKey, $title, $filename, MediaSourceStatus::Missing);
        }

        try {
            $size = $disk->size($path);
            $key = 'media-image-metadata.'.hash('sha256', $disk->path($path).':'.$disk->lastModified($path).':'.$size);
            $metadata = Cache::remember($key, now()->addMinutes(5), function () use ($path): array {
                $image = Image::fromStorage($path, 'public');

                return ['width' => $image->width(), 'height' => $image->height(), 'mime' => $image->mimeType()];
            });
            $sourceStatus = $metadata['mime'] === 'image/webp'
                && $metadata['width'] <= ImageUploadOptimizer::MAX_DIMENSION
                && $metadata['height'] <= ImageUploadOptimizer::MAX_DIMENSION
                ? MediaSourceStatus::Optimized
                : MediaSourceStatus::NeedsOptimization;
        } catch (Throwable) {
            return $this->unavailable($type, $recordKey, $title, $filename, MediaSourceStatus::Unreadable);
        }

        $variantsReady = $this->images->hasRequiredVariants($path, $metadata['width']);

        return new MediaHealthRecord(
            type: $type,
            recordKey: $recordKey,
            title: $title,
            filename: $filename,
            width: $metadata['width'],
            height: $metadata['height'],
            fileSize: $size,
            sourceStatus: $sourceStatus,
            variantStatus: $variantsReady ? MediaVariantStatus::Ready : MediaVariantStatus::Missing,
            status: match (true) {
                $sourceStatus === MediaSourceStatus::NeedsOptimization => MediaHealthStatus::ReuploadRequired,
                $variantsReady => MediaHealthStatus::Healthy,
                default => MediaHealthStatus::NeedsRepair,
            },
            repairable: ! $variantsReady,
        );
    }

    /** A record whose image is missing or unreadable, so only a re-upload can fix it. */
    private function unavailable(MediaHealthType $type, string $recordKey, string $title, ?string $filename, MediaSourceStatus $sourceStatus): MediaHealthRecord
    {
        return new MediaHealthRecord(
            type: $type,
            recordKey: $recordKey,
            title: $title,
            filename: $filename,
            width: null,
            height: null,
            fileSize: null,
            sourceStatus: $sourceStatus,
            variantStatus: MediaVariantStatus::Unavailable,
            status: MediaHealthStatus::ReuploadRequired,
            repairable: false,
        );
    }
}
