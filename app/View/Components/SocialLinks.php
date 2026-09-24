<?php

namespace App\View\Components;

use App\Models\SocialProfile;
use App\Queries\SocialProfilesQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;
use InvalidArgumentException;

class SocialLinks extends Component
{
    /** @var Collection<int, SocialProfile> */
    public Collection $profiles;

    public function __construct(SocialProfilesQuery $query, public string $variant = 'buttons')
    {
        $this->profiles = match ($variant) {
            'buttons' => $query->forFooter(),
            'list' => $query->forContactPage(),
            default => throw new InvalidArgumentException("Unsupported social link variant [{$variant}]."),
        };
    }

    public function render(): View
    {
        return view('components.social-links');
    }
}
