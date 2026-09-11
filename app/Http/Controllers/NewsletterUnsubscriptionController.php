<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\ViewModels\NewsletterUnsubscriptionViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterUnsubscriptionController
{
    public function create(Request $request, Subscriber $subscriber, NewsletterUnsubscriptionViewModel $viewModel): View
    {
        return view('newsletter.unsubscribe', $viewModel->data($subscriber, $request->fullUrl()));
    }
}
