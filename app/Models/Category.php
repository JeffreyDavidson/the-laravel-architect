<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;

#[Fillable('name', 'slug', 'description')]
#[Sluggable(from: 'name')]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use TracksActivity;

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return HasMany<Post, $this> */
    public function publishedPosts(): HasMany
    {
        return $this->posts()
            ->published();
    }
}
