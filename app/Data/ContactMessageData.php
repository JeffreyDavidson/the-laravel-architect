<?php

declare(strict_types=1);

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
        public ?string $projectTitle = null,
    ) {}

    /**
     * The inquiry's columns, for creator-kit's `SendContactMessage`.
     *
     * @return array{name: string, email: string, type: string, budget: string|null, message: string, project_title: string|null}
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type->value,
            'budget' => $this->budget?->value,
            'message' => $this->message,
            'project_title' => $this->projectTitle,
        ];
    }
}
