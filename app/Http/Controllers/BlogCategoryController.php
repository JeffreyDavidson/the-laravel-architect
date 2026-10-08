<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\ViewModels\BlogCategoryViewModel;
use Illuminate\Contracts\View\View;

final class BlogCategoryController
{
    public function __invoke(Category $category, BlogCategoryViewModel $blogCategoryViewModel): View
    {
        return view('pages.blog.category', $blogCategoryViewModel->data($category));
    }
}
