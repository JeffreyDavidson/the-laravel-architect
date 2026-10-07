<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use App\ViewModels\BlogTagViewModel;
use Illuminate\Contracts\View\View;

final class BlogTagController
{
    public function __invoke(Tag $tag, BlogTagViewModel $blogTagViewModel): View
    {
        return view('pages.blog.tag', $blogTagViewModel->data($tag));
    }
}
