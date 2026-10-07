<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\ContentReadinessStatus;
use App\Enums\MediaHealthStatus;
use App\Enums\MediaHealthType;
use App\Enums\MediaSourceStatus;
use App\Enums\MediaVariantStatus;
use App\Enums\ProjectReadinessFilter;
use App\Enums\PublishStatus;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

covers(
    ContactInquiryStatus::class,
    ContentReadinessStatus::class,
    MediaHealthStatus::class,
    MediaHealthType::class,
    MediaSourceStatus::class,
    MediaVariantStatus::class,
    ProjectReadinessFilter::class,
    PublishStatus::class,
);

it('reports the badge color for each status', function (HasColor $status, string $color) {
    $result = $status->getColor();

    expect($result)
        ->toBe($color);
})->with([
    'draft' => [PublishStatus::Draft, 'gray'],
    'in review' => [PublishStatus::InReview, 'info'],
    'published' => [PublishStatus::Published, 'success'],
    'scheduled' => [PublishStatus::Scheduled, 'warning'],
    'new inquiry' => [ContactInquiryStatus::New, 'info'],
    'inquiry in progress' => [ContactInquiryStatus::InProgress, 'warning'],
    'resolved inquiry' => [ContactInquiryStatus::Resolved, 'success'],
    'healthy media' => [MediaHealthStatus::Healthy, 'success'],
    'media needing repair' => [MediaHealthStatus::NeedsRepair, 'warning'],
    'media needing re-upload' => [MediaHealthStatus::ReuploadRequired, 'danger'],
    'optimized source' => [MediaSourceStatus::Optimized, 'success'],
    'source needing optimization' => [MediaSourceStatus::NeedsOptimization, 'warning'],
    'missing source' => [MediaSourceStatus::Missing, 'danger'],
    'unreadable source' => [MediaSourceStatus::Unreadable, 'danger'],
    'ready variants' => [MediaVariantStatus::Ready, 'success'],
    'missing variants' => [MediaVariantStatus::Missing, 'warning'],
    'unavailable variants' => [MediaVariantStatus::Unavailable, 'warning'],
    'ready content' => [ContentReadinessStatus::Ready, 'success'],
    'content needing attention' => [ContentReadinessStatus::NeedsAttention, 'warning'],
]);

it('labels each case as it was previously displayed', function (HasLabel $case, string $label) {
    $result = $case->getLabel();

    expect($result)
        ->toBe($label);
})->with([
    'new inquiry' => [ContactInquiryStatus::New, 'New'],
    'inquiry in progress' => [ContactInquiryStatus::InProgress, 'In progress'],
    'resolved inquiry' => [ContactInquiryStatus::Resolved, 'Resolved'],
    'healthy media' => [MediaHealthStatus::Healthy, 'Healthy'],
    'media needing repair' => [MediaHealthStatus::NeedsRepair, 'Needs repair'],
    'media needing re-upload' => [MediaHealthStatus::ReuploadRequired, 'Re-upload required'],
    'optimized source' => [MediaSourceStatus::Optimized, 'Optimized'],
    'source needing optimization' => [MediaSourceStatus::NeedsOptimization, 'Needs optimization'],
    'missing source' => [MediaSourceStatus::Missing, 'Missing'],
    'unreadable source' => [MediaSourceStatus::Unreadable, 'Unreadable'],
    'ready variants' => [MediaVariantStatus::Ready, 'Ready'],
    'missing variants' => [MediaVariantStatus::Missing, 'Missing'],
    'unavailable variants' => [MediaVariantStatus::Unavailable, 'Unavailable'],
    'project type' => [MediaHealthType::Project, 'Project'],
    'post type' => [MediaHealthType::Post, 'Post'],
    'podcast type' => [MediaHealthType::Podcast, 'Podcast'],
    'ready project' => [ProjectReadinessFilter::Ready, 'Ready'],
    'project needing an image' => [ProjectReadinessFilter::NeedsImage, 'Needs image'],
    'project needing a case study' => [ProjectReadinessFilter::NeedsCaseStudy, 'Needs case study'],
    'project needing details' => [ProjectReadinessFilter::NeedsDetails, 'Needs project details'],
    'ready content' => [ContentReadinessStatus::Ready, 'Ready'],
    'content needing attention' => [ContentReadinessStatus::NeedsAttention, 'Needs attention'],
]);

it('keeps the stored media health type keys', function () {
    $keys = array_column(MediaHealthType::cases(), 'value');

    expect($keys)
        ->toBe(['project', 'post', 'podcast']);
});

it('keeps the project readiness filter keys', function () {
    $keys = array_column(ProjectReadinessFilter::cases(), 'value');

    expect($keys)
        ->toBe(['ready', 'needs_image', 'needs_case_study', 'needs_details']);
});

it('uses snake_case keys for the status values', function (string $enum, array $values) {
    $keys = array_column($enum::cases(), 'value');

    expect($keys)
        ->toBe($values);
})->with([
    'media health' => [MediaHealthStatus::class, ['healthy', 'needs_repair', 'reupload_required']],
    'media source' => [MediaSourceStatus::class, ['optimized', 'needs_optimization', 'missing', 'unreadable']],
    'media variants' => [MediaVariantStatus::class, ['ready', 'missing', 'unavailable']],
    'content readiness' => [ContentReadinessStatus::class, ['ready', 'needs_attention']],
]);
