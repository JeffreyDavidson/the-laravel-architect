<?php

it('sends new contact inquiries to the site contact address', function () {
    expect(config('creator-kit.contact.notify'))
        ->toBe([config('mail.contact_to')]);
});
