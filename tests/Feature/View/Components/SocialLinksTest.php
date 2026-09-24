<?php

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('renders enabled profiles only in their configured public placements', function () {
    SocialProfile::query()->delete();

    $footerProfile = createSocialLinksProfile(SocialPlatform::GitHub, 'https://github.com/footer-only', true, false);
    $contactProfile = createSocialLinksProfile(SocialPlatform::LinkedIn, 'https://linkedin.com/in/contact-only', false, true);
    createSocialLinksProfile(SocialPlatform::Bluesky, 'https://bsky.app/profile/disabled', true, true, false);

    $this->get(route('home'))
        ->assertSeeHtml($footerProfile->url)
        ->assertDontSeeHtml($contactProfile->url)
        ->assertDontSeeHtml('https://bsky.app/profile/disabled');

    $this->get(route('contact'))
        ->assertSeeHtml($contactProfile->url)
        ->assertSeeHtml($footerProfile->url)
        ->assertDontSeeHtml('https://bsky.app/profile/disabled');
});

it('uses the enabled YouTube profile for the homepage channel link', function () {
    SocialProfile::query()->delete();

    $youtubeProfile = createSocialLinksProfile(
        SocialPlatform::YouTube,
        'https://youtube.com/@managed-channel',
        false,
        false,
    );

    $this->get(route('home'))
        ->assertSeeHtml($youtubeProfile->url);

    $youtubeProfile->update(['is_enabled' => false]);

    $this->get(route('home'))
        ->assertDontSeeHtml($youtubeProfile->url);
});

it('escapes a profile display label on the contact page', function () {
    SocialProfile::query()->delete();

    createSocialLinksProfile(
        SocialPlatform::LinkedIn,
        'https://linkedin.com/in/safe-profile',
        false,
        true,
        true,
        '<img src=x onerror=alert(1)>',
    );

    $this->get(route('contact'))
        ->assertSee('<img src=x onerror=alert(1)>')
        ->assertDontSeeHtml('<img src=x onerror=alert(1)>');
});

function createSocialLinksProfile(
    SocialPlatform $platform,
    string $url,
    bool $showInFooter,
    bool $showOnContact,
    bool $isEnabled = true,
    ?string $label = null,
): SocialProfile {
    return SocialProfile::query()->create([
        'platform' => $platform,
        'label' => $label,
        'url' => $url,
        'is_enabled' => $isEnabled,
        'show_in_footer' => $showInFooter,
        'show_on_contact' => $showOnContact,
        'sort_order' => 10,
    ]);
}
