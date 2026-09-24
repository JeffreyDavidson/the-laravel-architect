<?php

namespace App\Queries;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class SocialProfilesQuery
{
    /** @return Collection<int, SocialProfile> */
    public function forFooter(): Collection
    {
        if (! $this->profilesTableExists()) {
            return new Collection;
        }

        return $this->orderedEnabledProfiles()
            ->where('show_in_footer', true)
            ->get();
    }

    /** @return Collection<int, SocialProfile> */
    public function forContactPage(): Collection
    {
        if (! $this->profilesTableExists()) {
            return new Collection;
        }

        return $this->orderedEnabledProfiles()
            ->where('show_on_contact', true)
            ->get();
    }

    public function enabledUrlFor(SocialPlatform $platform): ?string
    {
        if (! $this->profilesTableExists()) {
            return null;
        }

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

    private function profilesTableExists(): bool
    {
        return Schema::hasTable('social_profiles');
    }
}
