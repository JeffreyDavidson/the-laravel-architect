<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class OgImageCache
{
    private const RENDERER_VERSION = 'v1';

    public function __construct(private readonly OgImageGenerator $generator) {}

    public function generate(Post $post): string
    {
        $post->loadMissing('category');

        $disk = Storage::disk('local');
        $imagePath = $this->imagePath($post);
        $signaturePath = $this->signaturePath($post);
        $signature = $this->signature($post);

        $cachedImage = $this->currentImage($disk, $imagePath, $signaturePath, $signature);

        if ($cachedImage !== null) {
            return $cachedImage;
        }

        $png = $this->generator->generate($post);

        $disk->put($imagePath, $png);
        $disk->put($signaturePath, $signature);

        return $png;
    }

    public function forget(Post $post): void
    {
        Storage::disk('local')->deleteDirectory("og-images/{$post->getKey()}");
    }

    private function imagePath(Post $post): string
    {
        return "og-images/{$post->getKey()}/image.png";
    }

    private function signaturePath(Post $post): string
    {
        return "og-images/{$post->getKey()}/signature";
    }

    private function signature(Post $post): string
    {
        $category = $post->getRelation('category');

        return hash('sha256', json_encode([
            'version' => self::RENDERER_VERSION,
            'title' => $post->title,
            'category' => $category instanceof Category ? $category->name : null,
        ], JSON_THROW_ON_ERROR));
    }

    private function currentImage(
        FilesystemAdapter $disk,
        string $imagePath,
        string $signaturePath,
        string $signature,
    ): ?string {
        if (! $disk->exists($imagePath) || ! $disk->exists($signaturePath)) {
            return null;
        }

        $cachedSignature = $disk->get($signaturePath);

        if ($cachedSignature === null || ! hash_equals($signature, trim($cachedSignature))) {
            return null;
        }

        return $disk->get($imagePath);
    }
}
