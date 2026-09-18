<?php

namespace App\Http\Controllers;

use App\Enums\SearchContentType;
use App\Http\Requests\ArchiveIndexRequest;
use App\Queries\ArchiveQuery;
use App\ViewModels\ArchiveViewModel;
use Illuminate\Contracts\View\View;

class ArchiveController
{
    public function __invoke(ArchiveIndexRequest $request, ArchiveQuery $archiveQuery, ArchiveViewModel $viewModel): View
    {
        $type = $request->enum('type', SearchContentType::class);
        $year = $request->integer('year') ?: null;

        return view('pages.archive', $viewModel->data(
            $archiveQuery->get($type, $year),
            $archiveQuery->years(),
            $type,
            $year,
        ));
    }
}
