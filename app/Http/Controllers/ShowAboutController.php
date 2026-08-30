<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowAboutController extends Controller
{
    public function __invoke(): View
    {
        $seoSource = new SEOData(
            title: 'About',
            description: 'Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.',
        );

        return view('pages.about', ['seoSource' => $seoSource]);
    }
}
