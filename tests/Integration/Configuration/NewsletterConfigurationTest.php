<?php

it('sends editor test emails to the site contact address', function () {
    expect(config('creator-kit.newsletter.test_recipients'))
        ->toBe([config('mail.contact_to')]);
});
