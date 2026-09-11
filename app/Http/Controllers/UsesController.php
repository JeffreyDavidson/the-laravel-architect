<?php

namespace App\Http\Controllers;

use App\ViewModels\UsesViewModel;
use Illuminate\Contracts\View\View;

class UsesController
{
    public function __invoke(UsesViewModel $viewModel): View
    {
        return view('pages.uses', $viewModel->data());
    }
}
