<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SearchContentType;
use App\Http\Requests\SearchRequest;
use App\Queries\SearchQuery;
use App\ViewModels\SearchViewModel;
use Illuminate\Contracts\View\View;

final class SearchController
{
    public function __invoke(SearchRequest $request, SearchQuery $searchQuery, SearchViewModel $viewModel): View
    {
        $query = $request->string('q')
            ->toString();
        $type = $request->enum('type', SearchContentType::class);

        return view('pages.search', $viewModel->data(
            $searchQuery->get($query, $type),
            $query,
            $type,
        ));
    }
}
