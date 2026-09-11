<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\ViewModels\BlogCategoryViewModel;
use Illuminate\Contracts\View\View;

class BlogCategoryController
{
    public function __invoke(Category $category, BlogCategoryViewModel $blogCategoryViewModel): View
    {
        return view('blog.category', $blogCategoryViewModel->data($category));
    }
}
