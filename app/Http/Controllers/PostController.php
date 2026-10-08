<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BlogIndexRequest;
use App\Models\Post;
use App\ViewModels\PostIndexViewModel;
use App\ViewModels\PostShowViewModel;
use Illuminate\Contracts\View\View;

final class PostController
{
    public function index(BlogIndexRequest $request, PostIndexViewModel $blogIndexViewModel): View
    {
        $filters = $request->validated();

        $data = $blogIndexViewModel->data(
            is_string($filters['q'] ?? null) ? $filters['q'] : '',
            is_string($filters['category'] ?? null) ? $filters['category'] : null,
        );

        return view('pages.blog.index', [...$data, 'initialData' => $data]);
    }

    public function show(Post $post, PostShowViewModel $postShowViewModel): View
    {
        abort_unless($post->isPublished(), 404);

        return view('pages.blog.show', $postShowViewModel->data($post));
    }
}
