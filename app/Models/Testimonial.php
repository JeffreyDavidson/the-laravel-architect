<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Testimonial extends Model
{
    protected $guarded = [];

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
