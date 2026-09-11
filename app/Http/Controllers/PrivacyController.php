<?php

namespace App\Http\Controllers;

use App\ViewModels\PrivacyViewModel;
use Illuminate\Contracts\View\View;

class PrivacyController
{
    public function __invoke(PrivacyViewModel $viewModel): View
    {
        return view('pages.privacy', $viewModel->data());
    }
}
