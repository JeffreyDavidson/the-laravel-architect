<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\StoreContactRequest;
use App\ViewModels\ContactViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ContactController
{
    public function create(Request $request, ContactViewModel $viewModel): View
    {
        $projectSlug = $request->string('project')
            ->trim()
            ->toString();

        return view('pages.contact', $viewModel->data($projectSlug));
    }

    public function store(StoreContactRequest $request, SendContactMessage $sendContactMessage): RedirectResponse
    {
        $sendContactMessage->handle($request->toData());

        return back()->with('success', StoreContactRequest::SENT_MESSAGE);
    }
}
