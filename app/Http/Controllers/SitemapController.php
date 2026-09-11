<?php

namespace App\Http\Controllers;

use App\Actions\GenerateSitemap;
use Illuminate\Http\Response;

class SitemapController
{
    public function __invoke(GenerateSitemap $generateSitemap): Response
    {
        return response($generateSitemap->handle(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
