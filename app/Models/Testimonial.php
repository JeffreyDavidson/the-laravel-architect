<?php

namespace App\Models;

use App\Enums\TestimonialStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'role', 'company', 'body', 'status', 'sort_order')]
/** @property TestimonialStatus $status */
class Testimonial extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return ['status' => TestimonialStatus::class];
    }

    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', TestimonialStatus::Approved);
    }

    public function testimonialStatus(): TestimonialStatus
    {
        return $this->getAttribute('status');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll()
            ->dontLogEmptyChanges();
    }
}
