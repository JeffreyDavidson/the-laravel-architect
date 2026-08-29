<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Queries\RelatedPostsQuery;
use App\ViewModels\BlogIndexViewModel;
use Illuminate\Contracts\View\View;

class PostsController extends Controller
{
    public function index(BlogIndexViewModel $blogIndexViewModel): View
    {
        return view('blog.index', $blogIndexViewModel->data());
    }

    public function show(Post $post, RelatedPostsQuery $relatedPostsQuery): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load(['category', 'tags', 'author']);
        $relatedPosts = $relatedPostsQuery->get($post);

        $seoSource = $post;

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'seoSource' => $seoSource,
        ]);
    }
}
