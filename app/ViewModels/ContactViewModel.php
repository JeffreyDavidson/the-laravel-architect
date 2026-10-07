<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Models\Project;
use App\Queries\PublishedProjectQuery;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class ContactViewModel
{
    public function __construct(private PublishedProjectQuery $publishedProjectQuery) {}

    /**
     * @return array{
     *     seoSource: SEOData,
     *     contactTypeOptions: array<string, string>,
     *     contactBudgetOptions: array<string, string>,
     *     defaultContactType: string,
     *     selectedProject: Project|null,
     * }
     */
    public function data(string $projectSlug = ''): array
    {
        return [
            'contactTypeOptions' => collect(ContactType::cases())
                ->mapWithKeys(fn (ContactType $type): array => [$type->value => $type->getLabel()])
                ->all(),
            'contactBudgetOptions' => collect(ContactBudget::cases())
                ->mapWithKeys(fn (ContactBudget $budget): array => [$budget->value => $budget->getLabel()])
                ->all(),
            'defaultContactType' => ContactType::Freelance->value,
            'selectedProject' => $this->publishedProjectQuery->findBySlug($projectSlug),
            'seoSource' => new SEOData(
                title: 'Contact',
                description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
            ),
        ];
    }
}
