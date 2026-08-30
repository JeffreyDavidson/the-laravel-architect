<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowUsesController extends Controller
{
    public function __invoke(): View
    {
        $seoSource = new SEOData(
            title: 'Uses',
            description: 'The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.',
        );

        return view('pages.uses', ['seoSource' => $seoSource]);
    }
}
