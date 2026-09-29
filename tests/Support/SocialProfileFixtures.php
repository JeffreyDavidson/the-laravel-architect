<?php

namespace Tests\Support;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;

class SocialProfileFixtures
{
    public static function create(
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
}
