<?php

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;

#[Fillable('name', 'slug', 'description')]
#[Sluggable(from: 'name')]
class Category extends Model
{
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
