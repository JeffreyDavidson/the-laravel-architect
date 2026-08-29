<?php

namespace App\Http\Controllers;

use App\ViewModels\HomeViewModel;
use Illuminate\Contracts\View\View;

class ShowHomeController extends Controller
{
    public function __invoke(HomeViewModel $homeViewModel): View
    {
        return view('pages.home', $homeViewModel->data());
    }
}
