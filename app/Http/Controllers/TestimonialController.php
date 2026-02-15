<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'body' => 'required|string|max:1000',
        ]);

        Testimonial::create($validated);

        return back()->with('testimonial_success', 'Thank you! Your testimonial has been submitted and will appear once approved.');
    }
}
