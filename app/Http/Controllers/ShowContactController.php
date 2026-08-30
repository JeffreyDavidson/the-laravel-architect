<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowContactController extends Controller
{
    public function __invoke(): View
    {
        $seoSource = new SEOData(
            title: 'Contact',
            description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
        );

        return view('pages.contact', ['seoSource' => $seoSource]);
    }
}
