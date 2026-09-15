<?php

namespace App\ViewModels;

use App\Enums\ContactBudget;
use App\Enums\ContactType;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ContactViewModel
{
    /**
     * @return array{
     *     seoSource: SEOData,
     *     contactTypeOptions: array<string, string>,
     *     contactBudgetOptions: array<string, string>,
     *     defaultContactType: string,
     * }
     */
    public function data(): array
    {
        return [
            'contactTypeOptions' => collect(ContactType::cases())
                ->mapWithKeys(fn (ContactType $type): array => [$type->value => $type->getLabel()])
                ->all(),
            'contactBudgetOptions' => collect(ContactBudget::cases())
                ->mapWithKeys(fn (ContactBudget $budget): array => [$budget->value => $budget->getLabel()])
                ->all(),
            'defaultContactType' => ContactType::Freelance->value,
            'seoSource' => new SEOData(
                title: 'Contact',
                description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
            ),
        ];
    }
}
