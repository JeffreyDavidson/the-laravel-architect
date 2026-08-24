<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class TestimonialController extends Controller
{
    public function create(): View
    {
        seo()->for(new SEOData(
            title: 'Share Your Experience',
            description: 'Share a testimonial about working with Jeffrey Davidson and The Laravel Architect.',
        ));

        return view('testimonials.create');
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
        }

        Testimonial::query()->create($request->testimonialAttributes());

        return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
    }
}
