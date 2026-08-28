<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PageController extends Controller
{
    public function contact(): View
    {
        $seoSource = new SEOData(
            title: 'Contact',
            description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
        );

        return view('pages.contact', ['seoSource' => $seoSource]);
    }

    public function privacy(): View
    {
        $seoSource = new SEOData(
            title: 'Privacy',
            description: 'How The Laravel Architect handles contact messages, newsletter subscriptions, testimonials, analytics, and essential site data.',
        );

        return view('pages.privacy', ['seoSource' => $seoSource]);
    }

    public function uses(): View
    {
        $seoSource = new SEOData(
            title: 'Uses',
            description: 'The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.',
        );

        return view('pages.uses', ['seoSource' => $seoSource]);
    }
}
