<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\postJson;

it('accepts temporary uploads only up to the image size limit', function (int $kilobytes, int $status) {
    Storage::fake('tmp-for-tests');
    $file = UploadedFile::fake()->create('cover.jpg', $kilobytes, 'image/jpeg');
    $uploadUrl = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5), absolute: false);

    $response = postJson($uploadUrl, ['files' => [$file]]);

    $response->assertStatus($status);
})->with([
    'at the limit' => [10240, 200],
    'over the limit' => [10241, 422],
]);
