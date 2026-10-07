<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ViewModels\PrivacyViewModel;
use Illuminate\Contracts\View\View;

final class PrivacyController
{
    public function __invoke(PrivacyViewModel $viewModel): View
    {
        return view('pages.privacy', $viewModel->data());
    }
}
