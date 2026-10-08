<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SearchContentType;
use App\Http\Requests\SearchRequest;
use App\ViewModels\SearchViewModel;
use Illuminate\Contracts\View\View;

final class SearchController
{
    public function __invoke(SearchRequest $request, SearchViewModel $viewModel): View
    {
        return view('pages.search', $viewModel->data(
            $request->string('q')
                ->toString(),
            $request->enum('type', SearchContentType::class),
        ));
    }
}
