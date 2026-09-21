<?php

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\StoreContactRequest;
use App\Models\Project;
use App\Services\TurnstileVerifier;
use App\ViewModels\ContactViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContactController
{
    public function create(Request $request, ContactViewModel $viewModel): View
    {
        $projectSlug = $request->string('project')->trim()->toString();
        $project = filled($projectSlug)
            ? Project::query()->published()->where('slug', $projectSlug)->first()
            : null;

        return view('pages.contact', $viewModel->data($project));
    }

    public function store(
        StoreContactRequest $request,
        TurnstileVerifier $turnstileVerifier,
        SendContactMessage $sendContactMessage,
    ): RedirectResponse {
        if ($request->filled('website')) {
            return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
        }

        $key = 'contact-form:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()
                ->withErrors(['message' => 'Too many submissions. Please try again later.'])
                ->withInput($request->except(['website', 'cf-turnstile-response']));
        }

        $turnstileAction = config('services.turnstile.contact_action');

        if (! is_string($turnstileAction) || ! $turnstileVerifier->passes($request, $turnstileAction)) {
            return back()
                ->withErrors([
                    'cf-turnstile-response' => 'Please verify that you are human and try again.',
                ])
                ->withInput($request->except('cf-turnstile-response'));
        }

        RateLimiter::hit($key, 3600);

        $projectSlug = $request->string('project')->trim()->toString();
        $projectTitle = filled($projectSlug)
            ? Project::query()
                ->published()
                ->where('slug', $projectSlug)
                ->first()?->title
            : null;

        $sendContactMessage->handle($request->toData($projectTitle));

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}
