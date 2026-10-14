<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('presents services with page metadata and working next steps', function () {
    $response = get(route('services'));

    $response
        ->assertOk()
        ->assertViewIs('pages.services')
        ->assertSeeHtml('<title>Services — Jeffrey Davidson</title>')
        ->assertSee('Build your application')
        ->assertSee('Improve an existing codebase')
        ->assertSee('Ship with confidence')
        ->assertSee(route('contact.create'))
        ->assertSee(route('projects.index'));
});
