<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

#[Unguarded]
class Testimonial extends Model
{
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable();
    }
}
