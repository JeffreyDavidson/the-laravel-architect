<?php

namespace App\Queries;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SocialProfilesQuery
{
    /** @return Collection<int, SocialProfile> */
    public function forFooter(): Collection
    {
        return $this->orderedEnabledProfiles()
            ->where('show_in_footer', true)
            ->get();
    }

    /** @return Collection<int, SocialProfile> */
    public function forContactPage(): Collection
    {
        return $this->orderedEnabledProfiles()
            ->where('show_on_contact', true)
            ->get();
    }

    public function enabledUrlFor(SocialPlatform $platform): ?string
    {
        $url = $this->orderedEnabledProfiles()
            ->where('platform', $platform->value)
            ->value('url');

        return is_string($url) ? $url : null;
    }

    /** @return Builder<SocialProfile> */
    private function orderedEnabledProfiles(): Builder
    {
        return SocialProfile::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
