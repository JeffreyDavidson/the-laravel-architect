<?php

use function Pest\Laravel\get;

it('presents services with page metadata and working next steps', function () {
    get(route('services'))
        ->assertOk()
        ->assertViewIs('pages.services')
        ->assertSeeHtml('<title>Services — Jeffrey Davidson</title>')
        ->assertSee('Build your application')
        ->assertSee('Improve an existing codebase')
        ->assertSee('Ship with confidence')
        ->assertSee(route('contact'))
        ->assertSee(route('projects.index'));
});
