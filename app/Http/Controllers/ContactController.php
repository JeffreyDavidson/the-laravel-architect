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

class ContactController
{
    public function create(Request $request, ContactViewModel $viewModel): View
    {
        $projectSlug = $request->string('project')
            ->trim()
            ->toString();
        $project = filled($projectSlug)
            ? Project::query()->published()
                ->where('slug', $projectSlug)
                ->first()
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

        $turnstileAction = config('services.turnstile.contact_action');

        if (! is_string($turnstileAction) || ! $turnstileVerifier->passes($request, $turnstileAction)) {
            return back()
                ->withErrors([
                    'cf-turnstile-response' => 'Please verify that you are human and try again.',
                ])
                ->withInput($request->except('cf-turnstile-response'));
        }

        $projectSlug = $request->string('project')
            ->trim()
            ->toString();
        $projectTitle = filled($projectSlug)
            ? Project::query()
                ->published()
                ->where('slug', $projectSlug)
                ->first()
                ?->title
            : null;

        $sendContactMessage->handle($request->toData($projectTitle));

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}
