<?php

namespace App\Http\Controllers;

use App\ViewModels\ServiceViewModel;
use Illuminate\Contracts\View\View;

class ServiceController
{
    public function __invoke(ServiceViewModel $viewModel): View
    {
        return view('pages.services', $viewModel->data());
    }
}
