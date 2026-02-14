<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'type' => 'required|string|in:freelance,consulting,modernization,collaboration,other',
            'budget' => 'nullable|string|in:small,medium,large,enterprise',
            'message' => 'required|string|max:5000',
        ]);

        Mail::raw(
            "Name: {$validated['name']}\n" .
            "Email: {$validated['email']}\n" .
            "Type: {$validated['type']}\n" .
            "Budget: " . ($validated['budget'] ?? 'Not specified') . "\n\n" .
            "Message:\n{$validated['message']}",
            function ($message) use ($validated) {
                $message->to(config('app.contact_email', config('mail.from.address')))
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject("Contact Form: {$validated['type']} — {$validated['name']}");
            }
        );

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours.');
    }
}
