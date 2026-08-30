<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\ViewModels\BlogIndexViewModel;
use App\ViewModels\PostShowViewModel;
use Illuminate\Contracts\View\View;

class PostsController extends Controller
{
    public function index(BlogIndexViewModel $blogIndexViewModel): View
    {
        return view('blog.index', $blogIndexViewModel->data());
    }

    public function show(Post $post, PostShowViewModel $postShowViewModel): View
    {
        abort_unless($post->isPublished(), 404);

        return view('blog.show', $postShowViewModel->data($post));
    }
}
