<?php

namespace App\Http\Controllers;

use App\ViewModels\HomeViewModel;
use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(HomeViewModel $homeViewModel): View
    {
        return view('pages.home', $homeViewModel->data());
    }
}
