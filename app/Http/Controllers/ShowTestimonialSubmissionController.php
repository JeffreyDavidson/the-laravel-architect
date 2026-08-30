<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowTestimonialSubmissionController extends Controller
{
    public function __invoke(): View
    {
        $seoSource = (new SEOData(
            title: 'Share Your Experience',
            description: 'Share a testimonial about working with Jeffrey Davidson and The Laravel Architect.',
        ))->markAsNoindex();

        return view('testimonials.create', ['seoSource' => $seoSource]);
    }
}
