<?php

namespace App\Http\Controllers;

use App\ViewModels\AboutViewModel;
use Illuminate\Contracts\View\View;

class AboutController
{
    public function __invoke(AboutViewModel $viewModel): View
    {
        return view('pages.about', $viewModel->data());
    }
}
