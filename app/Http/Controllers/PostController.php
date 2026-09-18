<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogIndexRequest;
use App\Models\Post;
use App\Queries\BlogIndexQuery;
use App\ViewModels\BlogIndexViewModel;
use App\ViewModels\PostShowViewModel;
use Illuminate\Contracts\View\View;

class PostController
{
    public function index(
        BlogIndexRequest $request,
        BlogIndexQuery $blogIndexQuery,
        BlogIndexViewModel $blogIndexViewModel,
    ): View {
        $filters = $request->validated();
        $query = is_string($filters['q'] ?? null) ? $filters['q'] : '';
        $categorySlug = is_string($filters['category'] ?? null) ? $filters['category'] : null;

        return view('pages.blog.index', $blogIndexViewModel->data(
            $blogIndexQuery->results($query, $categorySlug),
            $query,
            $categorySlug,
        ));
    }

    public function show(Post $post, PostShowViewModel $postShowViewModel): View
    {
        abort_unless($post->isPublished(), 404);

        return view('pages.blog.show', $postShowViewModel->data($post));
    }
}
