<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\ViewModels\BlogTagViewModel;
use Illuminate\Contracts\View\View;

class BlogTagController
{
    public function __invoke(Tag $tag, BlogTagViewModel $blogTagViewModel): View
    {
        return view('blog.tag', $blogTagViewModel->data($tag));
    }
}
