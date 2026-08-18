<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        Testimonial::query()->create($validated);

        return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
    }
}
