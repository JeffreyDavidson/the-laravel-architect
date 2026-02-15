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

        // Send to Jeffrey
        Mail::raw(
            "Name: {$validated['name']}\n" .
            "Email: {$validated['email']}\n" .
            "Type: {$validated['type']}\n" .
            "Budget: " . ($validated['budget'] ?? 'Not specified') . "\n\n" .
            "Message:\n{$validated['message']}",
            function ($message) use ($validated) {
                $message->to(config('mail.contact_to', config('mail.from.address')))
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject("Contact Form: {$validated['type']} — {$validated['name']}");
            }
        );

        // Send confirmation copy to submitter
        Mail::raw(
            "Hi {$validated['name']},\n\n" .
            "Thanks for reaching out! Here's a copy of your message. I'll get back to you within 24–48 hours.\n\n" .
            "---\n\n" .
            "Type: {$validated['type']}\n" .
            "Budget: " . ($validated['budget'] ?? 'Not specified') . "\n\n" .
            "Message:\n{$validated['message']}\n\n" .
            "---\n\n" .
            "Jeffrey Davidson\nThe Laravel Architect\nhttps://thelaravelarchitect.com",
            function ($message) use ($validated) {
                $message->to($validated['email'], $validated['name'])
                    ->subject("Got your message — thanks, {$validated['name']}!");
            }
        );

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}
