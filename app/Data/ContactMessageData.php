<?php

namespace App\Data;

use App\Enums\ContactBudget;
use App\Enums\ContactType;

final readonly class ContactMessageData
{
    public function __construct(
        public string $name,
        public string $email,
        public ContactType $type,
        public ?ContactBudget $budget,
        public string $message,
    ) {}
}
