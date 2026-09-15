<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\ViewModels\PostShowViewModel;
use Illuminate\Contracts\View\View;

class PreviewPostController
{
    public function __invoke(Post $post, PostShowViewModel $viewModel): View
    {
        return view('blog.show', $viewModel->previewData($post));
    }
}
