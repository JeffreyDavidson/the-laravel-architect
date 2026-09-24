<?php

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use App\Queries\SocialProfilesQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('preserves the current social links and placements after migration', function () {
    $query = app(SocialProfilesQuery::class);

    expect($query->forFooter()->map(fn (SocialProfile $profile): string => socialProfilePlatform($profile)->value)->all())
        ->toBe(['github', 'x', 'youtube', 'bluesky', 'instagram', 'facebook'])
        ->and($query->forContactPage()->map(fn (SocialProfile $profile): string => socialProfilePlatform($profile)->value)->all())
        ->toBe(['github', 'x', 'youtube', 'bluesky']);
});

it('returns enabled profiles for each placement in configured order', function () {
    SocialProfile::query()->delete();

    $footerProfile = createSocialProfilesQueryRecord(SocialPlatform::GitHub, 20, true, false);
    $contactProfile = createSocialProfilesQueryRecord(SocialPlatform::Bluesky, 10, false, true);
    createSocialProfilesQueryRecord(SocialPlatform::Instagram, 30, true, false, false);

    $query = app(SocialProfilesQuery::class);

    expect($query->forFooter()->modelKeys())->toBe([$footerProfile->getKey()])
        ->and($query->forContactPage()->modelKeys())->toBe([$contactProfile->getKey()]);
});

it('returns the first enabled URL for a platform and ignores disabled profiles', function () {
    SocialProfile::query()->delete();

    createSocialProfilesQueryRecord(SocialPlatform::YouTube, 10, true, true, false, 'https://youtube.com/disabled');
    $activeProfile = createSocialProfilesQueryRecord(
        SocialPlatform::YouTube,
        20,
        true,
        true,
        true,
        'https://youtube.com/active',
    );

    expect(app(SocialProfilesQuery::class)->enabledUrlFor(SocialPlatform::YouTube))
        ->toBe($activeProfile->url);
});

function createSocialProfilesQueryRecord(
    SocialPlatform $platform,
    int $sortOrder,
    bool $showInFooter,
    bool $showOnContact,
    bool $isEnabled = true,
    string $url = 'https://example.com/profile',
): SocialProfile {
    return SocialProfile::query()->create([
        'platform' => $platform,
        'url' => $url,
        'is_enabled' => $isEnabled,
        'show_in_footer' => $showInFooter,
        'show_on_contact' => $showOnContact,
        'sort_order' => $sortOrder,
    ]);
}

function socialProfilePlatform(SocialProfile $profile): SocialPlatform
{
    $platform = $profile->getAttribute('platform');

    if (! $platform instanceof SocialPlatform) {
        throw new LogicException('The social profile platform must be cast to its enum.');
    }

    return $platform;
}
