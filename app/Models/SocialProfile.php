<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('platform', 'label', 'url', 'is_enabled', 'show_in_footer', 'show_on_contact', 'sort_order')]
class SocialProfile extends Model
{
    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'is_enabled' => 'boolean',
            'show_in_footer' => 'boolean',
            'show_on_contact' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
