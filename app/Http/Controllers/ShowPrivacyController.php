<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowPrivacyController extends Controller
{
    public function __invoke(): View
    {
        $seoSource = new SEOData(
            title: 'Privacy',
            description: 'How The Laravel Architect handles contact messages, newsletter subscriptions, testimonials, analytics, and essential site data.',
        );

        return view('pages.privacy', ['seoSource' => $seoSource]);
    }
}
