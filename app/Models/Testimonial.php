<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
