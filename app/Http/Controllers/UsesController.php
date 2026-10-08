<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ViewModels\UsesViewModel;
use Illuminate\Contracts\View\View;

final class UsesController
{
    public function __invoke(UsesViewModel $viewModel): View
    {
        return view('pages.uses', $viewModel->data());
    }
}
