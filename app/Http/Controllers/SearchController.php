<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Queries\SearchQuery;
use App\ViewModels\SearchViewModel;
use Illuminate\Contracts\View\View;

class SearchController
{
    public function __invoke(SearchRequest $request, SearchQuery $searchQuery, SearchViewModel $viewModel): View
    {
        $query = $request->string('q')->toString();

        return view('search.index', $viewModel->data(
            $searchQuery->get($query),
            $query,
        ));
    }
}
