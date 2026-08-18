<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;

class TestimonialController extends Controller
{
    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
        }

        Testimonial::query()->create($request->testimonialAttributes());

        return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
    }
}
