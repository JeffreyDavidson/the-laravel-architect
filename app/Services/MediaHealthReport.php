<?php

namespace App\Services;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

class MediaHealthReport
{
    /** @var array<string, array{model: class-string<Model>, title: string, path: string, label: string}> */
    private const array SOURCES = [
        'project' => ['model' => Project::class, 'title' => 'title', 'path' => 'featured_image_path', 'label' => 'Project'],
        'post' => ['model' => Post::class, 'title' => 'title', 'path' => 'featured_image_path', 'label' => 'Post'],
        'podcast' => ['model' => Podcast::class, 'title' => 'name', 'path' => 'cover_image_path', 'label' => 'Podcast'],
    ];

    public function __construct(private readonly ResponsiveImageVariants $images) {}

    /** @return array<string, array{type: string, type_key: string, record_key: string, title: string, filename: string, dimensions: string, file_size: string, source_status: string, variants: string, status: string, status_color: string, repairable: bool}> */
    public function records(?string $typeFilter = null, ?string $search = null): array
    {
        $records = [];

        foreach (self::SOURCES as $type => $source) {
            if ($typeFilter !== null && $type !== $typeFilter) {
                continue;
            }

            $source['model']::query()
                ->select(['id', $source['title'], $source['path']])
                ->orderBy('id')
                ->lazyById(100)
                ->each(function (Model $model) use (&$records, $type, $source, $search): void {
                    $title = $model->getAttribute($source['title']);
                    $path = $model->getAttribute($source['path']);
                    if (filled($search) && ! Str::contains(
                        Str::lower((is_string($title) ? $title : '').' '.(is_string($path) ? basename($path) : '')),
                        Str::lower($search),
                    )) {
                        return;
                    }

                    $record = $this->inspect($model, $type, $source);
                    $records[$record['type_key'].':'.$record['record_key']] = $record;
                });
        }

        return $records;
    }

    public function repair(string $type, string $recordKey): bool
    {
        $source = self::SOURCES[$type] ?? null;

        if ($source === null) {
            return false;
        }

        $model = $source['model']::query()->find($recordKey);
        $path = $model?->getAttribute($source['path']);

        return is_string($path) && filled($path) && $this->images->generate($path);
    }

    /**
     * @param  array{model: class-string<Model>, title: string, path: string, label: string}  $source
     * @return array{type: string, type_key: string, record_key: string, title: string, filename: string, dimensions: string, file_size: string, source_status: string, variants: string, status: string, status_color: string, repairable: bool}
     */
    private function inspect(Model $model, string $type, array $source): array
    {
        $path = $model->getAttribute($source['path']);
        $recordKey = $model->getKey();
        $recordKey = is_int($recordKey) || is_string($recordKey) ? (string) $recordKey : 'unknown';
        $base = [
            'type' => $source['label'],
            'type_key' => $type,
            'record_key' => $recordKey,
            'title' => is_string($title = $model->getAttribute($source['title'])) ? $title : '',
            'filename' => '—',
            'dimensions' => '—',
            'file_size' => '—',
            'source_status' => 'Missing',
            'variants' => 'Unavailable',
            'status' => 'Re-upload required',
            'status_color' => 'danger',
            'repairable' => false,
        ];

        if (! is_string($path) || blank($path)) {
            return $base;
        }

        $disk = Storage::disk('public');
        $base['filename'] = basename($path);

        if (! $disk->exists($path)) {
            return $base;
        }

        try {
            $size = $disk->size($path);
            $key = 'media-image-metadata.'.hash('sha256', $disk->path($path).':'.$disk->lastModified($path).':'.$size);
            $metadata = Cache::remember($key, now()->addMinutes(5), function () use ($path): array {
                $image = Image::fromStorage($path, 'public');

                return ['width' => $image->width(), 'height' => $image->height(), 'mime' => $image->mimeType()];
            });
            $base['dimensions'] = "{$metadata['width']} × {$metadata['height']}";
            $base['file_size'] = Number::fileSize($size);
            $base['source_status'] = $metadata['mime'] === 'image/webp'
                && $metadata['width'] <= ImageUploadOptimizer::MAX_DIMENSION
                && $metadata['height'] <= ImageUploadOptimizer::MAX_DIMENSION
                ? 'Optimized'
                : 'Needs optimization';
        } catch (Throwable) {
            $base['source_status'] = 'Unreadable';

            return $base;
        }

        $variantsReady = $this->images->hasRequiredVariants($path, $metadata['width']);
        $base['variants'] = $variantsReady ? 'Ready' : 'Missing';
        $base['repairable'] = ! $variantsReady;

        if ($base['source_status'] === 'Optimized' && $variantsReady) {
            $base['status'] = 'Healthy';
            $base['status_color'] = 'success';
        } elseif ($base['source_status'] === 'Needs optimization') {
            $base['status'] = 'Re-upload required';
        } else {
            $base['status'] = 'Needs repair';
            $base['status_color'] = 'warning';
        }

        return $base;
    }
}
