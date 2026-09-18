<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterIssue extends CreateRecord
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;
}
