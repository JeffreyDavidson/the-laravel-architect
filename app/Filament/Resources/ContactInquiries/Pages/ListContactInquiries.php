<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListContactInquiries extends ListRecords
{
    #[\Override]
    protected static string $resource = ContactInquiryResource::class;
}
