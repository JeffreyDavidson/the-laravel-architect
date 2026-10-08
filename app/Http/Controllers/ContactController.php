<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\CreateContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\ViewModels\ContactViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class ContactController
{
    public function create(CreateContactRequest $request, ContactViewModel $viewModel): View
    {
        return view('pages.contact', $viewModel->data($request->projectSlug()));
    }

    public function store(StoreContactRequest $request, SendContactMessage $sendContactMessage): RedirectResponse
    {
        $sendContactMessage->handle($request->toData());

        return back()->with('success', StoreContactRequest::SENT_MESSAGE);
    }
}
