<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\OgImageGenerator;
use Illuminate\Http\Response;

class OgImageController extends Controller
{
    public function __invoke(Post $post, OgImageGenerator $generator): Response
    {
        $png = $generator->generate($post);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
