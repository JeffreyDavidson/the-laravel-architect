<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SearchContentType;
use App\Http\Requests\ArchiveIndexRequest;
use App\ViewModels\ArchiveViewModel;
use Illuminate\Contracts\View\View;

final class ArchiveController
{
    public function __invoke(ArchiveIndexRequest $request, ArchiveViewModel $viewModel): View
    {
        return view('pages.archive', $viewModel->data(
            $request->enum('type', SearchContentType::class),
            $request->integer('year') ?: null,
        ));
    }
}
